<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SubscriptionHelper;
use App\Models\User;
use App\Models\Package;
use Illuminate\Support\Facades\Http;
use App\Models\PaymentSubscription;
use App\Models\PaymentHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use App\Mail\SendEmail;
use App\Mail\ResetPasswordMail;
use App\Mail\AdminMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendNewSubMail;

class SubscriptionController extends Controller
{
    protected $SubscriptionHelper;
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function checkout($id, $plan) 
    {
        $user = User::find($id);
        
        $package = Package::find($plan);
        $data = [
            'cusID' => $user->CusID,
            'price_id' => $package->PriceID,
            'user_id' => $id,
            'plan' => $plan,
        ];

        $res = $this->subscriptionHelper->addSubscription($data);

        if(!empty($res)) {
            return redirect($res['url']);
        }

        return redirect()->route('user.login')->with('success', 'Something went wrong');
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        $userHash = $request->get('user');
        $plan = $request->get('plan');

        $user = User::whereRaw('MD5(UserID) = ?', [$userHash])->firstOrFail();

        // 1. Get checkout session details
        $session = Http::withToken(config('services.stripe.secret'))
            ->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

        if (!$session->successful()) {
            abort(500, 'Payment session could not be verified.');
        }

        $session_data = $session->json();
        $subscriptionId = $session_data['subscription'] ?? null;

        if (!$subscriptionId) {
            abort(500, 'Subscription ID missing in session.');
        }

        // 2. Fetch latest invoice for subscription
        $invoice = Http::withToken(config('services.stripe.secret'))
            ->get("https://api.stripe.com/v1/invoices", [
                'subscription' => $subscriptionId,
                'limit' => 1,
            ]);

        $invoiceData = null;
        if ($invoice->successful() && !empty($invoice->json('data.0'))) {
            $invoiceData = $invoice->json('data.0');
        }

        $invoiceId = $invoiceData['id'] ?? null;

        if(!empty($invoiceId)) {
            // 3. Update user and create subscription record
            $user->Status = 'Active';
            $user->SubID = $subscriptionId;
            $user->CurrentPackageID = $plan;
            $user->save();

            PaymentSubscription::where('UserID', $user->UserID)
                ->whereIn('Status', ['Canceled', 'Pending'])
                ->delete();

            $packages = Package::findOrFail($user->CurrentPackageID);

            $paymentStartDate = Carbon::now();
            $paymentEndDate = $paymentStartDate->copy()->addHours(24);
            $nextRenewalDate = $paymentStartDate->copy()->addDays((int)$packages->Duration + 1);

            $paymentSubscription = PaymentSubscription::create([
                'UserID' => $user->UserID,
                'PackageID' => $user->CurrentPackageID,
                'PaymentMethodID' => 1,
                'PaymentAmount' => $packages->Price,
                'PaymentStartDate' => $paymentStartDate,
                'PaymentEndDate' => $paymentEndDate,
                'NextRenewalDate' => $nextRenewalDate,
                'ChecksGiven' => $packages->CheckLimitPerMonth,
                'ChecksUsed' => 0,
                'RemainingChecks' => 0,
                'PaymentDate' => $paymentStartDate,
                'PaymentAttempts' => 0,
                'TransactionID' => $invoiceId,
                'InvoiceID' => $invoiceId,
                'Status' => 'Active',
            ]);

            PaymentHistory::create([
                'PaymentSubscriptionID' => $paymentSubscription->PaymentSubscriptionID,
                'PaymentAmount' => $packages->Price,
                'PaymentDate' => $paymentStartDate,
                'PaymentStatus' => 'Success',
                'PaymentAttempts' => 0,
                'TransactionID' => $invoiceId,
                'InvoiceID' => $invoiceId,
            ]);

             $user_name = $user->FirstName . ' ' .$user->LastName;
             $data = [
                'plan_name' => $packages->Name,
                'start_date' => $paymentStartDate->format('m/d/Y'),
                'next_billing_date' => $nextRenewalDate->format('m/d/Y'),
                'amount' => $packages->Price,
             ];
             Mail::to($user->Email)->send(new SendNewSubMail(6, $user_name, $data));
             Mail::to(env('ADMIN_EMAIL'))->send(new SendNewSubMail(10, $packages->Name, $user_name, $user->Email));      
            // Optional: redirect or show a view
            return redirect()->route('user.login')->with('success', 'Account created successfully!');
        }
         return redirect()->route('user.login')->with('error', 'Something want to wrong');
    }

    public function cancel()
    {
        return redirect()->route('user.login')->with('error', 'Something went wrong');
    }

    public function add_card(Request $request) 
    {
    
        $stripeSecretKey = env('STRIPE_SECRET');
        $stripeToken = $request->stripeToken; 
        $customerId =Auth::user()->CusID;

        try {
            $response = Http::withBasicAuth($stripeSecretKey, '')
            ->asForm()
            ->post("https://api.stripe.com/v1/customers/{$customerId}/sources", [
                'source' => $stripeToken
            ]);

            $data = $response->json();

            if ($response->failed() || isset($data['error'])) {
                $message = $data['error']['message'] ?? 'Something went wrong';
                return redirect()->back()->with('error_card', $message);
            }

            return redirect()->back()->with('success_card', 'Card added successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_card', 'Something went wrong');
        }
    }

    public function delete_card($id)
    {
        $stripeSecretKey = env('STRIPE_SECRET');
        $customerId =Auth::user()->CusID;

        try {
            $response = Http::withBasicAuth($stripeSecretKey, '')
                ->delete("https://api.stripe.com/v1/customers/{$customerId}/sources/{$id}");

            $data = $response->json();

            if ($response->failed() || isset($data['error'])) {
                $message = $data['error']['message'] ?? 'Failed to delete card';
                return redirect()->back()->with('error_card', $message);
            }

            return back()->with('success_card', 'Card deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_card', 'Failed to delete card');
        }
    }

    public function set_default($id)
    {
        $stripeSecretKey = env('STRIPE_SECRET');
        $customerId = Auth::user()->CusID;

        $response = Http::withBasicAuth($stripeSecretKey, '')
            ->asForm()
            ->post("https://api.stripe.com/v1/customers/{$customerId}", [
                'invoice_settings[default_payment_method]' => $id,
            ]);

        $data = $response->json();

        if ($response->failed() || isset($data['error'])) {
            return back()->with('error_card', $data['error']['message'] ?? 'Failed to set default card.');
        }

        return back()->with('success_card', 'Default card set successfully!');
    }

}
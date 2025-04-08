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
use Illuminate\Support\Facades\Mail;

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
        $user->CurrentPackageID = $plan;
        
        $package = Package::find($plan);
        $data = [
            'cusID' => $user->CusID,
            'price_id' => $package->PriceID,
            'user_id' => $id,
        ];

        $res = $this->subscriptionHelper->addSubscription($data);

        if(!empty($res)) {
            $user->save();
            return redirect($res['url']);
        }

        return redirect()->route('user.login')->with('success', 'Something went wrong');
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        $user = $request->get('user');

        $user = User::whereRaw('MD5(UserID) = ?', [$user])->first();

        $session = Http::withToken(config('services.stripe.secret'))->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

        $session_data = $session->json();
        $user->Status = 'Active';
        $user->save();

        $PaymentSubscription_plan = PaymentSubscription::where('UserID', $user->UserID)->whereIn('Status', ['Canceled', 'Pending'])->delete();


        $packages = Package::find($user->CurrentPackageID);

        $paymentStartDate = Carbon::now();

        $paymentEndDate = $paymentStartDate->copy()->addHours(24);

        $nextRenewalDate = $paymentStartDate->copy()->addDays($packages->Duration);


        $paymentSubscription = PaymentSubscription::create([
            'UserID' => $user->UserID,
            'PackageID' => $user->CurrentPackageID,
            'PaymentMethodID' => 1,
            'PaymentAmount' => $packages->Price,
            'PaymentStartDate' => $paymentStartDate,
            'PaymentEndDate' => $paymentEndDate,
            'NextRenewalDate' => $nextRenewalDate,
            'ChecksGiven' => $packages->CheckLimitPerMonth,
            'ChecksUsed'=> 0,
            'RemainingChecks' => 0,
            'PaymentDate' => $paymentStartDate,
            'PaymentAttempts' => 0 ,
            'TransactionID' => $session_data['subscription'],
            'Status' => 'Active', 
        ]);

        $paymentSubscriptionId = $paymentSubscription->PaymentSubscriptionID;

        $paymentSubscription = PaymentHistory::create([
            'PaymentSubscriptionID' => $paymentSubscriptionId,
            'PaymentAmount' => $packages->Price,
            'PaymentDate' => $paymentStartDate,
            'PaymentStatus' => 'Success',
            'PaymentAttempts' => 0,
            'TransactionID' => $paymentSubscription->TransactionID,
        ]);

        $name = $user->FirstName . ' ' .$user->LastName;
        Mail::to($user->Email)->send(new SendEmail(1, $name));
        return redirect()->route('user.login')->with('success', 'Account created successful!');

    }

    public function cancel()
    {
        return redirect()->route('user.login')->with('success', 'Something went wrong');
    }
}

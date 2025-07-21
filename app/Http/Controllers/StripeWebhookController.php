<?php

namespace App\Http\Controllers;

use App\Helpers\SubscriptionHelper;
use App\Mail\StripeCancelSubMail;
use App\Models\PaymentHistory;
use App\Models\PaymentSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StripeWebhookController extends Controller
{

    protected $subscriptionHelper;
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $header = $request->header('Stripe-Signature');
        $secret = env('STRIPE_WEBHOOK_SECRET'); // Your webhook secret from Stripe

        if (!$this->isFromStripe($payload, $header, $secret)) {

            Log::info('Invalid Stripe signature');
            return;

        } else {

            $event = json_decode($payload, true);

            if ($event['type'] === 'customer.subscription.updated') {

                Log::info('Event started : customer.subscription.updated');

                $this->hanldeDowngradeSubscription($event);

                Log::info('Event finished : customer.subscription.updated');

            } else if ($event['type'] === 'customer.subscription.deleted') {

                Log::info('Event started : customer.subscription.deleted');


                $this->cancelSubscription($event);

                Log::info('Event finished : customer.subscription.deleted');

            } else if ($event['type'] === 'invoice.payment_succeeded') {

                Log::info('Event started : invoice.payment_succeeded');

                $this->paymentSuccess($event);

                Log::info('Event finished : invoice.payment_succeeded');

            } else if ($event['type'] === 'invoice.payment_failed') {

                Log::info('Event started : invoice.payment_failed');

                $this->paymentFailed($event);

                Log::info('Event finished : invoice.payment_failed');

            }
        }
    }

    private function isFromStripe($payload, $signatureHeader, $secret)
    {
        // Parse Stripe-Signature header (format: t=timestamp,v1=signature)
        $parts = explode(',', $signatureHeader);
        $timestamp = null;
        $v1Signature = null;

        foreach ($parts as $part) {
            if (str_starts_with($part, 't=')) {
                $timestamp = trim(substr($part, 2));
            }
            if (str_starts_with($part, 'v1=')) {
                $v1Signature = trim(substr($part, 3));
            }
        }

        if (!$timestamp || !$v1Signature) {
            return false;
        }

        // Create the signed payload string
        $signedPayload = $timestamp . '.' . $payload;

        // Compute HMAC with the webhook secret
        $computedSignature = hash_hmac('sha256', $signedPayload, $secret);

        // Use constant time string comparison
        return hash_equals($computedSignature, $v1Signature);
    }

    private function hanldeDowngradeSubscription($event)
    {

        try {

            DB::beginTransaction();

            $subscription = $event['data']['object'];

            $product_id = $subscription['items']['data'][0]['plan']['product'];

            $new_package = Package::where('ProductID', $product_id)->first();

            $user = User::where('CusID', $subscription['customer'])
                ->where('SubID', $subscription['id'])->first();

            $old_package = Package::where('PackageID', $user->CurrentPackageID)->first();



            if ($new_package->Price >= $old_package->Price) {
                return;
            }

            Log::info('New Package Product ID : ' . $product_id);

            PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)->update([
                'NextPackageID' => null,
                'status' => 'InActive'
            ]);

            $current_period_start = $subscription['items']['data'][0]['current_period_start'];
            $current_period_end = $subscription['items']['data'][0]['current_period_end'];

            $PaymentStartDate = Carbon::createFromTimestamp($current_period_start)->toDateString();
            $PaymentEndDate = Carbon::createFromTimestamp($current_period_start)->addHours(24)->toDateString();
            $NextRenewalDate = Carbon::createFromTimestamp($current_period_end)->toDateString();
            $PaymentDate = Carbon::createFromTimestamp($current_period_start)->toDateTimeString();

            $newPaymentSubscription = PaymentSubscription::create([
                'UserID' => $user->UserID,
                'PackageID' => $new_package->PackageID,
                'PaymentMethodID' => 1,
                'PaymentAmount' => $new_package->Price,
                'PaymentStartDate' => $PaymentStartDate,
                'PaymentEndDate' => $PaymentEndDate,
                'NextRenewalDate' => $NextRenewalDate,
                'ChecksGiven' => $new_package->CheckLimitPerMonth,
                'ChecksReceived' => 0,
                'ChecksSent' => 0,
                'ChecksUsed' => 0,
                'RemainingChecks' => $new_package->CheckLimitPerMonth,
                'PaymentDate' => $PaymentDate,
                'Status' => 'Active',
                'TransactionID' => $subscription['latest_invoice'],
                'NextPackageID' => null
            ]);

            PaymentHistory::create([
                'PaymentSubscriptionID' => $newPaymentSubscription->PaymentSubscriptionID,
                'PaymentAmount' => $new_package->Price,
                'PaymentDate' => $PaymentDate,
                'TransactionID' => $subscription['latest_invoice'],
                'PaymentStatus' => 'Success'
            ]);

            $user->update([
                'CurrentPackageID' => $new_package->PackageID
            ]);

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            Log::info('Webhook customer.subscription.updated failed. Subscription ID: ' . $subscription['id']);
            Log::info('Webhook customer.subscription.updated error: ' . $e->getMessage() . ' on line ' . $e->getLine());
        }

    }

    private function cancelSubscription($event)
    {

        try {

            DB::beginTransaction();

            $subscription = $event['data']['object'];

            $user = User::where('CusID', $subscription['customer'])
                ->where('SubID', $subscription['id'])->first();

            PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)
                ->whereNotNull('CancelAt')->where('Status', 'Active')->update([
                        'status' => 'Canceled'
                    ]);


            $user_name = $user->FirstName . ' ' . $user->LastName;
            $package = Package::find($user->CurrentPackageID);
            $data = [
                'plan_name' => $package->Name,
            ];
            // $user->update([
            //     'CurrentPackageID' => null
            // ]);

            Mail::to($user->Email)->send(new StripeCancelSubMail(14, $user_name, $data));

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            Log::info('Webhook customer.subscription.deleted failed. Subscription ID: ' . $subscription['id']);
            Log::info('Webhook customer.subscription.deleted error: ' . $e->getMessage() . ' on line ' . $e->getLine());
        }

    }

    private function paymentSuccess($event)
    {

        try {

            Log::info($event);

            DB::beginTransaction();

            $invoice = $event['data']['object'];

            $user = User::where('CusID', $invoice['customer'])->first();

            $PaymentSubscription = PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)
                ->orderBy('PaymentSubscriptionID', 'desc')->first();

            if ($PaymentSubscription) {
                PaymentHistory::create([
                    'PaymentSubscriptionID' => $PaymentSubscription->PaymentSubscriptionID,
                    'PaymentAmount' => $invoice['amount_paid'] / 100,
                    'PaymentDate' => Carbon::createFromTimestamp($invoice['created'])->toDateTimeString(),
                    'PaymentStatus' => 'Success',
                    'PaymentAttempts' => $invoice['attempt_count'],
                    'TransactionID' => $invoice['id'],
                ]);

                $PaymentSubscription->update([
                    'Status' => 'Active'
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            Log::info('Webhook invoice.payment_succeeded failed. Subscription ID: ' . $invoice['id']);
            Log::info('Webhook invoice.payment_succeeded error: ' . $e->getMessage() . ' on line ' . $e->getLine());
        }
    }

    private function paymentFailed($event)
    {

        try {
            Log::info($event);
            DB::beginTransaction();

            $invoice = $event['data']['object'];

            $user = User::where('CusID', $invoice['customer'])->first();


            $PaymentSubscription = PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)
                ->orderBy('PaymentSubscriptionID', 'desc')->first();

            if ($PaymentSubscription) {
                PaymentHistory::create([
                    'PaymentSubscriptionID' => $PaymentSubscription->PaymentSubscriptionID,
                    'PaymentAmount' => $invoice['amount_due'] / 100,
                    'PaymentDate' => Carbon::createFromTimestamp($event['created'])->toDateTimeString(),
                    'PaymentStatus' => 'Failed',
                    'PaymentAttempts' => $invoice['attempt_count'],
                    'TransactionID' => $invoice['id'],
                    'PaymentUrl' => $invoice['hosted_invoice_url']
                ]);


                if ($invoice['attempt_count'] >= 3) {
                    $this->cancelSubscriptionAfterFailedAttempts($user);
                } else {
                    $PaymentSubscription->update([
                        'Status' => 'Pending'
                    ]);
                }
            }
            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            Log::info('Webhook invoice.payment_succeeded failed. Subscription ID: ' . $invoice['id']);
            Log::info('Webhook invoice.payment_succeeded error: ' . $e->getMessage() . ' on line ' . $e->getLine());
        }
    }

    public function cancelSubscriptionAfterFailedAttempts($user)
    {

        $res = $this->subscriptionHelper->cancelImmediately($user->SubID);
        $data_current_package = PaymentSubscription::where('UserId', $user->UserID)->where('Status', 'Pending')->first();

        if (!empty($res) && !empty($data_current_package)) {
            $data_current_package->CancelAt = now()->toDateString();
            $data_current_package->Status = 'Canceled';
            $data_current_package->save();
        }
    }
}

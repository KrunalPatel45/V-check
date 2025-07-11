<?php

namespace App\Http\Controllers;

use App\Models\PaymentHistory;
use App\Models\PaymentSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class StripeWebhookController extends Controller
{

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

        }catch(\Exception $e) {
            
            DB::rollBack();
            Log::info('Webhook customer.subscription.updated failed');
            Log::info('Webhook customer.subscription.updated error: ' . $e->getMessage() . ' on line '.$e->getLine());
        }

    }
}

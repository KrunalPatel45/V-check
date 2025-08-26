<?php

namespace App\Http\Controllers;

use App\Helpers\SubscriptionHelper;
use App\Mail\StripeCancelSubMail;
use App\Models\PaymentHistory;
use App\Models\PaymentSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
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

           if ($event['type'] === 'customer.subscription.deleted') {

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

    private function hanldeDowngradeSubscription($invoice)
    {

        try {

            DB::beginTransaction();

            $product_id = $invoice['lines']['data'][0]['pricing']['price_details']['product'];

            $new_package = Package::where('ProductID', $product_id)->first();
            $sub_id=$invoice['parent']['subscription_details']['subscription'];

            $user = User::where('CusID', $invoice['customer'])
                ->where('SubID', $sub_id)->first();

            // $old_package = Package::where('PackageID', $user->CurrentPackageID)->first();

            // if ($new_package->Price >= $old_package->Price) {
            //     return;
            // }

            PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)
            ->orderBy('PaymentSubscriptionID', 'desc')->first()->update([
                'NextPackageID' => null,
                'Status' => 'Inactive'
            ]);

            // $current_period_start = $invoice['period_start'];
            // $current_period_end = $invoice['period_end'];
            $current_period_start = $invoice['lines']['data'][0]['period']['start'];
            $current_period_end = $invoice['lines']['data'][0]['period']['end'];

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
                'TransactionID' => $invoice['id'],
                'NextPackageID' => null
            ]);

            PaymentHistory::create([
                'PaymentSubscriptionID' => $newPaymentSubscription->PaymentSubscriptionID,
                'PaymentAmount' => $new_package->Price,
                'PaymentDate' => now(),
                'TransactionID' => $invoice['id'],
                'PaymentStatus' => 'Success'
            ]);

            $user->update([
                'CurrentPackageID' => $new_package->PackageID
            ]);

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
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

                $atEndSubCanceled=PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)
                ->whereNotNull('CancelAt')->where('Status', 'Active')
                ->orderBy('PaymentSubscriptionID', 'desc')->first();

                $afterAttemptSubTobeCanceled = PaymentSubscription::where('UserId', $user->UserID)->where('Status', 'Pending')
                    ->orderBy('PaymentSubscriptionID', 'desc')->first();

                if (!empty($afterAttemptSubTobeCanceled)) {
                    $afterAttemptSubTobeCanceled->CancelAt = now()->toDateString();
                    $afterAttemptSubTobeCanceled->Status = 'Canceled';
                    $afterAttemptSubTobeCanceled->save();
                }


                if (!empty($atEndSubCanceled)) {
                    $atEndSubCanceled->Status = 'Canceled';
                    $atEndSubCanceled->save();

                    $user_name = $user->FirstName . ' ' . $user->LastName;
                    $package = Package::find($user->CurrentPackageID);
                    $data = [
                        'plan_name' => $package->Name,
                    ];
                    Mail::to($user->Email)->send(new StripeCancelSubMail(14, $user_name, $data));
                }
           


            
            // $user->update([
            //     'CurrentPackageID' => null
            // ]);
            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            Log::info('Webhook customer.subscription.deleted error: ' . $e->getMessage() . ' on line ' . $e->getLine());
        }

    }

    private function paymentSuccess($event)
    {

        try {

            DB::beginTransaction();

            $invoice = $event['data']['object'];

            $user = User::where('CusID', $invoice['customer'])->first();
            
            $PaymentSubscription = PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)
                ->whereNot('Status','Canceled')->orderBy('PaymentSubscriptionID', 'desc')->first();
            
            $renew = false;
            $is_downgraded = false;
            $is_upgraded = false;

            if ($PaymentSubscription) {

                $amount_paid = $invoice['amount_paid'];
                $current_amount = bcmul((string)$PaymentSubscription->PaymentAmount, '100', 0);
                
                if($invoice['amount_paid'] == $current_amount){

                    $start_date = $invoice['lines']['data'][0]['period']['start'];
                    $end_date = $invoice['lines']['data'][0]['period']['end'];

                    $PaymentStartDate = Carbon::createFromTimestamp($start_date)->toDateString();
                    $PaymentEndDate = Carbon::createFromTimestamp($start_date)->addHours(24)->toDateString();
                    $NextRenewalDate = Carbon::createFromTimestamp($end_date)->toDateString();
                    $PaymentDate = Carbon::createFromTimestamp($start_date)->toDateTimeString();

                    $newPaymentSubscription = PaymentSubscription::create([
                        'UserID' => $user->UserID,
                        'PackageID' => $PaymentSubscription->PackageID,
                        'PaymentMethodID' => 1,
                        'PaymentAmount' => $invoice['amount_paid']/100,
                        'PaymentStartDate' => $PaymentStartDate,
                        'PaymentEndDate' => $PaymentEndDate,
                        'NextRenewalDate' => $NextRenewalDate,
                        'ChecksGiven' => $PaymentSubscription->ChecksGiven,
                        'ChecksReceived' => 0,
                        'ChecksSent' => 0,
                        'ChecksUsed' => 0,
                        'RemainingChecks' => $PaymentSubscription->ChecksGiven,
                        'PaymentDate' => $PaymentDate,
                        'Status' => 'Active',
                        'TransactionID' => $invoice['id'],
                        'NextPackageID' => null
                    ]);
                    $renew = true;

                }elseif($invoice['amount_paid'] < $current_amount){

                    //Handle downgrade plan payments
                    if($invoice['billing_reason'] == 'subscription_cycle'){
                        
                        $this->hanldeDowngradeSubscription($invoice);
                        $is_downgraded = true;
                    }
                }elseif($invoice['amount_paid'] > $current_amount){
                        $is_upgraded = true;
                }
                

                $paymentHistory = PaymentHistory::where('PaymentSubscriptionID', $PaymentSubscription->PaymentSubscriptionID)
                                    ->where('TransactionID', $invoice['id'])->where('PaymentStatus', 'Success')->exists();
                if(!$paymentHistory){

                    if($renew){
                        $payment_sub_id=$newPaymentSubscription->PaymentSubscriptionID;
                    }else{
                        $payment_sub_id=$PaymentSubscription->PaymentSubscriptionID;
                    }
                    if($is_downgraded != true && $is_upgraded != true){
                        
                        PaymentHistory::create([
                            'PaymentSubscriptionID' => $payment_sub_id,
                            'PaymentAmount' => $invoice['amount_paid']/100,
                            'PaymentDate' => now(),
                            'PaymentStatus' => 'Success',
                            'PaymentAttempts' => $invoice['attempt_count'],
                            'TransactionID' => $invoice['id'],
                        ]);
                        // PaymentHistory::create([
                        // 'PaymentSubscriptionID' => $payment_sub_id,
                        // 'PaymentAmount' => (isset($newPaymentSubscription)) ? $newPaymentSubscription->PaymentAmount : $PaymentSubscription->PaymentAmount,
                        // 'PaymentDate' => now(),
                        // 'PaymentStatus' => 'Success',
                        // 'PaymentAttempts' => $invoice['attempt_count'],
                        // 'TransactionID' => (isset($newPaymentSubscription)) ? $newPaymentSubscription->TransactionID : $PaymentSubscription->TransactionID,
                        // ]);
                    }
                }

                if($renew){
                    $PaymentSubscription->update([
                        'Status' => 'Inactive'
                    ]);
                }else{
                    $PaymentSubscription->update([
                        'Status' => 'Active'
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            Log::info('Webhook invoice.payment_succeeded error: ' . $e->getMessage() . ' on line ' . $e->getLine());
        }
    }

    private function paymentFailed($event)
    {

        try {

            DB::beginTransaction();

            $invoice = $event['data']['object'];

            $user = User::where('CusID', $invoice['customer'])->first();


            $PaymentSubscription = PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)
                ->whereNot('Status', 'Canceled')
                ->orderBy('PaymentSubscriptionID', 'desc')->first();

            if ($PaymentSubscription) {
                PaymentHistory::create([
                    'PaymentSubscriptionID' => $PaymentSubscription->PaymentSubscriptionID,
                    'PaymentAmount' => $invoice['amount_due'] / 100,
                    'PaymentDate' => now(),
                    'PaymentStatus' => 'Failed',
                    'PaymentAttempts' => $invoice['attempt_count'],
                    'TransactionID' => $invoice['id'],
                    'PaymentUrl' => $invoice['hosted_invoice_url']
                ]);

                // if ($invoice['attempt_count'] >= 3) {
                //     $this->cancelSubscriptionAfterFailedAttempts($user);
                // } else {
                if ($invoice['attempt_count'] < 4) {
                    $PaymentSubscription->update([
                        'Status' => 'Pending'
                    ]);
                }
                // }
            }
            DB::commit();

        } catch (\Exception $e) {

            DB::rollBack();
            Log::info('Webhook invoice.payment_succeeded error: ' . $e->getMessage() . ' on line ' . $e->getLine());
        }
    }

    // public function cancelSubscriptionAfterFailedAttempts($user)
    // {

    //     $res = $this->subscriptionHelper->cancelImmediately($user->SubID);
    //     $data_current_package = PaymentSubscription::where('UserId', $user->UserID)->where('Status', 'Pending')
    //     ->orderBy('PaymentSubscriptionID', 'desc')->first();

    //     if (!empty($res) && !empty($data_current_package)) {
    //         $data_current_package->CancelAt = now()->toDateString();
    //         $data_current_package->Status = 'Canceled';
    //         $data_current_package->save();
    //     }
    // }
}

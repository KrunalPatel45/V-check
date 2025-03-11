<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentSubscription;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PaymentHistory;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change subscriptions Plan status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $subscriptions = PaymentSubscription::whereDate('NextRenewalDate', $today)->where('Status', '!=', 'Canceled')->get();
        foreach($subscriptions as $subscription){
            $user = User::find($subscriptions->UserID);
            $user->CurrentPackageID = $subscriptions->PaymentSubscriptionID;
            $subscription->Status = 'Active';
            $subscription->save();

            $paymentSubscription = PaymentHistory::where('PaymentSubscriptionID', $subscriptions->PaymentSubscriptionID)->where('PaymentStatus','Pending')->first();
            $paymentSubscription->PaymentStatus = 'Success';
            $paymentSubscription->save();
        }
    }
}

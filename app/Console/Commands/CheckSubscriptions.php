<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentSubscription;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\Log;

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
        Log::info('Check Subscriptions started');

        $today = Carbon::today()->subDay()->toDateString();
        $subscriptions = PaymentSubscription::whereDate('PaymentStartDate', $today)->where('Status', '!=', 'Canceled')->get();
        // foreach($subscriptions as $subscription){
        //     if($subscription->Status == 'Pending') {
        //         $user = User::find($subscription->UserID);
        //         $user->CurrentPackageID = $subscription->PackageID;
        //         $subscription->Status = 'Active';
        //         $subscription->save();

        //         PaymentSubscription::where('UserID', $user->UserID)->where('PaymentSubscriptionID', '!=', $subscription->PaymentSubscriptionID)->delete();


        //         $paymentSubscription = PaymentHistory::where('PaymentSubscriptionID', $subscription->PaymentSubscriptionID)->where('PaymentStatus','Pending')->first();
        //         $paymentSubscription->PaymentStatus = 'Success';
        //         $paymentSubscription->save();
        //     }
        // }
         Log::info('Check Subscriptions ended');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentSubscription;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PaymentHistory;
use Illuminate\Support\Str;
use App\Helpers\SubscriptionHelper;

class BillingAndPlanController extends Controller
{
      protected $SubscriptionHelper;
      public function __construct(SubscriptionHelper $subscriptionHelper)
      {
         $this->subscriptionHelper = $subscriptionHelper;
      }     
      public function index()
      {
        $user = User::where('userID', Auth::user()->UserID)->first();
        $package_id = $user->CurrentPackageID;
        if($user->CurrentPackageID == -1) {
            $package_data = [
                'total_days' => 0,
                'package_name' => 0,
                'expiryDate' => 0,
                'remainingDays' => 0,
                'downgrade_payment' => 0,
                'cancel_plan' => 0,
            ];
        } else {
            $paymentSubscription = PaymentSubscription::where('UserID', Auth::user()->UserID)->where('PackageID', $user->CurrentPackageID)->first();
            $package = Package::find($user->CurrentPackageID);
            $total_days = $package->Duration;
            $package_name = $package->Name;
            $expiry = Carbon::createFromFormat('Y-m-d', $paymentSubscription->NextRenewalDate);
            $expiryDate = $expiry->format('M d, Y');
            $remainingDays = $expiry->diffInDays(Carbon::now(), false);
            $downgrade_payment = PaymentSubscription::where('UserID', Auth::user()->UserID)->where('Status', 'Pending')->first();
            $cancel_plan = PaymentSubscription::where('UserID', Auth::user()->UserID)->where('Status', 'Canceled')->first();
            
            $package_data = [
                'total_days' => $total_days,
                'package_name' => $package_name,
                'expiryDate' => $expiryDate,
                'remainingDays' => abs(round($remainingDays)),
                'downgrade_payment' => $downgrade_payment,
                'cancel_plan' => $cancel_plan,
            ];
        }
        $maxPricePackage = Package::orderBy('price', 'desc')->first();
        $stander_Plan_price = $maxPricePackage->Price;
        $packages = Package::all();
        return view('user.billing_and_plan.index', compact('package_data', 'stander_Plan_price', 'user', 'packages', 'package_id'));
      }

      public function upgragde_plan($id)
      {
          if(!Auth::guard('web')->check()) {
              return redirect()->route('user.login');
          }
  
          $user = User::where('userID', $id)->first();
          $paymentSubscription = PaymentSubscription::where('UserID', $id)->where('PackageID', $user->CurrentPackageID)->first();
          $package = Package::find($user->CurrentPackageID);
          $total_days = $package->Duration;
          $package_name = $package->Name;
          $expiry = Carbon::createFromFormat('Y-m-d', $paymentSubscription->NextRenewalDate);
          $expiryDate = $expiry->format('M d, Y');
          $remainingDays = $expiry->diffInDays(Carbon::now(), false);
          $packages = Package::all();
          $package_data = [
              'total_days' => $total_days,
              'package_name' => $package_name,
              'expiryDate' => $expiryDate,
              'remainingDays' => abs(round($remainingDays)),
          ];
          
          return view('user.billing_and_plan.plan_change', compact('user', 'package_data', 'packages'));
      }
  
      public function change_plan($id, $plan)
      {
         $user = User::find($id);
         $package = Package::find($plan);
         $user_current_package = Package::find($user->CurrentPackageID);
         $data_current_package = PaymentSubscription::where('UserId', $id)->where('PackageID', $user->CurrentPackageID)->first();
         
         if($package->PackageID > $user_current_package->PackageID) {

            $data = [
                'subscription_id' => $data_current_package->TransactionID,
                'new_price_id' => $package->PriceID,
             ];
             $res = $this->subscriptionHelper->updateSubscription($data);

            if(!empty($res)) {
                
                $cancel_or_pending_query = PaymentSubscription::where('UserId', $id)
                ->whereIn('Status', ['Pending']);

                $subscriptionIds = $cancel_or_pending_query->pluck('PaymentSubscriptionID')->toArray();
                
                // Delete from PaymentHistory
                if (!empty($subscriptionIds)) {
                    PaymentHistory::whereIn('PaymentSubscriptionID', $subscriptionIds)->delete();
                }
                
                // Now delete the subscriptions
                $cancel_or_pending_query->delete();

                $price = $package->Price - $user_current_package->Price;
                $paymentSubscription = PaymentSubscription::find($data_current_package->PaymentSubscriptionID);
                $paymentSubscription->update([
                    'UserID' => $id,
                    'PackageID' => $plan,
                    'PaymentMethodID' => 1,
                    'PaymentAmount' => $price,
                    'PaymentStartDate' => $data_current_package->PaymentStartDate,
                    'PaymentEndDate' => $data_current_package->PaymentEndDate,
                    'NextRenewalDate' => $data_current_package->NextRenewalDate,
                    'ChecksGiven' => $package->CheckLimitPerMonth,
                    'RemainingChecks' => $package->CheckLimitPerMonth - $data_current_package->ChecksUsed,
                    'ChecksUsed' => $data_current_package->ChecksUsed,
                    'PaymentDate' => $data_current_package->PaymentDate,
                    'PaymentAttempts' => 0 ,
                    'TransactionID' => $res['subscription'],
                    'Status' => 'Active', 
                ]);
    
                $paymentSubscriptionId = $paymentSubscription->PaymentSubscriptionID;
        
                $paymentSubscription = PaymentHistory::create([
                    'PaymentSubscriptionID' => $paymentSubscriptionId,
                    'PaymentAmount' => $price,
                    'PaymentDate' => $data_current_package->PaymentDate,
                    'PaymentStatus' => 'Success',
                    'PaymentAttempts' => 0,
                    'TransactionID' => $paymentSubscription->TransactionID,
                ]);
  
                $user->CurrentPackageID = $plan;
                $user->save();
            }

         } else {
  
          $paymentStartDate = Carbon::parse($data_current_package->NextRenewalDate);
  
          $paymentEndDate = $paymentStartDate->copy()->addHours(24);
  
          $nextRenewalDate = $paymentStartDate->copy()->addDays($package->Duration);
  
          $paymentSubscription = PaymentSubscription::create([
              'UserID' => $id,
              'PackageID' => $plan,
              'PaymentMethodID' => 1,
              'PaymentAmount' => $package->Price,
              'PaymentStartDate' => $paymentStartDate,
              'PaymentEndDate' => $paymentEndDate,
              'NextRenewalDate' => $nextRenewalDate,
              'ChecksGiven' => $package->CheckLimitPerMonth,
              'RemainingChecks' => $package->CheckLimitPerMonth,
              'PaymentDate' => $paymentStartDate,
              'PaymentAttempts' => 0 ,
              'TransactionID' => Str::random(10),
              'Status' => 'Pending', 
          ]);
  
          $paymentSubscriptionId = $paymentSubscription->PaymentSubscriptionID;
          $paymentSubscription = PaymentHistory::create([
              'PaymentSubscriptionID' => $paymentSubscriptionId,
              'PaymentAmount' => $package->Price,
              'PaymentDate' => $paymentStartDate,
              'PaymentStatus' => 'Pending',
              'PaymentAttempts' => 0,
              'TransactionID' => $paymentSubscription->TransactionID,
          ]);

         }
         return redirect()->route('billing_and_plan')->with('success', 'Your changed successfully');
      }

      public function cancel_plan($id)
      {
         $user = User::find($id);
         $data_current_package = PaymentSubscription::where('UserId', $id)->where('Status', 'Active')->first();
         $data_current_package->Status = 'Canceled';
         $data_current_package->save();

         PaymentSubscription::where('UserId', $id)->where('Status', 'Pending')->delete();

         return redirect()->route('billing_and_plan')->with('success', 'Your plan has been canceled');
      }

      public function invoice(Request $request)
    {
        if ($request->ajax()) {
            $paymentSubscriptionIds = PaymentSubscription::where('UserID', Auth::id())->pluck('PaymentSubscriptionID')->toArray();
            $invoice = PaymentHistory::whereIn('PaymentSubscriptionID', $paymentSubscriptionIds);

            return datatables()->of($invoice)
                ->addIndexColumn()
                ->addColumn('PaymentDate', function ($row) {
                    return Carbon::parse($row->PaymentDate)->format('m/d/Y'); 
                })
                ->addColumn('PaymentStatus', function ($row) {
                    return '<span class="badge ' .
                        ($row->PaymentStatus == 'Success' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">'. ($row->PaymentStatus == 'Success' ? 'paid' : 'unpaid'). '</span>';
                })
                ->rawColumns(['PaymentStatus'])
                ->make(true);
        }
    }
}

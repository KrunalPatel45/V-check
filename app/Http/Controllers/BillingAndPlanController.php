<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentSubscription;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BillingAndPlanController extends Controller
{
      public function index()
      {
        $user = User::where('userID', Auth::user()->UserID)->first();
        $paymentSubscription = PaymentSubscription::where('UserID', Auth::user()->UserID)->where('PackageID', $user->CurrentPackageID)->first();
        $package = Package::find($user->CurrentPackageID);
        $total_days = $package->Duration;
        $package_name = $package->Name;
        $expiry = Carbon::createFromFormat('Y-m-d', $paymentSubscription->NextRenewalDate);
        $expiryDate = $expiry->format('M d, Y');
        $remainingDays = $expiry->diffInDays(Carbon::now(), false);
        $package_data = [
            'total_days' => $total_days,
            'package_name' => $package_name,
            'expiryDate' => $expiryDate,
            'remainingDays' => abs(round($remainingDays)),
        ];
        $maxPricePackage = Package::orderBy('price', 'desc')->first();
        $stander_Plan_price = $maxPricePackage->Price;
        return view('user.billing_and_plan.index', compact('package_data', 'stander_Plan_price'));
      }
}

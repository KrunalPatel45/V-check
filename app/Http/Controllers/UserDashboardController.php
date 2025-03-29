<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\PaymentSubscription;
use App\Models\Package;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Payors;
use App\Models\PaymentHistory;

class UserDashboardController extends Controller
{
    public function index()
    {
        if(!Auth::check()) {
            return redirct()->route('user.login');
        }

        $user = User::where('userID', Auth::user()->UserID)->first();
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

        $given_checks = ($paymentSubscription->ChecksGiven == 0) ? 'Unlimited' : $paymentSubscription->ChecksGiven;
        $used_checks = ($paymentSubscription->ChecksGiven == 0) ? '-' :$paymentSubscription->ChecksUsed;
        $remaining_checks =($paymentSubscription->ChecksGiven == 0) ? '-'  : $paymentSubscription->RemainingChecks;
        
        $total_vendor = Payors::where('UserID', Auth::user()->UserID)
                        ->whereIn('Type', ['Payor'])
                        ->count();

        //
        $total_client = Payors::where('UserID', Auth::user()->UserID)
                        ->whereIn('Type', ['Payee'])
                        ->count();                
        //
        $total_companies = Company::where('UserID', Auth::user()->UserID)->count();

        return view('content.dashboard.user-dashboards-analytics', compact('package_data', 'total_vendor', 'total_client', 'total_companies', 'given_checks', 'used_checks', 'remaining_checks'));
    }

    public function profile()
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

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

        return view('user.profile.profile', compact('user', 'package_data'));
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            // 'username' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $admin = User::where('UserID', $request->user_id)->first();
        // $admin->Username = $request->username;
        $admin->Email = $request->email;
        $admin->FirstName = $request->firstname;
        $admin->LastName = $request->lastname;
        $admin->PhoneNumber = $request->phone_number;
        $admin->UpdatedAt = now();
        $admin->save();

        return redirect()->route('user.profile')->with('profile_success', 'Profile updated successfully');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required|min:6',
        ]);

        if (!Hash::check($request->old_password, Auth::user()->PasswordHash)) {
            return back()->withErrors(['old_password' => 'The old password is incorrect.']);
        }

        $admin = User::where('UserID', $request->user_id)->first();
        $admin->PasswordHash = Hash::make($request->new_password);
        $admin->save();

        // Redirect back with success message
        return redirect()->route('user.profile')->with('pass_success', 'Password changed successfully');
    }
}

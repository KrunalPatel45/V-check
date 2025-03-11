<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentSubscription;
use App\Models\PaymentHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    public function register()
    {
        if(Auth::check()) {
            return redirect()->route('user.dashboard');
        }
        return view('frontend.auth.register');
    }

    public function login()
    {
        if(Auth::check()) {
            return redirect()->route('user.dashboard');
        }
        if(Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        return view('frontend.auth.login');
    }

    public function package(Request $request)
    {
        // if(Auth::check()) {
        //     return redirect()->route('user.dashboard');
        // }
        $userId = request()->query('user_id');
        $packages = Package::where('Status', 'Active')->get();
        return view('frontend.auth.package', compact('packages', 'userId'));
    }

    public function login_action(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check credentials manually without using guards
        $user = User::where('Email', $request->email)->first();

        if(!empty($user) && empty($user->CurrentPackageID)) {
            return redirect()->route('user.package', ['user_id' => $user->UserID]); 
        } 
 
        if(!empty($user) && $user->Status == 'Inactive') {
            return redirect()->back()->withErrors(['email' => 'User Status is not Active'])->withInput();
        } 

        if ($user && Hash::check($request->password, $user->PasswordHash)) {
            Auth::login($user);
            return redirect()->route('user.dashboard');
        }
        // Authentication failed, redirect back with an error message
        return redirect()->back()->withErrors(['email' => 'Invalid login credentials'])->withInput();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|unique:User,Username|max:255',
            'email' => 'required|string|email|unique:User,Email|max:255',
            'phone_number' => 'required|string|max:20',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'FirstName' => $request->firstname,
            'LastName' => $request->lastname,
            'Username' => $request->username,
            'Email' => $request->email,
            'PhoneNumber' => $request->phone_number,
            'PasswordHash' => Hash::make($request->password),
            'Status' => 'Inactive',
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
        ]);

        return redirect()->route('user.package', ['user_id' => $user->UserID]);
    }

    public function select_package(Request $request, $id, $plan) 
    {
        $user = User::find($id);
        $user->CurrentPackageID = $plan;
        $user->Status = 'Active';
        $user->save();

        $PaymentSubscription_plan = PaymentSubscription::where('UserID', $id)->whereIn('Status', ['Canceled', 'Pending'])->delete();


        $packages = Package::find($plan);

        $paymentStartDate = Carbon::now();

        $paymentEndDate = $paymentStartDate->copy()->addHours(24);

        $nextRenewalDate = $paymentStartDate->copy()->addDays($packages->Duration);


        $paymentSubscription = PaymentSubscription::create([
            'UserID' => $id,
            'PackageID' => $plan,
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
            'TransactionID' => Str::random(10),
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

        return redirect()->route('user.login')->with('success', 'Account created successful!');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('user.login'); // Redirect to the admin login page
    }

    public function expired_sub()
    {
        return view('frontend.auth.expired');
    }
}

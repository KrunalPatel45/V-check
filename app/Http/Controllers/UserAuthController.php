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
use App\Models\EmailTemplate;
use App\Models\UserHistory;
use App\Mail\SendEmail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use App\Helpers\SubscriptionHelper;
use App\Mail\AdminMail;

class UserAuthController extends Controller
{
    protected $SubscriptionHelper;
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

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
        $userId = request()->query('user_id');
        $packages = Package::where('Status', 'Active')->get();
        return view('frontend.auth.package', compact('packages', 'userId'));
    }

    public function login_action(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required',  
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $user = User::where('Email', $request->email)->first();
        
        if(empty($user)) {
            return redirect()->back()->withErrors(['email' => 'Invalid login credentials'])->withInput();
        }
        
        if (!empty($user) && $user->Status == 'Inactive') {
            return redirect()->back()->withErrors(['login' => 'User status is not Active'])->withInput();
        }
        
        $packag_c = PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)->count();
        

        if (!empty($user) && $packag_c == 0 && $user->CurrentPackageID != -1) {
            return redirect()->route('user.package', ['user_id' => $user->UserID]);
        }

        if ($user && Hash::check($request->password, $user->PasswordHash)) {
            Auth::login($user);
            $name = $user->FirstName . ' ' .$user->LastName;
            Mail::to($user->Email)->send(new SendEmail(2, $name));
            $user_history = UserHistory::where('UserID', $user->UserID)->first();
            if(!empty($user_history)){
                $user_history->last_login = now();
                $user_history->ip = $request->ip();
                $user_history->save();
            } else {
                UserHistory::create([
                    'UserID' => $user->UserID,
                    'last_login' => now(),
                    'ip' => $request->ip(),
                ]);
            }
            return redirect()->route('user.dashboard');
        }

        // Authentication failed
        return redirect()->back()->withErrors(['email' => 'Invalid login credentials'])->withInput();
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            // 'username' => 'required|string|unique:User,Username|max:255',
            'address' => 'required',
            'email' => 'required|string|email|unique:User,Email|max:255',
            'phone_number' => 'required|string|max:20',
            'company_name' => 'required'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'name' => $request->firstname . ' ' . $request->lastname,
            'email' => $request->email,
        ];

        $cus = $this->subscriptionHelper->addCustomer($data);

        $user = User::create([
            'FirstName' => $request->firstname,
            'LastName' => $request->lastname,
            'Email' => $request->email,
            'Address' => $request->address,
            'PhoneNumber' => $request->phone_number,
            'PasswordHash' => Hash::make($request->password),
            'Status' => 'Inactive',
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
            'CusID' => !empty($cus['id']) ? $cus['id'] : NULL,
            'CompanyName' => $request->company_name,
            'City' => $request->city,
            'State' => $request->state,
            'Zip' => $request->zip,
            'timezone' => $request->timezone,
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

    public function email()
    {
        $emailContent = EmailTemplate::find(1);
        return view('user.emails.mail', compact('emailContent'));
    }

    public function showForgotPasswordForm()
    {
        return view('frontend.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:User,email',
        ]);

        $user = User::where('Email', $request->email)->first();

        if(!empty($user)) {
            $token = Str::random(60);
            $user->reset_token = $token;
            $user->reset_token_expiry = Carbon::now()->addMinutes(30);
            $user->save();
    
            $name = $user->FirstName . ' ' .$user->LastName;
    
            Mail::to($user->Email)->send(new SendEmail(3, $name, $token));    
            return back()->with('success', 'We have emailed your password reset link!');
        }
        return back()->with('error', 'The email address you entered does not exist');
    }

    public function showResetForm($token)
    {
        $user = User::where('reset_token', $token)
                    ->where('reset_token_expiry', '>', Carbon::now())
                    ->first();

        if (!$user) {
            return redirect()->route('user.login')->with('error', 'Invalid or expired token!');
        }

        return view('frontend.auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6',
            'confirm-password' => 'required|min:6' ,
        ]);

        $user = User::where('reset_token', $request->token)
                    ->where('reset_token_expiry', '>', Carbon::now())
                    ->first();

        if (!$user) {
            return redirect()->route('user.login')->with('error', 'Invalid or expired token!');
        }

        // Update password and clear reset token
        $user->PasswordHash = Hash::make($request->password);
        $user->reset_token = null;
        $user->reset_token_expiry = null;
        $user->save();

        return redirect()->route('user.login')->with('success', 'Your password has been reset successfully!');
    }

    public function select_free_package(Request $request, $id, $plan)
    {
        $user = User::find($id);
        $user->CurrentPackageID = -1;
        $user->Status = 'Active';

        $packages = Package::findOrFail($plan);

        $paymentStartDate = Carbon::now();
        $paymentEndDate = $paymentStartDate->copy()->addHours(24);
        $nextRenewalDate = $paymentStartDate->copy()->addDays((int)$packages->Duration);

        $paymentSubscription = PaymentSubscription::create([
            'UserID' => $user->UserID,
            'PackageID' => $user->CurrentPackageID,
            'PaymentMethodID' => 1,
            'PaymentAmount' => $packages->Price,
            'PaymentStartDate' => $paymentStartDate,
            'PaymentEndDate' => $paymentEndDate,
            'NextRenewalDate' => $nextRenewalDate,
            'ChecksGiven' => $packages->CheckLimitPerMonth,
            'ChecksUsed' => 0,
            'RemainingChecks' => 0,
            'PaymentDate' => $paymentStartDate,
            'PaymentAttempts' => 0,
            'TransactionID' => 'Trial',
            'InvoiceID' => 'Tiral',
            'Status' => 'Active',
        ]);

         $user->save();
        
        $name = $user->FirstName . ' ' .$user->LastName;
        Mail::to($user->Email)->send(new SendEmail(1, $name));
        Mail::to(env('ADMIN_EMAIL'))->send(new AdminMail(10, 'Trial', $name, $user->Email));

        return redirect()->route('user.login')->with('success', 'Account created successful!');
    
    
    }
}  

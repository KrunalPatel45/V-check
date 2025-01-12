<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserDashboardController extends Controller
{
    public function index()
    {
        if(!Auth::check()) {
            return redirct()->route('user.login');
        }
        return view('content.dashboard.dashboards-analytics');
    }

    public function profile()
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        $user = User::where('userID', Auth::user()->UserID)->first();
        return view('user.profile.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $admin = User::where('UserID', $request->user_id)->first();
        $admin->Username = $request->username;
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

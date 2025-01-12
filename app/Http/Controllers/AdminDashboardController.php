<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Package;

class AdminDashboardController extends Controller
{
    public function index()
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        return view('content.dashboard.dashboards-analytics');
    }

    public function profile()
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = AdminUser::where('AdminID', Auth::guard('admin')->user()->AdminID)->first();
        return view('admin.profile.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $admin = AdminUser::where('AdminID', $request->admin_id)->first();
        $admin->Username = $request->username;
        $admin->Email = $request->email;
        $admin->UpdatedAt = now();
        $admin->save();

        return redirect()->route('admin.profile')->with('profile_success', 'Profile updated successfully');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required|min:6',
        ]);

        if (!Hash::check($request->old_password, Auth::guard('admin')->user()->PasswordHash)) {
            return back()->withErrors(['old_password' => 'The old password is incorrect.']);
        }

        $admin = AdminUser::where('AdminID', $request->admin_id)->first();
        $admin->PasswordHash = Hash::make($request->new_password);
        $admin->save();

        // Redirect back with success message
        return redirect()->route('admin.profile')->with('pass_success', 'Password changed successfully');
    }

    public function users()
    {
        $users = User::all();
        foreach($users as $user) {
            $package = Package::find($user->CurrentPackageID);
            $user->package = "";
            $user->package_price = 0;
            if(!empty($package)) {
                $user->package = $package->Name;
                $user->package_price = $package->Price;
            }
        }

        return view('admin.user.index', compact('users'));
    }
}

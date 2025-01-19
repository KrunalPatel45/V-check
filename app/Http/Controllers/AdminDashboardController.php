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

    public function users(Request $request)
    {
        if ($request->ajax()) {
            $users = User::all();

            foreach ($users as $user) {
                $package = Package::find($user->CurrentPackageID);
                $user->package = $package ? $package->Name : 'N/A'; 
                $user->package_price = $package ? $package->Price : 0;
            }

            return datatables()->of($users)
                ->addIndexColumn()
                ->addColumn('status', function ($user) {
                    return $user->Status == 'Active' 
                        ? '<span class="badge bg-label-primary">' . $user->Status . '</span>' 
                        : '<span class="badge bg-label-warning">' . $user->Status . '</span>';
                })
                ->addColumn('created_at', function ($user) {
                    return $user->CreatedAt;
                })
                ->addColumn('updated_at', function ($user) {
                    return $user->UpdatedAt;
                })
                ->rawColumns(['status', 'created_at', 'updated_at']) // Allow raw HTML content
                ->make(true);
        }

        return view('admin.user.index');
    }
}

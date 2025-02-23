<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Package;
use App\Models\PaymentSubscription;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Checks;
use App\Models\Company;
use App\Models\Payors;

class AdminDashboardController extends Controller
{
    public function index()
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        $total_users = User::count();
        $total_checks = PaymentSubscription::sum('ChecksGiven');
        $total_revanue = PaymentSubscription::sum('PaymentAmount');
        $total_used_checks = PaymentSubscription::sum('ChecksUsed');
        $total_unused_checks = $total_checks - $total_used_checks;
        $total_basic = PaymentSubscription::select('PaymentSubscriptionID')->where('Status', 'Active')->where('PackageID', 1)->count();
        $total_silver = PaymentSubscription::select('PaymentSubscriptionID')->where('Status', 'Active')->where('PackageID', 2)->count();
        $total_gold = PaymentSubscription::select('PaymentSubscriptionID')->where('Status', 'Active')->where('PackageID', 3)->count();
        return view('content.dashboard.dashboards-analytics', compact('total_users', 'total_checks', 'total_revanue', 'total_used_checks', 'total_unused_checks', 'total_basic', 'total_silver', 'total_gold', ));
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
                    return Carbon::parse($user->CreatedAt)->format('m/d/Y'); // Convert to MM/DD/YYYY
                })
                ->addColumn('updated_at', function ($user) {
                    return Carbon::parse($user->UpdatedAt)->format('m/d/Y'); // Convert to MM/DD/YYYY
                })
                ->rawColumns(['status', 'created_at', 'updated_at']) // Allow raw HTML content
                ->addColumn('actions', function ($user) {
                    // Dynamically build URLs for the edit and delete actions
                    $editUrl = route('admin.user.edit', ['id' => $user->UserID]);
                    $deleteUrl = route('admin.user.delete', ['id' => $user->UserID]);

                    return '
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="' . $editUrl . '" class="dropdown-item">
                                    <i class="ti ti-pencil me-1"></i> Edit
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                data-bs-target="#delete' . $user->UserID . '">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </a>
                            </div>
                        </div>

                        <!-- Modal for Deleting -->
                        <div class="modal fade" id="delete' . $user->UserID . '" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Package</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this Package?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                                            ' . csrf_field() . '
                                            ' . method_field('DELETE') . '
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'created_at', 'updated_at', 'actions'])
                ->make(true);
        }

        return view('admin.user.index');
    }

    public function user_edit(Request $request, $id)
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
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
        $check_used = $paymentSubscription->ChecksUsed;
        $remaining_checks = $paymentSubscription->RemainingChecks;
        $type = 'default';
        if(!empty($request->type)) {
            $type = $request->type;
        }
        $maxPricePackage = Package::orderBy('price', 'desc')->first();
        $stander_Plan_price = $maxPricePackage->Price;
        return view('admin.user.user_detail_page', compact('user', 'package_data', 'packages', 'check_used', 'remaining_checks', 'package', 'type', 'stander_Plan_price'));
    }

    public function updateUserProfile(Request $request)
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

        return redirect()->route('admin.user.edit', ['id' => $request->user_id])->with('profile_success', 'Profile updated successfully');
    }

    public function changeUserPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required|min:6',
        ]);


        $admin = User::where('UserID', $request->user_id)->first();
        $admin->PasswordHash = Hash::make($request->new_password);
        $admin->save();

        // Redirect back with success message
        return redirect()->route('admin.user.edit', ['id' => $request->user_id, 'type' => 'security'])->with('pass_success', 'Password changed successfully');
    }

    public function user_delete($id)
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = User::find($id);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully');
    }

    public function change_plan(Request $request)
    {
        $user = User::find($request->user_id);
        $plan = $request->plan;

        if($plan != $user->CurrentPackageID)  {
            $user->CurrentPackageID = $request->plan;
            $user->Status = 'Active';
            $user->save();
    
    
            $packages = Package::find($plan);
    
            $paymentStartDate = Carbon::now();
    
            $paymentEndDate = $paymentStartDate->copy()->addHours(24);
    
            $nextRenewalDate = $paymentStartDate->copy()->addDays($packages->Duration);
            $paymentSubscription = PaymentSubscription::where('UserID', $request->user_id)->where('PackageID', $request->old_plan)->first();
    
            if(!empty($paymentSubscription)) {
                $paymentSubscription->update([
                    'UserID' => $request->user_id,
                    'PackageID' => $plan,
                    'PaymentMethodID' => 1,
                    'PaymentAmount' => $packages->Price,
                    'PaymentStartDate' => $paymentStartDate,
                    'PaymentEndDate' => $paymentEndDate,
                    'NextRenewalDate' => $nextRenewalDate,
                    'ChecksGiven' => $packages->CheckLimitPerMonth,
                    'RemainingChecks' => $packages->CheckLimitPerMonth,
                    'PaymentDate' => $paymentStartDate,
                    'PaymentAttempts' => 0 ,
                    'TransactionID' => Str::random(10),
                    'Status' => 'Active', 
                ]);

            }
        }
        return redirect()->route('admin.user.edit', ['id' => $request->user_id])->with('success', 'User plan changed successfully');
    }

    public function user_profile_edit($id)
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = User::where('userID', $id)->first();
        return view('admin.user.edit', compact('user'));
    }

    public function upgragde_plan($id)
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
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

        return view('admin.user.plan_upgrade', compact('user', 'package_data', 'packages'));
    }


    public function company(Request $request, $id)
    {
        if ($request->ajax()) {
            $companies = Company::where('UserID', $id)->get();

            return datatables()->of($companies)
                ->addIndexColumn()
                ->addColumn('logo', function ($row) {
                    if(!empty($row->Logo)) {
                        return '<img src="' . asset('storage/' . $row->Logo) . '" alt="Company Logo" style="width: 50px;">';
                    } else {
                        return '<img src="' . asset('assets/img/empty.jpg') . '" alt="Company Logo" style="width: 50px;">';
                    }
                })
                ->addColumn('CreatedAt', function ($row) {
                    return Carbon::parse($row->CreatedAt)->format('m/d/Y'); 
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge ' .
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">' . $row->Status . '</span>';
                })
                ->rawColumns(['logo', 'status'])
                ->make(true);
        }
    }

    public function invoice(Request $request, $id)
    {
        if ($request->ajax()) {
            $invoice = PaymentSubscription::where('UserID', $id)->get();

            return datatables()->of($invoice)
                ->addIndexColumn()
                ->addColumn('PaymentDate', function ($row) {
                    return Carbon::parse($row->PaymentDate)->format('m/d/Y'); 
                })
                ->addColumn('Status', function ($row) {
                    return '<span class="badge ' .
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">'. ($row->Status == 'Active' ? 'paid' : 'unpaid'). '</span>';
                })
                ->rawColumns(['Status'])
                ->make(true);
        }
    }

    public function client(Request $request, $id)
    {

        if ($request->ajax()) {
            $payors = Payors::where('UserID', $id)
                ->whereIn('Type', ['Client', 'Both'])
                ->get();

            return datatables()->of($payors)
                ->addIndexColumn()
                ->addColumn('CreatedAt', function ($row) {
                    return Carbon::parse($row->CreatedAt)->format('m/d/Y'); 
                })
                ->addColumn('Status', function ($row) {
                    return '<span class="badge ' . 
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') . 
                        '">' . $row->Status . '</span>';
                })
                ->rawColumns(['Status'])
                ->make(true);
        }
    }
    public function vendor(Request $request, $id)
    {

        if ($request->ajax()) {
            $payors = Payors::where('UserID', $id)
                ->whereIn('Type', ['Vendor', 'Both'])
                ->get();

            return datatables()->of($payors)
                ->addIndexColumn()
                ->addColumn('CreatedAt', function ($row) {
                    return Carbon::parse($row->CreatedAt)->format('m/d/Y'); 
                })
                ->addColumn('Status', function ($row) {
                    return '<span class="badge ' . 
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') . 
                        '">' . $row->Status . '</span>';
                })
                ->rawColumns(['Status'])
                ->make(true);
        }
    }
}
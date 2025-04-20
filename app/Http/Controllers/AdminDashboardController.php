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
use App\Models\PaymentHistory;
use App\Models\UserHistory;

class AdminDashboardController extends Controller
{
    public function index()
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        $total_users = User::count();
        $total_checks = PaymentSubscription::sum('ChecksGiven');
        $total_revanue = PaymentHistory::where('PaymentStatus', 'Success')->sum('PaymentAmount');
        $month_revanue = PaymentHistory::where('PaymentStatus', 'Success')
                        ->whereMonth('PaymentDate', Carbon::now()->month)
                        ->whereYear('PaymentDate', Carbon::now()->year)
                        ->sum('PaymentAmount');
                        
        $total_used_checks = PaymentSubscription::sum('ChecksUsed');
        $total_unused_checks = abs($total_checks - $total_used_checks);

        $package_selected_user = User::whereNotNull('CurrentPackageID')->count();
        $package_data = [];
        $packages = Package::where('Status', 'Active')->get();
        foreach($packages as $key => $package) {
            $package_data[$key]['name'] = $package->Name;
            $package_data[$key]['total_count'] = PaymentSubscription::select('PaymentSubscriptionID')->where('Status', 'Active')->where('PackageID', $package->PackageID)->count();
        }
        // $total_basic = PaymentSubscription::select('PaymentSubscriptionID')->where('Status', 'Active')->where('PackageID', 1)->count();
        // $total_silver = PaymentSubscription::select('PaymentSubscriptionID')->where('Status', 'Active')->where('PackageID', 2)->count();
        // $total_gold = PaymentSubscription::select('PaymentSubscriptionID')->where('Status', 'Active')->where('PackageID', 3)->count();
        return view('content.dashboard.dashboards-analytics', compact('total_users', 'total_checks', 'total_revanue', 'total_used_checks', 'total_unused_checks', 'package_data', 'package_selected_user', 'month_revanue'));
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
                if($user->CurrentPackageID == '-1') {
                    $user->package = 'TRIAL';
                } 
                $user->package_price = $package ? $package->Price : 0;
            }

            return datatables()->of($users)
                ->addIndexColumn()
                ->editColumn('PhoneNumber', function ($user) {
                    return $this->formatPhoneNumber($user->PhoneNumber);
                })
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
        $user_history = UserHistory::where('UserID', $id)->first();
        if(!empty($user_history)) {
            $user_history->last_login = !empty($user_history->last_login)? Carbon::parse($user_history->last_login)->format('M, d Y h:i A'): '';
        }
        $paymentSubscription = PaymentSubscription::where('UserID', $id)->where('PackageID', $user->CurrentPackageID)->first();
        $package = Package::find($user->CurrentPackageID);
        $total_days = !empty($package->Duration) ? $package->Duration : '';
        $package_name = !empty($package->Name) ? $package->Name : '';
        $expiry = !empty($paymentSubscription->NextRenewalDate) ? Carbon::createFromFormat('Y-m-d', $paymentSubscription->NextRenewalDate) : '';
        $expiryDate = !empty($expiry) ? $expiry->format('M d, Y') : '';
        $remainingDays = !empty($expiry) ? $expiry->diffInDays(Carbon::now(), false) : '';
        $packages = Package::all();
        $downgrade_payment = PaymentSubscription::where('UserID', $id)->where('Status', 'Pending')->first();
        $cancel_plan = PaymentSubscription::where('UserID', $id)->where('Status', 'Canceled')->first();
        
        $check_used = '-';
        $remaining_checks = '-';
        if(!empty($paymentSubscription)) {
            $check_used = ($paymentSubscription->ChecksGiven == 0) ? '-' :$paymentSubscription->ChecksUsed;
            $remaining_checks =($paymentSubscription->ChecksGiven == 0) ? '-'  : $paymentSubscription->RemainingChecks;    
        }

        $package_data = [
            'total_days' => $total_days,
            'package_name' => $package_name,
            'expiryDate' => $expiryDate,
            'remainingDays' => !empty($remainingDays) ? abs(round($remainingDays)) : '',
            'downgrade_payment' => $downgrade_payment,
            'cancel_plan' => $cancel_plan,
        ];
       $type = 'default';
        if(!empty($request->type)) {
            $type = $request->type;
        }
        $maxPricePackage = Package::orderBy('price', 'desc')->first();
        $stander_Plan_price = $maxPricePackage->Price;
        $currentPackage = $user->CurrentPackageID;
        return view('admin.user.user_detail_page', compact('user', 'package_data', 'packages', 'check_used', 'remaining_checks', 'package', 'type', 'stander_Plan_price', 'currentPackage', 'user_history'));
    }

    public function updateUserProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'address' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $admin = User::where('UserID', $request->user_id)->first();
        $admin->Address = $request->address;
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
        $paymentSubscription = PaymentSubscription::where('UserID', $id);
        $paymentSubscriptionIds = $paymentSubscription->pluck('PaymentSubscriptionID')->toArray();
        PaymentHistory::whereIn('PaymentSubscriptionID', $paymentSubscriptionIds)->delete();
        $paymentSubscription->delete();
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully');
    }

    // public function change_plan(Request $request)
    // {
    //     $user = User::find($request->user_id);
    //     $plan = $request->plan;

    //     if($plan != $user->CurrentPackageID)  {
    //         $user->CurrentPackageID = $request->plan;
    //         $user->Status = 'Active';
    //         $user->save();
    
    
    //         $packages = Package::find($plan);
    
    //         $paymentStartDate = Carbon::now();
    
    //         $paymentEndDate = $paymentStartDate->copy()->addHours(24);
    
    //         $nextRenewalDate = $paymentStartDate->copy()->addDays($packages->Duration);
    //         $paymentSubscription = PaymentSubscription::where('UserID', $request->user_id)->where('PackageID', $request->old_plan)->first();
    
    //         if(!empty($paymentSubscription)) {
    //             $paymentSubscription->update([
    //                 'UserID' => $request->user_id,
    //                 'PackageID' => $plan,
    //                 'PaymentMethodID' => 1,
    //                 'PaymentAmount' => $packages->Price,
    //                 'PaymentStartDate' => $paymentStartDate,
    //                 'PaymentEndDate' => $paymentEndDate,
    //                 'NextRenewalDate' => $nextRenewalDate,
    //                 'ChecksGiven' => $packages->CheckLimitPerMonth,
    //                 'RemainingChecks' => $packages->CheckLimitPerMonth,
    //                 'PaymentDate' => $paymentStartDate,
    //                 'PaymentAttempts' => 0 ,
    //                 'TransactionID' => Str::random(10),
    //                 'Status' => 'Active', 
    //             ]);

    //         }
    //     }
    //     return redirect()->route('admin.user.edit', ['id' => $request->user_id])->with('success', 'User plan changed successfully');
    // }

    public function user_profile_edit($id)
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = User::where('userID', $id)->first();
        $packages = Package::all();
        return view('admin.user.edit', compact('user', 'packages'));
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
        
        return view('admin.user.plan_change', compact('user', 'package_data', 'packages'));
    }

    public function change_plan($id, $plan)
    {
       $user = User::find($id);
       $package = Package::find($plan);
       $user_current_package = Package::find($user->CurrentPackageID);
       $data_current_package = PaymentSubscription::where('UserId', $id)->where('PackageID', $user->CurrentPackageID)->first();
       if($package->PackageID > $user_current_package->PackageID) {
        
        $cancel_or_pending_query = PaymentSubscription::where('UserId', $id)
        ->whereIn('Status', ['Pending']);
        
        // Get subscription IDs
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
            'PaymentDate' => $data_current_package->PaymentDate,
            'PaymentAttempts' => 0 ,
            'TransactionID' => Str::random(10),
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
       return redirect()->route('admin.user.edit', ['id' => $id, 'type' => 'billing'])->with('success', 'User plan changed successfully');
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
            $paymentSubscriptionIds = PaymentSubscription::where('UserID', $id)->pluck('PaymentSubscriptionID')->toArray();
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

    public function formatPhoneNumber($number) {
        // Remove all non-digit characters
        $number = preg_replace('/\D/', '', $number);

        // Get the last 10 digits
        $number = substr($number, -10);

        // Format as 3-3-4
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $number);
    }

    public function changeStatus(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->Status = $request->status == 'active' ? 'Active' : 'Inactive';
        $user->save();
        return response()->json(['message' => 'Status updated successfully.']);
    }
        
}

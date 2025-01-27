<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Payors;
use App\Models\Checks;
use Illuminate\Support\Facades\Validator;
use PDF;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\File;
use App\Models\Package;
use App\Models\PaymentSubscription;

class CheckController extends Controller
{
    public function process_payment(Request $request)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $checks = Checks::where('UserID', Auth::id())->where('CheckType', 'Process Payment')->get();

            return datatables()->of($checks)
                ->addIndexColumn()
                ->addColumn('CompanyID', function ($row) {
                    $company = Company::find($row->CompanyID);
                    return $company->Name;
                })
                ->addColumn('EntityID', function ($row) {
                    $payor = Payors::find($row->EntityID);
                    return $payor->Name;
                })
                ->addColumn('Status', function ($row) {
                    $statusOptions = ['Pending', 'Issued', 'Cleared', 'Expired', 'Cancelled'];
                    $optionsHtml = '';
                
                    // Loop through options and set the selected option if it matches the row status
                    foreach ($statusOptions as $status) {
                        $selected = ($row->Status === $status) ? 'selected' : '';
                        $optionsHtml .= "<option value=\"$status\" $selected>$status</option>";
                    }
                
                    return '<div class="col-sm-10">
                                <select id="change_status" name="change_status" data-id="'.$row->CheckID.'" class="form-control form-select">
                                    ' . $optionsHtml . '
                                </select>
                            </div>';
                })
                ->addColumn('actions', function ($row) {
                    $check_preview = asset('checks/' . $row->CheckPDF);

                    return ' <a href="'.$check_preview.'" target="_blank" class="btn">
                                    <i class="menu-icon tf-icons ti ti-files"></i>
                            </a>';
                })
                ->rawColumns(['logo', 'Status', 'actions'])
                ->make(true);
        }

        return view('user.check.process_payment_check');
    }
    public function process_payment_check()
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        $package = Package::find(Auth::user()->CurrentPackageID);
        $total_used_check = Checks::where('UserID', Auth::id())->count();
        // dd($package->CheckLimitPerMonth, $total_used_check);

        if($package->CheckLimitPerMonth <= $total_used_check) {
            return redirect()->route('check.process_payment')->with('info', 'Your check limit has been exceeded. Please upgrade your plan.');
        }

        $payees = Company::where('UserID', Auth::id())->get();
        $payors = Payors::where('UserID', Auth::id())->get();
        return view('user.check.process_payment_generate_check', compact('payees', 'payors'));
    }
    public function process_payment_check_generate(Request $request)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        $validator = Validator::make($request->all(), [
            'check_date' => 'required|date',
            'check_number' => 'required|numeric|unique:Checks,CheckNumber',
            'amount' => 'required|numeric|min:0.01',
            'payee' => 'required|exists:Company,CompanyID',
            'payor' => 'required|exists:Entities,EntityID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [];
        $payor = Payors::find($request->payor);
        $payee = Company::find($request->payee);
        $data['payor_name'] = $payor->Name;
        $data['address1'] = $payor->Address1;
        $data['address2'] = $payor->Address2;
        $data['city'] = $payor->City;
        $data['state'] = $payor->State;
        $data['zip'] = $payor->Zip;
        $data['check_number'] = $request->check_number;
        $data['check_date'] = $request->check_date;
        $data['payee_name'] = $payee->Name;
        $data['amount'] = $request->amount;
        $data['amount_word'] = $this->numberToWords($request->amount);
        $data['memo'] = $request->memo;
        $data['routing_number'] = $payor->RoutingNumber;
        $data['account_number'] = $payor->AccountNumber;
        $data['memo'] = $request->memo;
        $data['bank_name'] = $payor->BankName; 
    
        $check_file = $this->generateAndSavePDF($data);
        
        $checks = Checks::create([
            'UserID' => Auth::id(),
            'CompanyID'=> $request->payee,
            'CheckType' => 'Process Payment',
            'Amount' => $request->amount,
            'EntityID' => $request->payor,
            'CheckNumber' => $request->check_number,
            'IssueDate' => now(),
            'ExpiryDate' => $request->check_date,
            'Status' => 'Pending',
            'Memo' => $request->memo, 
            'CheckPDF' => $check_file,
        ]);

        $paymentSubscription = PaymentSubscription::where('UserID', Auth::id())->where('PackageID', Auth::user()->CurrentPackageID)->orderBy('PaymentSubscriptionID', 'desc')->first();
        $paymentSubscription->ChecksUsed  = $paymentSubscription->ChecksUsed + 1;
        $paymentSubscription->RemainingChecks  = $paymentSubscription->ChecksGiven - $paymentSubscription->ChecksUsed;
        $paymentSubscription->save();

        return redirect()->route('check.process_payment')->with('success', 'Check Generated successfully');
    }

    public function send_payment(Request $request)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $checks = Checks::where('UserID', Auth::id())->where('CheckType', 'Make Payment')->get();

            return datatables()->of($checks)
                ->addIndexColumn()
                ->addColumn('CompanyID', function ($row) {
                    $company = Company::find($row->CompanyID);
                    return $company->Name;
                })
                ->addColumn('EntityID', function ($row) {
                    $payor = Payors::find($row->EntityID);
                    return $payor->Name;
                })
                ->addColumn('Status', function ($row) {
                    $statusOptions = ['Pending', 'Issued', 'Cleared', 'Expired', 'Cancelled'];
                    $optionsHtml = '';
                
                    // Loop through options and set the selected option if it matches the row status
                    foreach ($statusOptions as $status) {
                        $selected = ($row->Status === $status) ? 'selected' : '';
                        $optionsHtml .= "<option value=\"$status\" $selected>$status</option>";
                    }
                
                    return '<div class="col-sm-10">
                                <select id="change_status" name="change_status" data-id="'.$row->CheckID.'" class="form-control form-select">
                                    ' . $optionsHtml . '
                                </select>
                            </div>';
                })
                ->addColumn('actions', function ($row) {
                    $check_preview = asset('checks/' . $row->CheckPDF);

                    return ' <a href="'.$check_preview.'" target="_blank" class="btn">
                                    <i class="menu-icon tf-icons ti ti-files"></i>
                            </a>';
                })
                ->rawColumns(['logo', 'Status', 'actions'])
                ->make(true);
        }

        return view('user.check.send_payment_check');
    }
    public function send_payment_check()
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        $package = Package::find(Auth::user()->CurrentPackageID);
        $total_used_check = Checks::where('UserID', Auth::id())->count();

        if($package->CheckLimitPerMonth <= $total_used_check) {
            return redirect()->route('check.process_payment')->with('info', 'Your check limit has been exceeded. Please upgrade your plan.');
        }

        $payees = Payors::where('UserID', Auth::id())->get();
        $payors = Company::where('UserID', Auth::id())->get();
        return view('user.check.send_payment_generate_check', compact('payees', 'payors'));
    }
    public function send_payment_check_generate(Request $request)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        $validator = Validator::make($request->all(), [
            'check_date' => 'required|date',
            'check_number' => 'required|numeric|unique:Checks,CheckNumber',
            'amount' => 'required|numeric|min:0.01',
            'payor' => 'required|exists:Company,CompanyID',
            'payee' => 'required|exists:Entities,EntityID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [];
        $payor = Company::find($request->payor);
        $payee = Payors::find($request->payee);
        $data['payor_name'] = $payor->Name;
        $data['address1'] = $payor->Address1;
        $data['address2'] = $payor->Address2;
        $data['city'] = $payor->City;
        $data['state'] = $payor->State;
        $data['zip'] = $payor->Zip;
        $data['check_number'] = $request->check_number;
        $data['check_date'] = $request->check_date;
        $data['payee_name'] = $payee->Name;
        $data['amount'] = $request->amount;
        $data['amount_word'] = $this->numberToWords($request->amount);
        $data['memo'] = $request->memo;
        $data['routing_number'] = $payor->RoutingNumber;
        $data['account_number'] = $payor->AccountNumber;
        $data['memo'] = $request->memo;
        $data['bank_name'] = $payor->BankName;
    
        $check_file = $this->generateAndSavePDF($data);
        
        $checks = Checks::create([
            'UserID' => Auth::id(),
            'CompanyID'=> $request->payor,
            'CheckType' => 'Make Payment',
            'Amount' => $request->amount,
            'EntityID' => $request->payee,
            'CheckNumber' => $request->check_number,
            'IssueDate' => now(),
            'ExpiryDate' => $request->check_date,
            'Status' => 'Pending',
            'Memo' => $request->memo, 
            'CheckPDF' => $check_file,
        ]);

        $paymentSubscription = PaymentSubscription::where('UserID', Auth::id())->where('PackageID', Auth::user()->CurrentPackageID)->orderBy('PaymentSubscriptionID', 'desc')->first();
        $paymentSubscription->ChecksUsed  = $paymentSubscription->ChecksUsed + 1;
        $paymentSubscription->RemainingChecks  = $paymentSubscription->ChecksGiven - $paymentSubscription->ChecksUsed;
        $paymentSubscription->save();

        return redirect()->route('check.send_payment')->with('success', 'Check Generated successfully');
    }

    public function generateAndSavePDF($data)
    {
        $directoryPath = public_path('checks');

        // Check if the directory exists, if not, create it
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }
        // return view('user.check_formate.index', compact('data'));
    
        // Generate PDF from a view
        $pdf = PDF::loadView('user.check_formate.index', compact('data'))->setPaper('a4', 'portrait')
        ->set_option('isHtml5ParserEnabled', true)
        ->set_option('isRemoteEnabled', true);
    
        // Define the file path where you want to save the PDF
        $file_name = 'check-' . $data['check_number'] . '.pdf';
        $filePath = $directoryPath .  '/' . $file_name;
    
        // Save the PDF to the specified path
        $pdf->save($filePath);
        return $file_name;
    }

    function numberToWords($number) {
        $numberToWords = new NumberToWords();
        $transformer = $numberToWords->getNumberTransformer('en');

        $words = $transformer->toWords($number);
        return $words;
    }

    public function check()
    {
        return view('user.check_formate.index');
    }

    public function change_status(Request $request)
    {
        $check = Checks::find($request->id);
        $check->Status = $request->value;
        $check->save();

        if ($request->page == 1) {
            return response()->json([
                'redirectUrl' => route('check.process_payment'),
                'message' => 'Status changed successfully'
            ]);
        } else {
            return response()->json([
                'redirectUrl' => route('check.send_payment'),
                'message' => 'Status changed successfully'
            ]);
        }
    }

    public function history(Request $request)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $checks = Checks::all();
            return datatables()->of($checks)
                ->addIndexColumn()
                ->addColumn('CompanyID', function ($row) {
                    $company = Company::find($row->CompanyID);
                    return $company->Name;
                })
                ->addColumn('EntityID', function ($row) {
                    $payor = Payors::find($row->EntityID);
                    return $payor->Name;
                })
                ->addColumn('Status', function ($row) {
                    $html = '';
                    if($row->Status == 'Pending') {
                        $html =  '<span class="badge bg-label-info me-1">' . $row->Status . '</span>';
                    } else if($row->Status == 'Issued') {
                        $html =  '<span class="badge bg-label-primary me-1">' . $row->Status . '</span>';
                    } else if($row->Status == 'Cleared') {
                        $html =  '<span class="badge bg-label-success me-1">' . $row->Status . '</span>';
                    }else if($row->Status == 'Expired') {
                        $html =  '<span class="badge bg-label-warning me-1">' . $row->Status . '</span>';
                    } else if($row->Status == 'Cancelled') {
                        $html =  '<span class="badge bg-label-warning me-1">' . $row->Status . '</span>';
                    }
                    return $html;
                })
                ->addColumn('actions', function ($row) {
                    $check_preview = asset('checks/' . $row->CheckPDF);

                    return ' <a href="'.$check_preview.'" target="_blank" class="btn">
                                    <i class="menu-icon tf-icons ti ti-files"></i>
                            </a>';
                })
                ->rawColumns(['Status','actions'])
                ->make(true);
        }

        $clients = Payors::where('UserID', Auth::id())->count();
        $checks = Checks::where('UserID', Auth::id())->count();
        $paidAmount = Checks::where('UserID', Auth::id())->where('Status', 'Cleared')->sum('Amount');
        $unPaidAmount = Checks::where('UserID', Auth::id())->where('Status','!=','Cleared')->sum('Amount');

        
        $paidAmount = $this->formatToK($paidAmount);
        $unPaidAmount = $this->formatToK($unPaidAmount);
        return view('user.check.history', compact('clients', 'checks', 'paidAmount', 'unPaidAmount'));
    }

    function formatToK($number)
    {
        if ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }

        return $number;
    }
}

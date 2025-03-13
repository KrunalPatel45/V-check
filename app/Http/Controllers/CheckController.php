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
use Carbon\Carbon;
use App\Models\WebForm;
use Illuminate\Support\Str;

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
                ->addColumn('IssueDate', function ($row) {
                    return Carbon::parse($row->IssueDate)->format('m/d/Y');
                })
                ->addColumn('actions', function ($row) {
                    // $editUrl = route('user.payors.edit', ['type' => 'Payee', 'id' => $row->EntityID]);
                    // $deleteUrl = route('user.payors.delete', ['type' => 'Payee', 'id' => $row->EntityID]);
                    $editUrl = route('check.process_payment_check_edit', ['id' => $row->CheckID]);
                    $check_generate = route('check_generate', ['id' => $row->CheckID]);

                    if($row->Status == 'draft') {
                        return '<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i> Draft
                                    </a>
                                   <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" 
                                    data-bs-target="#check-generate' . $row->CheckID . '">
                                        <i class="ti ti-bookmark-plus me-1"></i> generate
                                    </a>
                                </div>
                            </div>
                            <div class="modal fade" id="check-generate' . $row->CheckID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Check Generate</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to generate check ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="'.$check_generate.'" class="btn btn-primary">Generate</a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                    } else {
                        if(!empty($row->CheckPDF)) {
                            return '<a href="'.asset('checks/' . $row->CheckPDF).'" target="_blank" class="btn">
                                        <i class="menu-icon tf-icons ti ti-files"></i>
                                </a>';
    
                        } else {
                            return '-';
                        }
                    }
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
        $payors = Payors::where('UserID', Auth::id())->where('Type', 'Vendor')->get();
        return view('user.check.process_payment_generate_check', compact('payees', 'payors'));
    }
    public function process_payment_check_generate(Request $request)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        $validator = Validator::make($request->all(), [
            'check_date' => 'required',
            'check_number' => 'required|numeric',
            'amount' => 'required|numeric|min:0.01',
            'payee' => 'required|exists:Company,CompanyID',
            'payor' => 'required|exists:Entities,EntityID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if(!empty($request->signed)) {
            $folderPath = public_path('sign/');

            $image_parts = explode(";base64,", $request->signed);
            $image_type_aux = explode("image/", $image_parts[0]);

            $fileName = '';
            if(!empty($image_type_aux[1])) {
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = uniqid() . '.'.$image_type;
                $file = $folderPath . $fileName;

                file_put_contents($file, $image_base64);
            }
        }

        $check_date = Carbon::parse(str_replace('-', '/', $request->check_date));
        
        if (!empty($request->id)) {
            // Update existing record
            $checks = Checks::find($request->id);
            if(empty($fileName)) {
                $fileName = $checks->DigitalSignature;
            }
            if ($checks) {
                $checks->update([
                    'CompanyID' => $request->payee,
                    'CheckType' => 'Process Payment',
                    'Amount' => $request->amount,
                    'EntityID' => $request->payor,
                    'CheckNumber' => $request->check_number,
                    'IssueDate' => now(),
                    'ExpiryDate' => $check_date,
                    'Status' => 'draft',
                    'Memo' => $request->memo, 
                    'CheckPDF' => null,
                    'DigitalSignatureRequired' => (!empty($request->is_sign) && $request->is_sign == 'on') ? 1 : 0,
                    'DigitalSignature' => (!empty($request->is_sign) && $request->is_sign == 'on') ? $fileName : '',
                ]);
            }
            $message = 'Check Updated successfully';
        } else {
            // Create new record
            $checks = Checks::create([
                'UserID' => Auth::id(),
                'CompanyID'=> $request->payee,
                'CheckType' => 'Process Payment',
                'Amount' => $request->amount,
                'EntityID' => $request->payor,
                'CheckNumber' => $request->check_number,
                'IssueDate' => now(),
                'ExpiryDate' => $check_date,
                'Status' => 'draft',
                'Memo' => $request->memo, 
                'CheckPDF' => null,
                'DigitalSignatureRequired' => (!empty($request->is_sign) && $request->is_sign == 'on') ? 1 : 0,
                'DigitalSignature' => (!empty($request->is_sign) && $request->is_sign == 'on') ? $fileName : '',
            ]);

            $paymentSubscription = PaymentSubscription::where('UserID', Auth::id())->where('PackageID', Auth::user()->CurrentPackageID)->orderBy('PaymentSubscriptionID', 'desc')->first();
            $paymentSubscription->ChecksUsed  = $paymentSubscription->ChecksUsed + 1;
            $paymentSubscription->RemainingChecks  = $paymentSubscription->ChecksGiven - $paymentSubscription->ChecksUsed;
            $paymentSubscription->save();
            $message = 'Check Crated successfully';
        }
        
        return redirect()->route('check.process_payment')->with('success', $message);
    }

    public function process_payment_check_edit(Request $request, $id)
    {
        $check = Checks::find($id);
        $check->ExpiryDate = Carbon::parse($check->ExpiryDate)->format('m-d-Y');
        $payees = Company::where('UserID', Auth::id())->get();
        $payors = Payors::where('UserID', Auth::id())->where('Type', 'Vendor')->get();
        $old_payee = Company::find($check->CompanyID);
        $old_payor = Payors::find($check->EntityID);
        return view('user.check.process_payment_generate_check', compact('payees', 'payors','check', 'old_payee', 'old_payor'));
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
                ->addColumn('IssueDate', function ($row) {
                    return Carbon::parse($row->IssueDate)->format('m/d/Y');
                })
                ->addColumn('actions', function ($row) {
                    
                    $editUrl = route('check.process_send_check_edit', ['id' => $row->CheckID]);
                    $check_generate = route('check_generate', ['id' => $row->CheckID]);

                    if($row->Status == 'draft') {
                        return '<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i> Draft
                                    </a>
                                   <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" 
                                    data-bs-target="#check-generate' . $row->CheckID . '">
                                        <i class="ti ti-bookmark-plus me-1"></i> generate
                                    </a>
                                </div>
                            </div>
                            <div class="modal fade" id="check-generate' . $row->CheckID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Check Generate</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to generate check ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="'.$check_generate.'" class="btn btn-primary">Generate</a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                    } else {
                        if(!empty($row->CheckPDF)) {
                            return '<a href="'.asset('checks/' . $row->CheckPDF).'" target="_blank" class="btn">
                                        <i class="menu-icon tf-icons ti ti-files"></i>
                                </a>';
    
                        } else {
                            return '-';
                        }
                    }
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

        $payees = Payors::where('UserID', Auth::id())->where('Type', 'Client')->get();
        $payors = Company::where('UserID', Auth::id())->get();
        return view('user.check.send_payment_generate_check', compact('payees', 'payors'));
    }
    public function send_payment_check_generate(Request $request)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        $validator = Validator::make($request->all(), [
            'check_date' => 'required',
            'check_number' => 'required|numeric',
            'amount' => 'required|numeric|min:0.01',
            'payor' => 'required|exists:Company,CompanyID',
            'payee' => 'required|exists:Entities,EntityID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if(!empty($request->signed)) {
            $folderPath = public_path('sign/');

            $image_parts = explode(";base64,", $request->signed);
            $image_type_aux = explode("image/", $image_parts[0]);

            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $fileName = uniqid() . '.'.$image_type;
            $file = $folderPath . $fileName;
            file_put_contents($file, $image_base64);
        }
        
        $check_date = Carbon::parse(str_replace('-', '/', $request->check_date));

        if (!empty($request->id)) {
            $checks = Checks::find($request->id);
            if(empty($fileName)) {
                $fileName = $checks->DigitalSignature;
            }
            if ($checks) {
                $checks->update([
                    'CompanyID'=> $request->payor,
                    'CheckType' => 'Make Payment',
                    'Amount' => $request->amount,
                    'EntityID' => $request->payee,
                    'CheckNumber' => $request->check_number,
                    'IssueDate' => now(),
                    'ExpiryDate' => $check_date,
                    'Status' => 'draft',
                    'Memo' => $request->memo, 
                    'CheckPDF' => null,
                    'DigitalSignatureRequired' => (!empty($request->is_sign) && $request->is_sign == 'on') ? 1 : 0,
                    'DigitalSignature' => (!empty($request->is_sign) && $request->is_sign == 'on') ? $fileName : '',
                ]);
            }
            $message = 'Check Updated successfully';
        } else {
            $checks = Checks::create([
                'UserID' => Auth::id(),
                'CompanyID'=> $request->payor,
                'CheckType' => 'Make Payment',
                'Amount' => $request->amount,
                'EntityID' => $request->payee,
                'CheckNumber' => $request->check_number,
                'IssueDate' => now(),
                'ExpiryDate' => $check_date,
                'Status' => 'draft',
                'Memo' => $request->memo, 
                'CheckPDF' => null,
                'DigitalSignatureRequired' => (!empty($request->is_sign) && $request->is_sign == 'on') ? 1 : 0,
                'DigitalSignature' => (!empty($request->is_sign) && $request->is_sign == 'on') ? $fileName : '',
            ]);
    
            $paymentSubscription = PaymentSubscription::where('UserID', Auth::id())->where('PackageID', Auth::user()->CurrentPackageID)->orderBy('PaymentSubscriptionID', 'desc')->first();
            $paymentSubscription->ChecksUsed  = $paymentSubscription->ChecksUsed + 1;
            $paymentSubscription->RemainingChecks  = $paymentSubscription->ChecksGiven - $paymentSubscription->ChecksUsed;
            $paymentSubscription->save();
            $message = 'Check Crated successfully';
        }
        
        return redirect()->route('check.send_payment')->with('success', 'Check Generated successfully');
    }

    public function process_send_check_edit(Request $request, $id)
    {
        $check = Checks::find($id);
        $check->ExpiryDate = Carbon::parse($check->ExpiryDate)->format('m-d-Y');
        $payors = Company::where('UserID', Auth::id())->get();
        $payees = Payors::where('UserID', Auth::id())->where('Type', 'Client')->get();
        $old_payor = Company::find($check->CompanyID);
        $old_payee = Payors::find($check->EntityID);
        return view('user.check.send_payment_generate_check', compact('payees', 'payors','check', 'old_payee', 'old_payor'));
    }
    
    public function generateAndSavePDF($data)
    {
        $directoryPath = public_path('checks');
        // Create fonts directory if it doesn't exist
        $fontPath = storage_path('fonts');
        if (!File::exists($fontPath)) {
            File::makeDirectory($fontPath, 0755, true);
        }

        // Define font file paths
        $fontFiles = [
            'MICRCheckPrixa.ttf',
            'MICRCheckPrixa.woff',
            'MICRCheckPrixa.woff2',
            'MICRCheckPrixa.eot'
        ];

        // Copy font files from public to storage if they don't exist
        foreach ($fontFiles as $fontFile) {
            $sourcePath = public_path('storage/fonts/' . $fontFile);
            $destPath = $fontPath . '/' . $fontFile;
            
            if (!File::exists($destPath) && File::exists($sourcePath)) {
                File::copy($sourcePath, $destPath);
            }
        }

        // Configure DOMPDF font cache directory
        $configPath = config_path('dompdf.php');
        if (File::exists($configPath)) {
            config(['dompdf.options.font_cache' => $fontPath]);
        }

        // Check if the directory exists, if not, create it
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }
        // Generate PDF from a view
        $pdf = PDF::loadView('user.check_formate.index', compact('data'))->setPaper('a4', 'portrait')
        ->setPaper([0, 0, 800, 800])
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
            $checks = Checks::where('UserID', Auth::id())->get();
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
                ->addColumn('IssueDate', function ($row) {
                    return Carbon::parse($row->IssueDate)->format('m/d/Y');
                })
                ->addColumn('Status', function ($row) {
                    if($row->Status == 'draft') {
                        return 'Draft';
                    } else {
                        return 'Generated';
                    }
                })
                ->addColumn('actions', function ($row) {
                    $check_preview = asset('checks/' . $row->CheckPDF);

                    if(!empty($row->CheckPDF)) {
                        return ' <a href="'.$check_preview.'" target="_blank" class="btn">
                        <i class="menu-icon tf-icons ti ti-files"></i>
                        </a>';
                    } else {
                        return '-';
                    }
                })
                ->rawColumns(['Status','actions'])
                ->make(true);
        }

        $clients = Payors::where('UserID', Auth::id())->count();
        $checks = Checks::where('UserID', Auth::id())->count();
        $paidAmount = Checks::where('UserID', Auth::id())->where('Status', 'generated')->sum('Amount');
        $unPaidAmount = Checks::where('UserID', Auth::id())->where('Status','draft')->sum('Amount');

        
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
    
    public function amount_word(Request $request)
    {
        $word = '';
        if(!empty($request->amount)) {
            $word = $this->numberToWords($request->amount);
        }
        return response()->json(['success' => true,'word' => $word]);
    }

    public function get_payee($id) 
    {
        $payee = Company::find($id);
        return response()->json(['success' => true,'payee' => $payee]);
    }

    public function get_payor($id) 
    {
        $payor = Payors::find($id);
        return response()->json(['success' => true,'payor' => $payor]);
    }

    public function check_generate($id)
    {
        $check = Checks::find($id);

        $check_date = Carbon::parse(str_replace('/', '-', $check->ExpiryDate))->format('m/d/Y');

        $data = [];
        $payor = Payors::find($check->EntityID);
        $payee = Company::find($check->CompanyID);
        $data['payor_name'] = $payor->Name;
        $data['address1'] = $payor->Address1;
        $data['address2'] = $payor->Address2;
        $data['city'] = $payor->City;
        $data['state'] = $payor->State;
        $data['zip'] = $payor->Zip;
        $data['check_number'] = $check->CheckNumber;
        $data['check_date'] = $check_date;
        $data['payee_name'] = $payee->Name;
        $data['amount'] = $check->Amount;
        $data['amount_word'] = $this->numberToWords($check->Amount);
        $data['memo'] = $check->Memo;
        $data['routing_number'] = $payor->RoutingNumber;
        $data['account_number'] = $payor->AccountNumber;
        $data['bank_name'] = $payor->BankName; 
        $data['signature'] = (!empty($check->DigitalSignatureRequired)) ? $check->DigitalSignature : '';

        // return view('user.check_formate.index', compact('data'));
        
        $check_file = $this->generateAndSavePDF($data);

        $check->Status = 'generated';
        $check->CheckPDF = $check_file;

        $check->save();

        return redirect()->back()->with('success', 'Check generated successfully.');
    }

    public function web_form($id)
    {
        $data = WebForm::whereRaw("MD5(Id) = ?", [$id])->first();
        $company = Company::find($data->CompanyID);
        return view('user.web_form.web', compact('data', 'company'));
    }

    public function get_web_forms(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $webforms = WebForm::where('UserID', Auth::id())->get();

            return datatables()->of($webforms)
                ->addIndexColumn()
                ->addColumn('logo', function ($row) {
                    $company = Company::find($row->CompanyID);
                    if(!empty($company->Logo)) {
                        return '<img src="' . asset($company->Logo) . '" alt="Webform Logo" style="width: 50px;">';
                    } else {
                        return '<img src="' . asset('assets/img/empty.jpg') . '" alt="Webform Logo" style="width: 50px;">';
                    }
                })
                ->addColumn('CompanyID', function ($row) {
                    $company = Company::find($row->CompanyID);
                    return !empty($company->Name) ? $company->Name : '-'; 
                })
                ->addColumn('actions', function ($row) {
                    $Preview = route('web_form', ['id' => md5($row->Id)]);
                    return '<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="'.$Preview.'" class="dropdown-item" target="_blank">
                                        <i class="ti ti-files me-1"></i> Preview
                                    </a>
                                    <a href="'.$Preview.'" data-link="'.$Preview.'" class="dropdown-item copy-link">
                                        <i class="ti ti-clipboard-copy me-1"></i> Copy Link
                                    </a>
                                </div>
                            </div>';
                })
                ->rawColumns(['logo','actions'])
                ->make(true);
        }
        return view('user.web_form.index');
    }

    public function new_web_form()
    {
        $companies = Company::where('UserID', Auth::id())->get();
        return view('user.web_form.new', compact('companies'));
    }

    public function new_web_form_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required',
            'phone_number' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $webform = new WebForm();

        $company = Company::find($request->company);

        $webform->UserID = Auth::id();
        $webform->PhoneNumber = $request->phone_number;
        $webform->CompanyID = $request->company;

        $webform->save();

        return redirect()->route('get_web_forms')->with('success', 'Web form generated successfully');
    }
    
    public function store_web_form_data(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'check_number' => 'required',
            'check_date' => 'required',
            'amount' => 'required',
            'name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'bank_name' => 'required',
            'routing_number' => 'required',
            'account_number_verify' => 'required',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Please fill all required details.');
        }

        $company = Company::find($request->comany_id);
        // dd($company->UserID);

        $payor_data = [
            'Name' => $request->name,
            'Address1' => $request->address,
            'City' => $request->city,
            'State' => $request->state,
            'Zip' => $request->zip,
            'UserID' => $company->UserID,
            'BankName' => $request->bank_name,
            'RoutingNumber' => $request->routing_number,
            'AccountNumber' => $request->account_number_verify,
            'Type' => 'Vendor',
        ];

        $payor = Payors::create($payor_data);

        $check_date = Carbon::parse(str_replace('-', '/', $request->check_date));
        
        $check_data = [
            'UserID' => $company->UserID,
            'CompanyID'=> $request->comany_id,
            'CheckType' => 'Process Payment',
            'Amount' => $request->amount,
            'EntityID' => $payor->EntityID,
            'CheckNumber' => $request->check_number,
            'IssueDate' => now(),
            'ExpiryDate' => $check_date,
            'Status' => 'draft',
            'Memo' => $request->memo, 
            'CheckPDF' => null,
            'DigitalSignatureRequired' => 0,
        ];

        $check = Checks::create($check_data);
        return redirect()->route('thankyou');
    }

    public function thankyou()
    {
        return view('user.web_form.thank_you');
    }
}

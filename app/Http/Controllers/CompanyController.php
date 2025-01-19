<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $companies = Company::where('UserID', Auth::id())->get();

            return datatables()->of($companies)
                ->addIndexColumn()
                ->addColumn('logo', function ($row) {
                    return '<img src="' . asset('storage/' . $row->Logo) . '" alt="Company Logo" style="width: 50px;">';
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge ' .
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">' . $row->Status . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('user.company.edit', ['id' => $row->CompanyID]);
                    $deleteUrl = route('user.company.delete', ['id' => $row->CompanyID]);

                    return '<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                    data-bs-target="#delete' . $row->CompanyID . '">
                                        <i class="ti ti-trash me-1"></i> Delete
                                    </a>
                                </div>
                            </div>
                            <div class="modal fade" id="delete' . $row->CompanyID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Company</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this company?</p>
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
                            </div>';
                })
                ->rawColumns(['logo', 'status', 'actions'])
                ->make(true);
        }

        return view('user.company.index');
    }

    public function create() 
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        return view('user.company.new');   
    }

    public function store(Request $request) 
    {  
        $validator = Validator::make($request->all(), [
            'name' => 'required' ,
            'address1' => 'required',
            'address2' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'email' => 'required|email|unique:Company,email',
            'logo' => 'required|image|mimes:jpeg,png,jpg',
            'bank_name' => 'required',
            'routing_number' => 'required',
            'account_number' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $uniqueName = Str::uuid() . '.' . $file->getClientOriginalExtension(); // Generate a unique name with extension
            $logoPath = $file->storeAs('logos', $uniqueName, 'public'); // Store in "storage/app/public/logos"
        }

        $slug = Str::slug($request->name, '-');
        
        $company = new Company();

        $company->Name = $request->name;
        $company->UserID = Auth::id();
        $company->Address1 = $request->address1;
        $company->Address2 = $request->address2;
        $company->City = $request->city;
        $company->State = $request->state;
        $company->Zip = $request->zip;
        $company->Email = $request->email;
        $company->Logo = $logoPath; // Save the path of the logo
        $company->BankName = $request->bank_name;
        $company->RoutingNumber = $request->routing_number;
        $company->AccountNumber = $request->account_number;
        $company->PageURL = $request->page_url;
        $company->PageDescription = $request->page_description;
        $company->Slug = $slug;
        $company->Status = $request->status;

        $company->save();


        return redirect()->route('user.company')->with('success', 'Company added successfully');
    }

    public function edit($id)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        $company = Company::find($id);
        return view('user.company.edit', compact('company'));
    }

    public function update(Request $request, $id) 
    {  
        $validator = Validator::make($request->all(), [
            'name' => 'required' ,
            'address1' => 'required',
            'address2' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'email' => 'required|email|unique:Company,email,' . $id.',CompanyID',
            'bank_name' => 'required',
            'routing_number' => 'required',
            'account_number' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $slug = Str::slug($request->name, '-');
        
        $company = Company::find($id);

        
        $logoPath =  $company->Logo;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $uniqueName = Str::uuid() . '.' . $file->getClientOriginalExtension(); // Generate a unique name with extension
            $logoPath = $file->storeAs('logos', $uniqueName, 'public'); // Store in "storage/app/public/logos"
        }

        $company->Name = $request->name;
        $company->UserID = Auth::id();
        $company->Address1 = $request->address1;
        $company->Address2 = $request->address2;
        $company->City = $request->city;
        $company->State = $request->state;
        $company->Zip = $request->zip;
        $company->Email = $request->email;
        $company->Logo = $logoPath; // Save the path of the logo
        $company->BankName = $request->bank_name;
        $company->RoutingNumber = $request->routing_number;
        $company->AccountNumber = $request->account_number;
        $company->PageURL = $request->page_url;
        $company->PageDescription = $request->page_description;
        $company->Slug = $slug;
        $company->Status = $request->status;

        $company->save();


        return redirect()->route('user.company')->with('success', 'Updated added successfully');
    }

    public function delete($id)
    {
        $package = Company::find($id);
        $package->delete();

        return redirect()->route('user.company')->with('success', 'Company deleted successfully');
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index()
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        $companies = Company::where('UserID', Auth::id())->get();
        return view('user.company.index', compact('companies'));
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
            'email' => 'required|email',
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
            'email' => 'required|email',
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
        
        $company = Company::find($id);

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

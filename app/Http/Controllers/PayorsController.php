<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Payors;

class PayorsController extends Controller
{
    public function index()
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        $payors = Payors::where('UserID', Auth::id())->get();
        return view('user.payors.index', compact('payors'));
    }

    public function create() 
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        return view('user.payors.new');   
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
            'bank_name' => 'required',
            'routing_number' => 'required',
            'account_number' => 'required',
            'status' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        
        $payor = new Payors();

        $payor->Name = $request->name;
        $payor->UserID = Auth::id();
        $payor->Address1 = $request->address1;
        $payor->Address2 = $request->address2;
        $payor->City = $request->city;
        $payor->State = $request->state;
        $payor->Zip = $request->zip;
        $payor->Email = $request->email;
        $payor->BankName = $request->bank_name;
        $payor->RoutingNumber = $request->routing_number;
        $payor->AccountNumber = $request->account_number;
        $payor->Status = $request->status;
        $payor->Type = $request->type;

        $payor->save();


        return redirect()->route('user.payors')->with('success', 'payors added successfully');
    }

    public function edit($id)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        $payor = Payors::find($id);
        return view('user.payors.edit', compact('payor'));
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

        
        $payor = Payors::find($id);

        $payor->Name = $request->name;
        $payor->UserID = Auth::id();
        $payor->Address1 = $request->address1;
        $payor->Address2 = $request->address2;
        $payor->City = $request->city;
        $payor->State = $request->state;
        $payor->Zip = $request->zip;
        $payor->Email = $request->email;
        $payor->BankName = $request->bank_name;
        $payor->RoutingNumber = $request->routing_number;
        $payor->AccountNumber = $request->account_number;
        $payor->Type = $request->type;
        $payor->Status = $request->status;

        $payor->save();


        return redirect()->route('user.payors')->with('success', 'Updated added successfully');
    }

    public function delete($id)
    {
        $package = Payors::find($id);
        $package->delete();

        return redirect()->route('user.payors')->with('success', 'payors deleted successfully');
    }

}

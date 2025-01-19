<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Payors;

class PayorsController extends Controller
{
    public function client_index()
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        $payors = Payors::where('UserID', Auth::id())->whereIn('Type', ['Client', 'Both'])->get();
        return view('user.client.index', compact('payors'));
    }
    public function vendor_index()
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        $payors = Payors::where('UserID', Auth::id())->whereIn('Type', ['Vendor', 'Both'])->get();
        return view('user.vendor.index', compact('payors'));
    }

    public function create($type) 
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        return view('user.'.$type.'.new');   
    }

    public function store(Request $request, $type) 
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

        $payors_type = $request->type;
        if(!empty($request->same_as) && $request->same_as == 'on') {
            $payors_type = 'Both';
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
        $payor->Type = $payors_type;

        $payor->save();


        return redirect()->route('user.'. $type)->with('success', $type.' added successfully');
    }

    public function edit($type, $id)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }
        $payor = Payors::find($id);
        return view('user.'.$type.'.edit', compact('payor'));
    }

    public function update(Request $request, $type, $id) 
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

        $payors_type = $request->type;
        if(!empty($request->same_as) && $request->same_as == 'on') {
            $payors_type = 'Both';
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
        $payor->Type = $payors_type;
        $payor->Status = $request->status;

        $payor->save();


        return redirect()->route('user.'.$type)->with('success', $type . ' updated successfully');
    }

    public function delete($type, $id)
    {
        $package = Payors::find($id);
        $package->delete();

        return redirect()->route('user.'.$type)->with('success', $type . ' deleted successfully');
    }

}

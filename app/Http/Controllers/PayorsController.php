<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Payors;

class PayorsController extends Controller
{
    public function client_index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $payors = Payors::where('UserID', Auth::id())
                ->whereIn('Type', ['Client', 'Both'])
                ->get();

            return datatables()->of($payors)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge ' . 
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') . 
                        '">' . $row->Status . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('user.payors.edit', ['type' => 'client', 'id' => $row->EntityID]);
                    $deleteUrl = route('user.payors.delete', ['type' => 'client', 'id' => $row->EntityID]);
                
                    return '<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                    data-bs-target="#delete' . $row->EntityID . '">
                                        <i class="ti ti-trash me-1"></i> Delete
                                    </a>
                                </div>
                            </div>
                            <div class="modal fade" id="delete' . $row->EntityID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Payor</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this Payor?</p>
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
                
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('user.client.index');
    }

    public function vendor_index(Request $request)
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $payors = Payors::where('UserID', Auth::id())
                ->whereIn('Type', ['Vendor', 'Both'])
                ->get();

            return datatables()->of($payors)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return '<span class="badge ' . 
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') . 
                        '">' . $row->Status . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('user.payors.edit', ['type' => 'client', 'id' => $row->EntityID]);
                    $deleteUrl = route('user.payors.delete', ['type' => 'client', 'id' => $row->EntityID]);
                
                    return '<div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                    data-bs-target="#delete' . $row->EntityID . '">
                                        <i class="ti ti-trash me-1"></i> Delete
                                    </a>
                                </div>
                            </div>
                            <div class="modal fade" id="delete' . $row->EntityID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Payor</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this Payor?</p>
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
                
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('user.vendor.index');
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
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'email' => 'required|email|unique:Entities,email',
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
            'email' => 'required|email|unique:Entities,email,' . $id.',EntityID',
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

    public function add_payor(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'name' => 'required' ,
            'address1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'email' => !empty($request->id) ? 'required|email|unique:Entities,email,' . $request->id.',EntityID'  :'required|email|unique:Entities,email',
            'bank_name' => 'required',
            'routing_number' => 'required',
            'account_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // Create a new Payee entry (optional)
        if(!empty($request->id)) {
            $payor = Payors::find($request->id);
        } else {
            $payor = new Payors();
        }
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
        $payor->Status = 'Active';
        $payor->Type = $request->type;

        $payor->save();

        // Return success message
        return response()->json(['success' => true,'payor' => $payor]);
    }

}

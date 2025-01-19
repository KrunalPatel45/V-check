<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Package;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        if ($request->ajax()) {
            $packages = Package::all(); // Adjust as needed if you want to limit the data

            return datatables()->of($packages)
                ->addIndexColumn() // This adds an index column for row numbers
                ->addColumn('status', function ($package) {
                    return $package->Status == 'Active' 
                        ? '<span class="badge bg-label-primary">' . $package->Status . '</span>' 
                        : '<span class="badge bg-label-warning">' . $package->Status . '</span>';
                })
                ->addColumn('created_at', function ($package) {
                    return $package->CreatedAt;
                })
                ->addColumn('updated_at', function ($package) {
                    return $package->UpdatedAt;
                })
                ->addColumn('actions', function ($row) {
                    // Dynamically build URLs for the edit and delete actions
                    $editUrl = route('admin.package.edit', ['id' => $row->PackageID]);
                    $deleteUrl = route('admin.package.delete', ['id' => $row->PackageID]);

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
                                data-bs-target="#delete' . $row->PackageID . '">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </a>
                            </div>
                        </div>

                        <!-- Modal for Deleting -->
                        <div class="modal fade" id="delete' . $row->PackageID . '" tabindex="-1" aria-hidden="true">
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
                ->rawColumns(['status', 'created_at', 'updated_at', 'actions']) // Specify which columns have HTML content
                ->make(true); // Send response to DataTables
        }

        return view('admin.package.index');
    }



    public function create()
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        return view('admin.package.new');
    }

    public function store(Request $request)
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
            'check_limit' => 'required|numeric',
            'frequency' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $package = new Package();
        $package->Name = $request->name;
        $package->Description = $request->description;
        $package->Price = $request->price;
        $package->Duration = $request->duration;
        $package->CheckLimitPerMonth = $request->check_limit;
        $package->RecurringPaymentFrequency = $request->frequency;
        $package->Status = $request->status;

        $package->save();

        return redirect()->route('admin.package')->with('success', 'Package added successfully');

        return view('admin.package.new');
    }

    public function edit($id)
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        $package = Package::find($id);
        return view('admin.package.edit', compact('package'));
    }

    public function update(Request $request, $id)
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
            'check_limit' => 'required|numeric',
            'frequency' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $package = Package::find($id);
        $package->Name = $request->name;
        $package->Description = $request->description;
        $package->Price = $request->price;
        $package->Duration = $request->duration;
        $package->CheckLimitPerMonth = $request->check_limit;
        $package->RecurringPaymentFrequency = $request->frequency;
        $package->Status = $request->status;

        $package->save();

        return redirect()->route('admin.package')->with('success', 'Package updated successfully');

        return view('admin.package.new');
    }

    public function delete($id)
    {
        $package = Package::find($id);
        $package->delete();

        return redirect()->route('admin.package')->with('success', 'Package deleted successfully');
    }
}

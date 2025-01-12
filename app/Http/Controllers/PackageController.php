<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Package;

class PackageController extends Controller
{
    public function index()
    {
        if(!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        $packages = Package::all();
        return view('admin.package.index', compact('packages'));
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

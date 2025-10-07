<?php

namespace App\Http\Controllers;

use App\Models\HowItWork;
use Illuminate\Http\Request;

class HowItWorksController extends Controller
{
    public function index()
    {
        return view('admin.how-it-works.index');
    }

    public function list()
    {
        $howItWorks = HowItWork::orderBy('id', 'desc')->get();

        return datatables()->of($howItWorks)
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                    return $row->status == 'Active'
                        ? '<span class="badge bg-label-primary">' . $row->status . '</span>'
                        : '<span class="badge bg-label-warning">' . $row->status . '</span>';
                })
            ->addColumn('actions', function ($row) {
                return '<a href="' . route('admin.how_it_works.edit', $row->id) . '" class="dropdown-item justify-content-center">
                                    <i class="ti ti-edit me-1"></i> Edit
                                </a>';
                // return '<a href="' . route('admin.suggestions.view', $row->id) . '" class="btn btn-primary">View</a>';
            })
            ->rawColumns(['status','actions'])
            ->make(true);
    }

    public function edit(HowItWork $howItWork)
    {
        return view('admin.how-it-works.edit', compact('howItWork'));
    }

    public function update(Request $request, HowItWork $howItWork)
    {
        $validated = $request->validate([
            'link' => 'required|url',
            'status' => 'required',
        ], [
            'link.required' => 'Link is required',
            'link.url' => 'Link must be a valid URL',
            'status.required' => 'Status is required',
        ]);

        $howItWork->update($validated);

        return redirect()->route('admin.how_it_works')->with('success', 'Link updated successfully');
    }
}

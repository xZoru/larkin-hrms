<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->getCurrentCompanyId();
        $branches = Branch::where('company_id', $companyId)->withCount('assignments')->orderBy('name')->get();
        return view('branches.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'code' => 'nullable|string|max:50',
            'type' => 'required|in:Branch,Outstation,Project Site', 'address' => 'nullable|string|max:255',
        ]);
        $data['company_id'] = auth()->user()->getCurrentCompanyId();
        $data['is_active'] = $request->boolean('is_active', true);
        Branch::create($data);
        return redirect()->route('branches.index')->with('success', 'Location created successfully.');
    }

    public function destroy(Branch $branch)
    {
        abort_unless($branch->company_id === auth()->user()->getCurrentCompanyId(), 403);
        if ($branch->assignments()->exists()) {
            return back()->with('error', 'This location has assignment history and cannot be deleted. Mark it inactive instead.');
        }
        $branch->delete();
        return back()->with('success', 'Location deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            $departments = Department::with('company')->orderBy('name')->get();
        } else {
            $companyId = $this->getCompanyId();
            $departments = Department::where('company_id', $companyId)
                ->with('company')
                ->orderBy('name')
                ->get();
        }
        
        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        
        $companyId = $this->getCompanyId();
        
        if ($user->isSuperAdmin() && $request->has('company_id')) {
            $companyId = $request->company_id;
        }
        
        // ✅ Generate code if not provided
        $code = $request->code;
        if (empty($code)) {
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $request->name), 0, 3)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
        
        Department::create([
            'name' => $request->name,
            'code' => $code,
            'description' => $request->description,
            'company_id' => $companyId,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function destroy(Department $department)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($department->company_id !== $companyId) {
                abort(403, 'You are not authorized to delete this department.');
            }
        }
        
        $department->delete();
        
        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    private function getCompanyId()
    {
        return auth()->user()->getCurrentCompanyId();
    }
}
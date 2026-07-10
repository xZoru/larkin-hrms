<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();
        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,NULL,id,company_id,' . Auth::user()->company_id,
        ]);

        // Generate a code from the name (e.g., "Human Resources" -> "HR")
        $code = strtoupper(substr($request->name, 0, 3));
        // If code already exists, add a number
        $existing = Department::where('company_id', Auth::user()->company_id)
            ->where('code', $code)
            ->exists();
        
        if ($existing) {
            $count = Department::where('company_id', Auth::user()->company_id)
                ->where('code', 'LIKE', $code . '%')
                ->count() + 1;
            $code = $code . $count;
        }

        Department::create([
            'company_id' => Auth::user()->company_id,
            'name' => $request->name,
            'code' => $code,
            'is_active' => true,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Department added successfully!');
    }

    public function destroy(Department $department)
    {
        // Prevent deletion if employees are assigned
        if ($department->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete department with assigned employees.');
        }
        
        $department->delete();
        return back()->with('success', 'Department deleted successfully.');
    }
}
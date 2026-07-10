<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::where('company_id', Auth::user()->company_id)
            ->with('department')
            ->orderBy('name')
            ->get();
        
        $departments = Department::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->get();
            
        return view('positions.index', compact('positions', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string|max:500',
        ]);

        // Check if position exists for this company
        $exists = Position::where('company_id', Auth::user()->company_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Position already exists!');
        }

        Position::create([
            'company_id' => Auth::user()->company_id,
            'department_id' => $request->department_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('positions.index')
            ->with('success', 'Position added successfully!');
    }

    public function destroy(Position $position)
    {
        // Check if any employees have this position_id
        $employeeCount = Employee::where('position_id', $position->id)->count();
        
        if ($employeeCount > 0) {
            return back()->with('error', 'Cannot delete position with assigned employees.');
        }
        
        $position->delete();
        return back()->with('success', 'Position deleted successfully.');
    }
}
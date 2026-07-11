<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $this->getCompanyId();
        
        if ($user->isSuperAdmin()) {
            $positions = Position::with(['company', 'department'])->orderBy('name')->get();
        } else {
            $positions = Position::where('company_id', $companyId)
                ->with(['company', 'department'])
                ->orderBy('name')
                ->get();
        }
        
        $departments = Department::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('positions.index', compact('positions', 'departments'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
        ]);
        
        $companyId = $this->getCompanyId();
        
        // Generate code if not provided
        $code = $request->code;
        if (empty($code)) {
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $request->name), 0, 3)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
        
        Position::create([
            'name' => $request->name,
            'code' => $code,
            'company_id' => $companyId,
            'department_id' => $request->department_id,
            'is_active' => true,
        ]);
        
        return redirect()->route('positions.index')
            ->with('success', 'Position created successfully.');
    }

    public function destroy(Position $position)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($position->company_id !== $companyId) {
                abort(403, 'You are not authorized to delete this position.');
            }
        }
        
        $position->delete();
        
        return redirect()->route('positions.index')
            ->with('success', 'Position deleted successfully.');
    }

    public function toggle(Position $position)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($position->company_id !== $companyId) {
                abort(403, 'You are not authorized to modify this position.');
            }
        }
        
        $position->is_active = !$position->is_active;
        $position->save();
        
        $status = $position->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('positions.index')
            ->with('success', "Position {$status} successfully.");
    }

    private function getCompanyId()
    {
        return auth()->user()->getCurrentCompanyId();
    }
}
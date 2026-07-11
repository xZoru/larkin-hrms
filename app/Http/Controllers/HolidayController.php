<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HolidayController extends Controller
{
    /**
     * Display a listing of holidays - VISIBLE TO ALL USERS
     */
    public function index()
    {
        $user = auth()->user();
        $year = request('year', date('Y'));
        
        // ✅ Super Admin sees ALL holidays
        if ($user->isSuperAdmin()) {
            $holidays = Holiday::with('company')
                ->whereYear('date', $year)
                ->orderBy('date')
                ->get();
        } else {
            // ✅ Regular users see ALL holidays (not filtered by company)
            $holidays = Holiday::with('company')
                ->whereYear('date', $year)
                ->orderBy('date')
                ->get();
        }
        
        $years = Holiday::selectRaw('DISTINCT YEAR(date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        if (empty($years)) {
            $years = [date('Y')];
        }
        
        return view('management.holidays.index', compact('holidays', 'years', 'year'));
    }

    /**
     * Show the form for creating a new holiday.
     */
    public function create()
    {
        $user = auth()->user();
        $companyId = $this->getCompanyId();
        
        // Super Admin can assign to any company
        if ($user->isSuperAdmin()) {
            $companies = Company::where('is_active', true)->get();
        } else {
            // Regular users can only assign to their company
            $companies = Company::where('id', $companyId)->get();
        }
        
        return view('management.holidays.create', compact('companies'));
    }

    /**
     * Store a newly created holiday.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);
        
        $companyId = $this->getCompanyId();
        
        // Super Admin can select a specific company
        if ($user->isSuperAdmin() && $request->has('company_id') && $request->company_id) {
            $companyId = $request->company_id;
        }
        
        Holiday::create([
            'name' => $request->name,
            'date' => Carbon::parse($request->date),
            'description' => $request->description,
            'company_id' => $companyId,
            'is_recurring' => $request->has('is_recurring'),
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('holidays.index')
            ->with('success', 'Holiday created successfully.');
    }

    /**
     * Show the form for editing a holiday.
     */
    public function edit(Holiday $holiday)
    {
        $user = auth()->user();
        
        // Check if user has access to edit this holiday
        // Super Admin can edit any, regular users only their company's
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($holiday->company_id !== $companyId) {
                abort(403, 'You are not authorized to edit this holiday.');
            }
        }
        
        $companyId = $this->getCompanyId();
        $companies = $user->isSuperAdmin() 
            ? Company::where('is_active', true)->get() 
            : Company::where('id', $companyId)->get();
        
        return view('management.holidays.edit', compact('holiday', 'companies'));
    }

    /**
     * Update the specified holiday.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $user = auth()->user();
        
        // Check if user has access to update this holiday
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($holiday->company_id !== $companyId) {
                abort(403, 'You are not authorized to update this holiday.');
            }
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);
        
        $data = [
            'name' => $request->name,
            'date' => Carbon::parse($request->date),
            'description' => $request->description,
            'is_recurring' => $request->has('is_recurring'),
            'is_active' => $request->has('is_active'),
        ];
        
        // Super Admin can change the company
        if ($user->isSuperAdmin() && $request->has('company_id') && $request->company_id) {
            $data['company_id'] = $request->company_id;
        }
        
        $holiday->update($data);
        
        return redirect()->route('holidays.index')
            ->with('success', 'Holiday updated successfully.');
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($holiday->company_id !== $companyId) {
                abort(403, 'You are not authorized to delete this holiday.');
            }
        }
        
        $holiday->delete();
        
        return redirect()->route('holidays.index')
            ->with('success', 'Holiday deleted successfully.');
    }

    /**
     * Toggle holiday active status.
     */
    public function toggle(Holiday $holiday)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($holiday->company_id !== $companyId) {
                abort(403, 'You are not authorized to modify this holiday.');
            }
        }
        
        $holiday->update(['is_active' => !$holiday->is_active]);
        
        $status = $holiday->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('holidays.index')
            ->with('success', "Holiday {$status} successfully.");
    }

    /**
     * Helper method to get company ID from session or default
     */
    private function getCompanyId()
    {
        return auth()->user()->getCurrentCompanyId();
    }
}
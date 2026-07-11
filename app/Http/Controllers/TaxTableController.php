<?php

namespace App\Http\Controllers;

use App\Models\TaxTable;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaxTableController extends Controller
{
    /**
     * Display a listing of tax tables - VISIBLE TO ALL USERS
     * ✅ Only shows Resident (National) tax tables
     */
    public function index()
    {
        $user = auth()->user();
        
        // ✅ ONLY show National/Resident tax tables (Expatriate hidden)
        $taxTables = TaxTable::with('company')
            ->where('employee_type', 'National')
            ->orderBy('min_amount')
            ->get();
        
        return view('management.tax.index', compact('taxTables'));
    }

    /**
     * Show the form for creating a new tax table.
     * ✅ Only allows creating National/Resident tax tables
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
        
        return view('management.tax.create', compact('companies'));
    }

    /**
     * Store a newly created tax table.
     * ✅ Forces employee_type to 'National' for all tax tables
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'fixed_tax' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after:effective_date',
        ]);

        $companyId = $this->getCompanyId();
        
        // Super Admin can select a specific company
        if ($user->isSuperAdmin() && $request->has('company_id') && $request->company_id) {
            $companyId = $request->company_id;
        }

        TaxTable::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'employee_type' => 'National', // ✅ FORCE National for ALL
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'tax_rate' => $request->tax_rate,
            'fixed_tax' => $request->fixed_tax ?? 0,
            'effective_date' => Carbon::parse($request->effective_date),
            'end_date' => $request->end_date ? Carbon::parse($request->end_date) : null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('tax-tables.index')
            ->with('success', 'Tax table created successfully.');
    }

    /**
     * Show the form for editing a tax table.
     */
    public function edit(TaxTable $taxTable)
    {
        $user = auth()->user();
        
        // Check if user has access to edit this tax table
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($taxTable->company_id !== $companyId) {
                abort(403, 'You are not authorized to edit this tax table.');
            }
        }
        
        $companyId = $this->getCompanyId();
        $companies = $user->isSuperAdmin() 
            ? Company::where('is_active', true)->get() 
            : Company::where('id', $companyId)->get();
        
        return view('management.tax.edit', compact('taxTable', 'companies'));
    }

    /**
     * Update the specified tax table.
     * ✅ Forces employee_type to 'National' for ALL
     */
    public function update(Request $request, TaxTable $taxTable)
    {
        $user = auth()->user();
        
        // Check if user has access to update this tax table
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($taxTable->company_id !== $companyId) {
                abort(403, 'You are not authorized to update this tax table.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'fixed_tax' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after:effective_date',
        ]);

        $data = [
            'name' => $request->name,
            'employee_type' => 'National', // ✅ FORCE National for ALL
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'tax_rate' => $request->tax_rate,
            'fixed_tax' => $request->fixed_tax ?? 0,
            'effective_date' => Carbon::parse($request->effective_date),
            'end_date' => $request->end_date ? Carbon::parse($request->end_date) : null,
            'is_active' => $request->has('is_active'),
        ];
        
        // Super Admin can change the company
        if ($user->isSuperAdmin() && $request->has('company_id') && $request->company_id) {
            $data['company_id'] = $request->company_id;
        }

        $taxTable->update($data);

        return redirect()->route('tax-tables.index')
            ->with('success', 'Tax table updated successfully.');
    }

    /**
     * Remove the specified tax table.
     */
    public function destroy(TaxTable $taxTable)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($taxTable->company_id !== $companyId) {
                abort(403, 'You are not authorized to delete this tax table.');
            }
        }
        
        $taxTable->delete();
        
        return redirect()->route('tax-tables.index')
            ->with('success', 'Tax table deleted successfully.');
    }

    /**
     * Toggle tax table active status.
     */
    public function toggle(TaxTable $taxTable)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($taxTable->company_id !== $companyId) {
                abort(403, 'You are not authorized to modify this tax table.');
            }
        }
        
        $taxTable->is_active = !$taxTable->is_active;
        $taxTable->save();

        $status = $taxTable->is_active ? 'activated' : 'deactivated';

        return redirect()->route('tax-tables.index')
            ->with('success', "Tax table {$status} successfully.");
    }

    /**
     * Helper method to get company ID from session or default
     */
    private function getCompanyId()
    {
        return auth()->user()->getCurrentCompanyId();
    }
}
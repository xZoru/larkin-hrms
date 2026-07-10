<?php

namespace App\Http\Controllers;

use App\Models\TaxTable;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxTableController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        
        $taxTables = TaxTable::where('company_id', $companyId)
            ->orderBy('employee_type')
            ->orderBy('min_amount')
            ->get();
        
        return view('management.tax.index', compact('taxTables'));
    }

    public function create()
    {
        return view('management.tax.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'employee_type' => 'required|in:National,Expatriate',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'fixed_tax' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after:effective_date',
            'is_active' => 'boolean',
        ]);

        TaxTable::create([
            'company_id' => Auth::user()->company_id,
            'name' => $request->name,
            'employee_type' => $request->employee_type,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'tax_rate' => $request->tax_rate,
            'fixed_tax' => $request->fixed_tax ?? 0,
            'effective_date' => $request->effective_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('tax-tables.index')
            ->with('success', 'Tax table created successfully.');
    }

    public function edit(TaxTable $taxTable)
    {
        return view('management.tax.edit', compact('taxTable'));
    }

    public function update(Request $request, TaxTable $taxTable)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'employee_type' => 'required|in:National,Expatriate',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'fixed_tax' => 'nullable|numeric|min:0',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after:effective_date',
            'is_active' => 'boolean',
        ]);

        $taxTable->update([
            'name' => $request->name,
            'employee_type' => $request->employee_type,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'tax_rate' => $request->tax_rate,
            'fixed_tax' => $request->fixed_tax ?? 0,
            'effective_date' => $request->effective_date,
            'end_date' => $request->end_date,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('tax-tables.index')
            ->with('success', 'Tax table updated successfully.');
    }

    public function destroy(TaxTable $taxTable)
    {
        $taxTable->delete();
        return redirect()->route('tax-tables.index')
            ->with('success', 'Tax table deleted successfully.');
    }

    public function toggle(TaxTable $taxTable)
    {
        $taxTable->is_active = !$taxTable->is_active;
        $taxTable->save();

        return redirect()->route('tax-tables.index')
            ->with('success', 'Tax table ' . ($taxTable->is_active ? 'activated' : 'deactivated') . ' successfully.');
    }
}
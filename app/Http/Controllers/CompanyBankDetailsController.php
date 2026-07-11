<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyBankDetailsController extends Controller
{
    public function index()
    {
        $companyId = Auth::user()->company_id;
        
        // Super Admin can see all companies, others see only their company
        if (auth()->user()->hasRole('Super Admin')) {
            $companies = Company::where('is_active', true)->orderBy('name')->get();
        } else {
            $companies = Company::where('id', $companyId)->get();
        }
        
        return view('management.company-bank-details.index', compact('companies'));
    }

    public function edit(Company $company)
    {
        return view('management.company-bank-details.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'bsb_code' => 'nullable|string|max:20',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
        ]);

        $company->update([
            'bank_name' => $request->bank_name,
            'bsb_code' => $request->bsb_code,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
        ]);

        return redirect()->route('company-bank-details.index')
            ->with('success', 'Bank details updated successfully for ' . $company->name);
    }
}
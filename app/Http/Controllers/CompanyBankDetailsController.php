<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyBankDetailsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $this->getCompanyId();
        
        if ($user->isSuperAdmin()) {
            $companies = Company::where('is_active', true)->get();
        } else {
            $companies = Company::where('id', $companyId)->get();
        }
        
        return view('management.company-bank-details.index', compact('companies'));
    }

    public function edit(Company $company)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($company->id !== $companyId) {
                abort(403, 'You are not authorized to edit this company.');
            }
        }
        
        return view('management.company-bank-details.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $user = auth()->user();
        
        if (!$user->isSuperAdmin()) {
            $companyId = $this->getCompanyId();
            if ($company->id !== $companyId) {
                abort(403, 'You are not authorized to update this company.');
            }
        }
        
        $request->validate([
            'bank_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:3',           
            'apca_user_id' => 'nullable|string|max:6',        
            'aba_file_format' => 'nullable|string|in:STANDARD,KUNDUPEI',  
            'bsb_code' => 'nullable|string|max:20',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
        ]);
        
        $company->update($request->only([
            'bank_name',
            'bank_code',           
            'apca_user_id',        
            'aba_file_format',     
            'bsb_code',
            'bank_account_number',
            'bank_account_name',
        ]));
        
        return redirect()->route('company-bank-details.index')
            ->with('success', 'Company bank details updated successfully.');
    }

    /**
     * Helper method to get company ID from session or default
     */
    private function getCompanyId()
    {
        return auth()->user()->getCurrentCompanyId();
    }
}
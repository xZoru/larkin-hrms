<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function switch(Request $request, $companyId)
    {
        $user = auth()->user();
        
        // ✅ Check if user belongs to this company
        if (!$user->belongsToCompany($companyId)) {
            return response()->json(['success' => false, 'message' => 'You do not have access to this company.'], 403);
        }
        
        $company = Company::find($companyId);
        
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found.'], 404);
        }
        
        // ✅ Set session
        session(['current_company_id' => $company->id]);
        session(['current_company_name' => $company->name]);
        
        return response()->json([
            'success' => true,
            'message' => 'Switched to ' . $company->name,
            'company' => $company
        ]);
    }
}
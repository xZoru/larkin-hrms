<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function switch(Request $request, $companyId)
    {
        $user = auth()->user();
        
        $company = Company::where('is_active', true)->find($companyId);
        
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found.'], 404);
        }

        // Super Admins can access every active company and are not assigned
        // company_user pivot rows. All other users need an explicit assignment.
        if (!$user->isSuperAdmin() && !$user->belongsToCompany($companyId)) {
            return response()->json(['success' => false, 'message' => 'You do not have access to this company.'], 403);
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

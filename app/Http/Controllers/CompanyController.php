<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function switch(Request $request, Company $company)
    {
        // Store company in session
        session(['current_company_id' => $company->id, 'current_company_name' => $company->name]);
        
        // Also update user's company_id if they're Super Admin
        $user = Auth::user();
        if ($user->hasRole('Super Admin')) {
            $user->company_id = $company->id;
            $user->save();
        }
        
        return response()->json(['success' => true, 'message' => 'Switched to ' . $company->name]);
    }
}
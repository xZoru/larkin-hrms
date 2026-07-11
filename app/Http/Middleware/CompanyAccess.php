<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CompanyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin bypasses all checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // ✅ Ensure user has companies assigned
        if ($user->companies()->count() === 0) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not assigned to any company. Please contact your administrator.');
        }

        // ✅ Ensure session has a company ID
        if (!session('current_company_id')) {
            $defaultCompany = $user->default_company;
            if ($defaultCompany) {
                session(['current_company_id' => $defaultCompany->id]);
                session(['current_company_name' => $defaultCompany->name]);
            } else {
                // Set first company as default
                $firstCompany = $user->companies()->first();
                if ($firstCompany) {
                    $user->setDefaultCompany($firstCompany->id);
                    session(['current_company_id' => $firstCompany->id]);
                    session(['current_company_name' => $firstCompany->name]);
                }
            }
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CompanyAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If not logged in, redirect to login
        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin can access everything without company restriction
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Other users must have a company assigned
        if (!$user->company_id) {
            return redirect()->route('dashboard')->with('error', 'Please select a company first.');
        }

        return $next($request);
    }
}
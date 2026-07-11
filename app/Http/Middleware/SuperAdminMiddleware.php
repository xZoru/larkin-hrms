<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Administrators can access this area.');
        }
        return $next($request);
    }
}
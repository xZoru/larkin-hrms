<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Company;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $companyIds = $user->isSuperAdmin()
            ? Company::query()->pluck('id')
            : $user->companies()->pluck('companies.id');
        if ($companyIds->isEmpty() && $user->company_id) {
            $companyIds = collect([$user->company_id]);
        }
        $dashboardCompanyLabel = $user->isSuperAdmin()
            ? 'All Companies'
            : ($companyIds->count() > 1
                ? 'All Accessible Companies'
                : ($user->getCurrentCompanyName() ?? 'Company'));
        $allowedTypes = $user->getAllowedEmployeeTypes();

        $recentLeaveRequests = LeaveRequest::with('employee.company')
            ->whereHas('employee', function ($query) use ($companyIds, $allowedTypes) {
                $query->whereIn('company_id', $companyIds)
                    ->active()
                    ->whereIn('employee_type', $allowedTypes);
            })
            ->where('status', 'Pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $today = now()->toDateString();
        $expatriatesOnLeave = LeaveRequest::with('employee.company')
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereHas('employee', function ($query) use ($companyIds) {
                $query->whereIn('company_id', $companyIds)
                    ->active()
                    ->where('employee_type', 'Expatriate');
            })
            ->orderBy('end_date')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'recentLeaveRequests',
            'expatriatesOnLeave',
            'companyIds',
            'dashboardCompanyLabel'
        ));
    }
}

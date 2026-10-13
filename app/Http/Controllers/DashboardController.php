<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $companyId = auth()->user()->getCurrentCompanyId();
        $allowedTypes = auth()->user()->getAllowedEmployeeTypes();

        $recentLeaveRequests = LeaveRequest::with('employee')
            ->whereHas('employee', function ($query) use ($companyId, $allowedTypes) {
                $query->where('company_id', $companyId)
                    ->active()
                    ->whereIn('employee_type', $allowedTypes);
            })
            ->where('status', 'Pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $today = now()->toDateString();
        $expatriatesOnLeave = LeaveRequest::with('employee')
            ->where('status', 'Approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereHas('employee', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->active()
                    ->where('employee_type', 'Expatriate');
            })
            ->orderBy('end_date')
            ->limit(10)
            ->get();

        return view('dashboard', compact('recentLeaveRequests', 'expatriatesOnLeave'));
    }
}

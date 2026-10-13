<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Loan;
use Carbon\Carbon;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->getCurrentCompanyId();
        $user = auth()->user();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        // Stat data - filtered by user type
        $totalEmployees = Employee::where('company_id', $companyId)
            ->active()
            ->whereIn('employee_type', $allowedTypes)
            ->count();
        
        $pendingLeaves = LeaveRequest::whereHas('employee', function($q) use ($companyId, $allowedTypes) {
            $q->where('company_id', $companyId)
              ->active()
              ->whereIn('employee_type', $allowedTypes);
        })->where('status', 'Pending')->count();
        
        $currentFortnight = $this->getCurrentFortnight();
        $totalPayroll = Payroll::where('company_id', $companyId)
            ->where('fortnight_number', $currentFortnight)
            ->sum('total_net') ?? 0;
        
        $activeLoans = Loan::where('company_id', $companyId)
            ->where('status', 'Active')
            ->count();
        
        // Recent data - filtered by user type
        $recentEmployees = Employee::where('company_id', $companyId)
            ->active()
            ->whereIn('employee_type', $allowedTypes)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $recentLeaveRequests = LeaveRequest::whereHas('employee', function($q) use ($companyId, $allowedTypes) {
            $q->where('company_id', $companyId)
              ->active()
              ->whereIn('employee_type', $allowedTypes);
        })->where('status', 'Pending')
          ->orderBy('created_at', 'desc')
          ->limit(5)
          ->get();
        
        return view('dashboard', compact(
            'totalEmployees',
            'pendingLeaves',
            'totalPayroll',
            'activeLoans',
            'recentEmployees',
            'recentLeaveRequests'
        ));
    }
    private function getCurrentFortnight(): string
    {
        $year = date('y');
        $start = Carbon::createFromDate(date('Y') - 1, 12, 25);
        $daysSinceStart = $start->diffInDays(now()) + 1;
        $fortnight = (int) ceil($daysSinceStart / 14);

        return $year . str_pad((string) $fortnight, 2, '0', STR_PAD_LEFT);
    }
}

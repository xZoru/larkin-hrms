<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    /**
     * Display a listing of leave requests
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // ✅ FIXED
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $query = LeaveRequest::whereHas('employee', function($q) use ($companyId, $allowedTypes) {
            $q->where('company_id', $companyId)
              ->whereIn('employee_type', $allowedTypes);
        })->with(['employee', 'approver']);
        
        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        
        if ($request->date_from) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }
        
        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get employees for filter - filtered by user type
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->orderBy('full_name')
            ->get(['id', 'employee_number', 'full_name']);
        
        // Get leave statistics
        $statistics = $this->getLeaveStatistics($companyId);
        
        return view('leave.index', compact('leaveRequests', 'employees', 'statistics'));
    }

    /**
     * Show the form for creating a new leave request
     */
    public function create()
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // ✅ FIXED
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->orderBy('full_name')
            ->get(['id', 'employee_number', 'full_name', 'joining_date']);
        
        return view('leave.create', compact('employees'));
    }

    /**
     * Store a newly created leave request
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:Annual,Sick,Casual,Maternity,Paternity,Bereavement,Public Holiday,Unpaid',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        // Verify employee is allowed
        $employee = Employee::find($request->employee_id);
        if (!$user->canViewEmployee($employee)) {
            return back()->with('error', 'You are not authorized to create a leave request for this employee.');
        }

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1;

        if ($request->leave_type === 'Annual') {
            $balance = $this->calculateLeaveBalance($request->employee_id);
            if ($balance->balance < $days) {
                return back()->with('error', 'Insufficient leave balance. Available: ' . $balance->balance . ' days');
            }
        }

        $overlap = LeaveRequest::where('employee_id', $request->employee_id)
            ->where('status', 'Pending')
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        if ($overlap) {
            return back()->with('error', 'Employee already has a pending leave request for this period.');
        }

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $request->employee_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_requested' => $days,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        return redirect()->route('leave.index')
            ->with('success', 'Leave request created successfully.');
    }

    /**
     * Display the specified leave request
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorizeLeaveRequest($leaveRequest);
        $leaveRequest->load(['employee', 'approver']);
        
        $leaveRecord = LeaveRecord::where('employee_id', $leaveRequest->employee_id)
            ->where('year', now()->year)
            ->first();
        
        $balance = $this->calculateLeaveBalance($leaveRequest->employee_id);
        
        return view('leave.show', compact('leaveRequest', 'leaveRecord', 'balance'));
    }

    /**
     * Show the form for editing the specified leave request
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        $this->authorizeLeaveRequest($leaveRequest);
        
        if ($leaveRequest->status !== 'Pending') {
            return redirect()->route('leave.index')
                ->with('error', 'Cannot edit a leave request that has been processed.');
        }
        
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // ✅ FIXED
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->get(['id', 'employee_number', 'full_name', 'joining_date']);
        
        return view('leave.edit', compact('leaveRequest', 'employees'));
    }

    /**
     * Update the specified leave request
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeLeaveRequest($leaveRequest);
        
        if ($leaveRequest->status !== 'Pending') {
            return redirect()->route('leave.index')
                ->with('error', 'Cannot update a leave request that has been processed.');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1;

        $leaveRequest->update([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_requested' => $days,
            'reason' => $request->reason,
        ]);

        return redirect()->route('leave.index')
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Remove the specified leave request
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $this->authorizeLeaveRequest($leaveRequest);
        
        if ($leaveRequest->status === 'Approved') {
            return redirect()->route('leave.index')
                ->with('error', 'Cannot delete an approved leave request.');
        }
        
        $leaveRequest->delete();
        
        return redirect()->route('leave.index')
            ->with('success', 'Leave request deleted successfully.');
    }

    /**
     * Approve a leave request
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        $this->authorizeLeaveRequest($leaveRequest);
        
        if ($leaveRequest->status !== 'Pending') {
            return redirect()->route('leave.index')
                ->with('error', 'This leave request has already been processed.');
        }

        if ($leaveRequest->leave_type === 'Annual') {
            $balance = $this->calculateLeaveBalance($leaveRequest->employee_id);
            if ($balance->balance < $leaveRequest->days_requested) {
                return redirect()->route('leave.index')
                    ->with('error', 'Employee has insufficient leave balance.');
            }
        }

        $leaveRequest->approve(auth()->id());

        return redirect()->route('leave.show', $leaveRequest)
            ->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject a leave request
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeLeaveRequest($leaveRequest);
        
        if ($leaveRequest->status !== 'Pending') {
            return redirect()->route('leave.index')
                ->with('error', 'This leave request has already been processed.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leaveRequest->reject($request->rejection_reason, auth()->id());

        return redirect()->route('leave.show', $leaveRequest)
            ->with('success', 'Leave request rejected.');
    }

    /**
     * Cancel a leave request (by employee)
     */
    public function cancel(LeaveRequest $leaveRequest)
    {
        $this->authorizeLeaveRequest($leaveRequest);
        
        if ($leaveRequest->status !== 'Pending') {
            return redirect()->route('leave.index')
                ->with('error', 'Cannot cancel a processed leave request.');
        }

        $leaveRequest->update([
            'status' => 'Cancelled',
        ]);

        return redirect()->route('leave.index')
            ->with('success', 'Leave request cancelled successfully.');
    }

    /**
     * Get leave statistics
     */
    private function getLeaveStatistics($companyId)
    {
        $user = auth()->user();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $employeeIds = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->pluck('id');
        
        $pending = LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Pending')
            ->count();
        
        $approved = LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Approved')
            ->count();
        
        $rejected = LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Rejected')
            ->count();
        
        $cancelled = LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Cancelled')
            ->count();

        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->get();
        
        $totalBalance = 0;
        foreach ($employees as $employee) {
            $balance = $this->calculateLeaveBalance($employee->id);
            $totalBalance += $balance->balance;
        }

        return (object) [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'cancelled' => $cancelled,
            'total_balance' => $totalBalance,
            'total_employees' => $employees->count(),
        ];
    }

    /**
     * Calculate leave balance for an employee
     */
    private function calculateLeaveBalance($employeeId)
    {
        $employee = Employee::find($employeeId);
        
        if (!$employee || !$employee->joining_date) {
            return (object) ['earned' => 0, 'taken' => 0, 'balance' => 0];
        }

        $months = Carbon::parse($employee->joining_date)->diffInMonths(now());
        $earned = floor($months / 1.5);
        $earned = min($earned, 9);

        $record = LeaveRecord::where('employee_id', $employeeId)
            ->where('year', now()->year)
            ->first();
        
        $taken = $record ? $record->leave_taken : 0;

        $balance = max(0, $earned - $taken);

        return (object) [
            'earned' => $earned,
            'taken' => $taken,
            'balance' => $balance,
        ];
    }

    /**
     * Check if user can view/manage this leave request
     */
    private function authorizeLeaveRequest($leaveRequest)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // ✅ FIXED
        
        if ($leaveRequest->employee->company_id !== $companyId) {
            abort(403, 'Unauthorized access to this leave request.');
        }
        
        if (!$user->canViewEmployee($leaveRequest->employee)) {
            abort(403, 'You are not authorized to view this employee.');
        }
    }

    /**
     * Get leave balance for an employee (API endpoint for dropdown)
     */
    public function getBalance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);
        
        $user = auth()->user();
        $employee = Employee::find($request->employee_id);
        if (!$user->canViewEmployee($employee)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $balance = $this->calculateLeaveBalance($request->employee_id);
        
        return response()->json($balance);
    }
}
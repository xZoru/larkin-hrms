<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Employee;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanRequestController extends Controller
{
    /** Employee-focused loan balances and deduction history. */
    public function management(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId();
        $allowedTypes = $user->getAllowedEmployeeTypes();

        $employees = Employee::where('company_id', $companyId)
            ->active()
            ->whereIn('employee_type', $allowedTypes)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'employee_number', 'full_name']);

        $employee = null;
        $loans = collect();
        $payments = collect();
        $payrollDeductions = collect();

        if ($request->filled('employee_id')) {
            $employee = Employee::where('id', $request->integer('employee_id'))
                ->where('company_id', $companyId)
                ->active()
                ->whereIn('employee_type', $allowedTypes)
                ->firstOrFail();

            $loans = Loan::where('company_id', $companyId)
                ->where('employee_id', $employee->id)
                ->with(['payments.payroll'])
                ->latest()
                ->get();

            $payments = LoanPayment::whereHas('loan', function ($query) use ($companyId, $employee) {
                $query->where('company_id', $companyId)
                    ->where('employee_id', $employee->id);
            })
                ->with(['loan', 'payroll'])
                ->latest()
                ->get();

            $payrollDeductions = $employee->payrollItems()
                ->where('loan_deduction', '>', 0)
                ->with('payroll')
                ->latest()
                ->get();
        }

        return view('loan-requests.management', compact(
            'employees', 'employee', 'loans', 'payments', 'payrollDeductions'
        ));
    }

    public function index()
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // FIXED: Use helper method
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $loans = Loan::with(['employee', 'approver', 'releaser', 'creator'])
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $employees = Employee::where('company_id', $companyId) //  FIXED: Use $companyId
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->orderBy('last_name')
            ->get();

        return view('loan-requests.index', compact('loans', 'employees'));
    }

    public function create()
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); //  FIXED: Use helper method
        $allowedTypes = $user->getAllowedEmployeeTypes();

        $employees = Employee::where('company_id', $companyId) //  FIXED: Use $companyId
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->orderBy('last_name')
            ->get();

        return view('loan-requests.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); //  FIXED: Use helper method
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $request->validate([
            'loans' => 'required|array|min:1',
            'loans.*.employee_id' => 'required|exists:employees,id',
            'loans.*.loan_type' => 'required|in:Cash Advance,Loan,Company Deductions',
            'loans.*.amount' => 'required|numeric|min:1',
            'loans.*.deduction_per_cutoff' => 'required|numeric|min:0.01',
            'loans.*.reason' => 'nullable|string|max:500',
        ]);

        $createdCount = 0;
        $errors = [];

        foreach ($request->loans as $loanData) {
            try {
                // Verify employee exists and is allowed
                $employee = Employee::find($loanData['employee_id']);
                if (!$employee || $employee->company_id !== $companyId || $employee->status !== 'Active'
                    || !in_array($employee->employee_type, $allowedTypes)) {
                    $errors[] = "Employee ID: {$loanData['employee_id']} - Not authorized";
                    continue;
                }

                $amount = (float) $loanData['amount'];

                // Fortnightly deduction is now the user-entered value. Cash Advance
                // is always a single-cutoff deduction, so the full amount is deducted
                // next payroll regardless of what was typed.
                $deductionPerCutoff = $loanData['loan_type'] === 'Cash Advance'
                    ? $amount
                    : min((float) $loanData['deduction_per_cutoff'], $amount);

                // installment_count is now derived purely for reporting/display -
                // it's how many fortnights it will take to clear the balance.
                $installmentCount = $deductionPerCutoff > 0
                    ? (int) ceil($amount / $deductionPerCutoff)
                    : 1;

                Loan::create([
                    'company_id' => $companyId,
                    'employee_id' => $loanData['employee_id'],
                    'loan_type' => $loanData['loan_type'],
                    'amount' => $amount,
                    'deduction_per_cutoff' => $deductionPerCutoff,
                    'remaining_balance' => $amount,
                    'total_paid' => 0,
                    'installment_count' => $installmentCount,
                    'payments_made' => 0,
                    'reason' => $loanData['reason'] ?? null,
                    'status' => 'Pending',
                    'created_by' => Auth::id()
                ]);
                $createdCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed for employee ID: {$loanData['employee_id']} - " . $e->getMessage();
            }
        }

        if ($createdCount > 0) {
            return redirect()->route('loan-requests.index')
                ->with('success', "{$createdCount} loan request(s) created successfully!" . 
                    ($errors ? ' Errors: ' . implode(', ', $errors) : ''));
        } else {
            return redirect()->route('loan-requests.index')
                ->with('error', 'Failed to create loan requests. ' . implode(', ', $errors));
        }
    }

    public function show(Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);
        
        $loanRequest->load(['employee', 'payments', 'approver', 'releaser', 'creator']);
        
        return view('loan-requests.show', compact('loanRequest'));
    }

    public function edit(Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);
        
        if (!$loanRequest->canBeEdited()) {
            return back()->with('error', 'This loan request cannot be edited.');
        }

        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // ✅ FIXED: Use helper method
        $allowedTypes = $user->getAllowedEmployeeTypes();
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->orderBy('last_name')
            ->get();

        return view('loan-requests.edit', compact('loanRequest', 'employees'));
    }

    public function update(Request $request, Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);
        
        if (!$loanRequest->canBeEdited()) {
            return back()->with('error', 'This loan request cannot be edited.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'loan_type' => ['required', Rule::in(['Cash Advance', 'Loan', 'Company Deductions'])],
            'amount' => 'required|numeric|min:1',
            'deduction_per_cutoff' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
        ]);

        // Verify employee is allowed
        $user = auth()->user();
        $employee = Employee::find($request->employee_id);
        if (!$employee || $employee->status !== 'Active' || !$user->canViewEmployee($employee)) {
            return back()->with('error', 'You are not authorized to create a loan for this employee.');
        }

        $amount = (float) $request->amount;
        $deductionPerCutoff = $request->loan_type === 'Cash Advance'
            ? $amount
            : min((float) $request->deduction_per_cutoff, $amount);
        $installmentCount = $deductionPerCutoff > 0
            ? (int) ceil($amount / $deductionPerCutoff)
            : 1;

        $loanRequest->update([
            'employee_id' => $request->employee_id,
            'loan_type' => $request->loan_type,
            'amount' => $amount,
            'deduction_per_cutoff' => $deductionPerCutoff,
            'installment_count' => $installmentCount,
            'reason' => $request->reason,
        ]);

        $loanRequest->remaining_balance = $loanRequest->amount - $loanRequest->total_paid;
        $loanRequest->save();

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request updated successfully!');
    }

    public function approve(Request $request, Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);
        
        if (!$loanRequest->canBeApproved()) {
            return response()->json(['error' => 'This loan request cannot be approved.'], 422);
        }

        $loanRequest->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
            'approved_date' => now()
        ]);

        return response()->json(['success' => 'Loan request approved successfully!']);
    }

    public function release(Request $request, Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);
        
        if (!$loanRequest->canBeReleased()) {
            return response()->json(['error' => 'This loan request cannot be released.'], 422);
        }

        $loanRequest->update([
            'status' => 'Released',
            'released_by' => Auth::id(),
            'released_date' => now()
        ]);

        return response()->json(['success' => 'Loan request released successfully!']);
    }

    public function reject(Request $request, Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);
        
        if (!$loanRequest->canBeRejected()) {
            return response()->json(['error' => 'This loan request cannot be rejected.'], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $loanRequest->update([
            'status' => 'Rejected',
            'notes' => $request->reason ?? 'Rejected by ' . Auth::user()->name
        ]);

        return response()->json(['success' => 'Loan request rejected successfully!']);
    }

    public function hold(Request $request, Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);
        
        if (!$loanRequest->canBePutOnHold()) {
            return response()->json(['error' => 'This loan request cannot be put on hold.'], 422);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $loanRequest->update([
            'status' => 'On-Hold',
            'notes' => 'On Hold: ' . $request->reason
        ]);

        return response()->json(['success' => 'Loan request put on hold successfully!']);
    }

    public function destroy(Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);

        $loanRequest->delete();

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request deleted successfully!');
    }

    // API: Search employees for dropdown
    public function searchEmployees(Request $request)
    {
        $search = $request->get('q');
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // ✅ FIXED: Use helper method
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $employees = Employee::where('company_id', $companyId)
            ->active()
            ->whereIn('employee_type', $allowedTypes)
            ->where(function($query) use ($search) {
                $query->where('employee_number', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$search}%");
            })
            ->where('status', 'Active')
            ->limit(10)
            ->get(['id', 'employee_number', 'first_name', 'last_name']);

        return response()->json($employees);
    }

    // API: Get employee loan history
    public function employeeLoans(Employee $employee)
    {
        $user = auth()->user();
        if (!$user->canViewEmployee($employee)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $loans = Loan::where('employee_id', $employee->id)
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($loans);
    }

    // Bulk add - multiple loan requests at once
    public function bulkStore(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // ✅ FIXED: Use helper method
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $request->validate([
            'loans' => 'required|array|min:1',
            'loans.*.employee_id' => 'required|exists:employees,id',
            'loans.*.loan_type' => ['required', Rule::in(['Cash Advance', 'Loan', 'Company Deductions'])],
            'loans.*.amount' => 'required|numeric|min:1',
            'loans.*.deduction_per_cutoff' => 'required|numeric|min:0.01',
            'loans.*.reason' => 'nullable|string|max:500',
        ]);

        $createdCount = 0;
        $errors = [];

        foreach ($request->loans as $loanData) {
            try {
                $employee = Employee::find($loanData['employee_id']);
                if (!$employee || $employee->company_id !== $companyId || $employee->status !== 'Active'
                    || !in_array($employee->employee_type, $allowedTypes)) {
                    $errors[] = "Employee ID: {$loanData['employee_id']} - Not authorized";
                    continue;
                }

                $amount = (float) $loanData['amount'];
                $deductionPerCutoff = $loanData['loan_type'] === 'Cash Advance'
                    ? $amount
                    : min((float) $loanData['deduction_per_cutoff'], $amount);
                $installmentCount = $deductionPerCutoff > 0
                    ? (int) ceil($amount / $deductionPerCutoff)
                    : 1;

                Loan::create([
                    'company_id' => $companyId,
                    'employee_id' => $loanData['employee_id'],
                    'loan_type' => $loanData['loan_type'],
                    'amount' => $amount,
                    'deduction_per_cutoff' => $deductionPerCutoff,
                    'remaining_balance' => $amount,
                    'total_paid' => 0,
                    'installment_count' => $installmentCount,
                    'payments_made' => 0,
                    'reason' => $loanData['reason'],
                    'status' => 'Pending',
                    'created_by' => Auth::id()
                ]);
                $createdCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to create loan for employee ID: {$loanData['employee_id']}";
            }
        }

        return redirect()->route('loan-requests.index')
            ->with('success', "{$createdCount} loan request(s) created successfully!" . 
                ($errors ? ' Errors: ' . implode(', ', $errors) : ''));
    }

    // Get loan payment history
    public function paymentHistory(Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);
        
        $payments = $loanRequest->payments()
            ->with('payroll')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('loan-requests.payments', compact('loanRequest', 'payments'));
    }

    // Manual payment entry
    public function addManualPayment(Request $request, Loan $loanRequest)
    {
        $this->authorizeCompany($loanRequest);
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $loanRequest->remaining_balance,
            'notes' => 'nullable|string|max:500'
        ]);

        if ($loanRequest->status === 'Completed') {
            return back()->with('error', 'This loan is already completed.');
        }

        $loanRequest->addPayment(
            $request->amount,
            null,
            $request->notes ?? 'Manual payment'
        );

        return back()->with('success', 'Payment recorded successfully!');
    }

    private function authorizeCompany(Loan $loanRequest)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId(); // ✅ FIXED: Use helper method
        if ($loanRequest->company_id !== $companyId) {
            abort(403, 'Unauthorized access to this loan request.');
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Employee;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::with(['employee', 'approver', 'releaser', 'creator'])
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        $employees = Employee::where('company_id', Auth::user()->company_id)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get();

        return view('loans.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'loan_type' => ['required', Rule::in(['Cash Advance', 'Loan', 'Company Deductions'])],
            'amount' => 'required|numeric|min:1',
            'installment_count' => 'nullable|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $companyId = Auth::user()->company_id;

        // Calculate deduction per cutoff
        $installmentCount = $request->installment_count ?? 4;
        $deductionPerCutoff = $request->amount / $installmentCount;

        $loan = Loan::create([
            'company_id' => $companyId,
            'employee_id' => $request->employee_id,
            'loan_type' => $request->loan_type,
            'amount' => $request->amount,
            'deduction_per_cutoff' => $deductionPerCutoff,
            'remaining_balance' => $request->amount,
            'total_paid' => 0,
            'installment_count' => $installmentCount,
            'payments_made' => 0,
            'reason' => $request->reason,
            'status' => 'Pending',
            'created_by' => Auth::id()
        ]);

        return redirect()->route('loans.index')
            ->with('success', 'Loan request created successfully!');
    }

    public function show(Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        $loan->load(['employee', 'payments', 'approver', 'releaser', 'creator']);
        
        return view('loans.show', compact('loan'));
    }

    public function edit(Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        if (!$loan->canBeEdited()) {
            return back()->with('error', 'This loan cannot be edited.');
        }

        $employees = Employee::where('company_id', Auth::user()->company_id)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get();

        return view('loans.edit', compact('loan', 'employees'));
    }

    public function update(Request $request, Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        if (!$loan->canBeEdited()) {
            return back()->with('error', 'This loan cannot be edited.');
        }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'loan_type' => ['required', Rule::in(['Cash Advance', 'Loan', 'Company Deductions'])],
            'amount' => 'required|numeric|min:1',
            'installment_count' => 'nullable|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $installmentCount = $request->installment_count ?? 4;
        $deductionPerCutoff = $request->amount / $installmentCount;

        $loan->update([
            'employee_id' => $request->employee_id,
            'loan_type' => $request->loan_type,
            'amount' => $request->amount,
            'deduction_per_cutoff' => $deductionPerCutoff,
            'installment_count' => $installmentCount,
            'reason' => $request->reason,
        ]);

        // Recalculate remaining balance
        $loan->remaining_balance = $loan->amount - $loan->total_paid;
        $loan->save();

        return redirect()->route('loans.index')
            ->with('success', 'Loan updated successfully!');
    }

    public function approve(Request $request, Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        if (!$loan->canBeApproved()) {
            return response()->json(['error' => 'This loan cannot be approved.'], 422);
        }

        $loan->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
            'approved_date' => now()
        ]);

        return response()->json(['success' => 'Loan approved successfully!']);
    }

    public function release(Request $request, Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        if (!$loan->canBeReleased()) {
            return response()->json(['error' => 'This loan cannot be released.'], 422);
        }

        $loan->update([
            'status' => 'Released',
            'released_by' => Auth::id(),
            'released_date' => now()
        ]);

        return response()->json(['success' => 'Loan released successfully!']);
    }

    public function reject(Request $request, Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        if (!$loan->canBeRejected()) {
            return response()->json(['error' => 'This loan cannot be rejected.'], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $loan->update([
            'status' => 'Rejected',
            'notes' => $request->reason ?? 'Rejected by ' . Auth::user()->name
        ]);

        return response()->json(['success' => 'Loan rejected successfully!']);
    }

    public function hold(Request $request, Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        if (!$loan->canBePutOnHold()) {
            return response()->json(['error' => 'This loan cannot be put on hold.'], 422);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $loan->update([
            'status' => 'On-Hold',
            'notes' => 'On Hold: ' . $request->reason
        ]);

        return response()->json(['success' => 'Loan put on hold successfully!']);
    }

    public function destroy(Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        if (!$loan->canBeEdited()) {
            return back()->with('error', 'This loan cannot be deleted.');
        }

        $loan->delete();

        return redirect()->route('loans.index')
            ->with('success', 'Loan deleted successfully!');
    }

    // API endpoint for employee search
    public function searchEmployees(Request $request)
    {
        $search = $request->get('q');
        
        $employees = Employee::where('company_id', Auth::user()->company_id)
            ->where(function($query) use ($search) {
                $query->where('employee_number', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$search}%");
            })
            ->where('status', 'active')
            ->limit(10)
            ->get(['id', 'employee_number', 'first_name', 'last_name']);

        return response()->json($employees);
    }

    // Get employee loan history
    public function employeeLoans(Employee $employee)
    {
        $loans = Loan::where('employee_id', $employee->id)
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($loans);
    }

    // Bulk add - multiple loans at once
    public function bulkStore(Request $request)
    {
        $request->validate([
            'loans' => 'required|array|min:1',
            'loans.*.employee_id' => 'required|exists:employees,id',
            'loans.*.loan_type' => ['required', Rule::in(['Cash Advance', 'Loan', 'Company Deductions'])],
            'loans.*.amount' => 'required|numeric|min:1',
            'loans.*.installment_count' => 'nullable|integer|min:1',
            'loans.*.reason' => 'nullable|string|max:500',
        ]);

        $companyId = Auth::user()->company_id;
        $createdCount = 0;
        $errors = [];

        foreach ($request->loans as $loanData) {
            try {
                $installmentCount = $loanData['installment_count'] ?? 4;
                $deductionPerCutoff = $loanData['amount'] / $installmentCount;

                Loan::create([
                    'company_id' => $companyId,
                    'employee_id' => $loanData['employee_id'],
                    'loan_type' => $loanData['loan_type'],
                    'amount' => $loanData['amount'],
                    'deduction_per_cutoff' => $deductionPerCutoff,
                    'remaining_balance' => $loanData['amount'],
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

        return redirect()->route('loans.index')
            ->with('success', "{$createdCount} loan(s) created successfully!" . 
                ($errors ? ' Errors: ' . implode(', ', $errors) : ''));
    }

    // Get loan payment history
    public function paymentHistory(Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        $payments = $loan->payments()
            ->with('payroll')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('loans.payments', compact('loan', 'payments'));
    }

    // Manual payment entry
    public function addManualPayment(Request $request, Loan $loan)
    {
        $this->authorizeCompany($loan);
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $loan->remaining_balance,
            'notes' => 'nullable|string|max:500'
        ]);

        if ($loan->status === 'Completed') {
            return back()->with('error', 'This loan is already completed.');
        }

        $loan->addPayment(
            $request->amount,
            null,
            $request->notes ?? 'Manual payment'
        );

        return back()->with('success', 'Payment recorded successfully!');
    }

    private function authorizeCompany(Loan $loan)
    {
        if ($loan->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this loan.');
        }
    }
}
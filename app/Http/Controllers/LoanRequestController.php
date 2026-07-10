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
    public function index()
    {

    $companyId = Auth::user()->company_id;
    
    $loans = Loan::with(['employee', 'approver', 'releaser', 'creator'])
        ->where('company_id', $companyId)
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    $employees = Employee::where('company_id', Auth::user()->company_id)
        ->where('status', 'Active')
        ->orderBy('last_name')
        ->get();

        return view('loan-requests.index', compact('loans', 'employees'));
    }

    public function create()
    {
        $companyId = Auth::user()->company_id;

        $employees = Employee::where('company_id', Auth::user()->company_id)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get();

        return view('loan-requests.create', compact('employees'));
    }

    public function store(Request $request)
    {

    $request->validate([
        'loans' => 'required|array|min:1',
        'loans.*.employee_id' => 'required|exists:employees,id',
        'loans.*.loan_type' => 'required|in:Cash Advance,Loan,Company Deductions',
        'loans.*.amount' => 'required|numeric|min:1',
        'loans.*.reason' => 'nullable|string|max:500',
    ]);

        $companyId = Auth::user()->company_id;
        $createdCount = 0;
        $errors = [];

        foreach ($request->loans as $loanData) {
            try {
                $installmentCount = 4; // Default
                $amount = $loanData['amount'];
                $deductionPerCutoff = $loanData['deduction'] ?? ($amount / $installmentCount);

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

        $employees = Employee::where('company_id', Auth::user()->company_id)
            ->where('status', 'active')
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
            'installment_count' => 'nullable|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        $installmentCount = $request->installment_count ?? 4;
        $deductionPerCutoff = $request->amount / $installmentCount;

        $loanRequest->update([
            'employee_id' => $request->employee_id,
            'loan_type' => $request->loan_type,
            'amount' => $request->amount,
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
        
        if (!$loanRequest->canBeEdited()) {
            return back()->with('error', 'This loan request cannot be deleted.');
        }

        $loanRequest->delete();

        return redirect()->route('loan-requests.index')
            ->with('success', 'Loan request deleted successfully!');
    }

    // API: Search employees for dropdown
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

    // API: Get employee loan history
    public function employeeLoans(Employee $employee)
    {
        $loans = Loan::where('employee_id', $employee->id)
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($loans);
    }

    // Bulk add - multiple loan requests at once
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
        if ($loanRequest->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized access to this loan request.');
        }
    }
}
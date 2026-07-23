<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\AttendanceSummary;
use App\Models\Loan;
use App\Models\TaxTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\ABAGeneratorService;

class PayrollController extends Controller
{
    // ============ LIST PAYROLLS ============
    public function index(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId();
        
        $payrolls = Payroll::where('company_id', $companyId)
            ->orderBy('period_start', 'desc')
            ->paginate(20);

        $fortnights = Payroll::where('company_id', $companyId)
            ->distinct()
            ->pluck('fortnight_number')
            ->toArray();
        
        $fortnightPeriods = [];
        foreach ($fortnights as $fn) {
            $fortnightPeriods[$fn] = $this->getFortnightPeriod($fn);
        }

        return view('payroll.index', compact('payrolls', 'fortnights', 'fortnightPeriods'));
    }

    // ============ CREATE PAYROLL ============
    public function create(Request $request)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        $fortnight = $request->fortnight ?? $this->getCurrentFortnight();
        $period = $this->getFortnightPeriod($fortnight);

        $allFortnights = $this->getAllFortnights();
        
        $fortnightPeriods = [];
        foreach ($allFortnights as $fn) {
            $fortnightPeriods[$fn] = $this->getFortnightPeriod($fn);
        }

        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->with(['attendanceSummaries' => function($query) use ($fortnight) {
                $query->where('fortnight_number', $fortnight);
            }])
            ->get();

        $activeLoans = Loan::where('company_id', $companyId)
            ->whereIn('status', ['Approved', 'Released'])
            ->where('remaining_balance', '>', 0)
            ->with('employee')
            ->get();

        return view('payroll.create', compact('employees', 'fortnight', 'period', 'allFortnights', 'fortnightPeriods', 'activeLoans'));
    }

    // ============ STORE PAYROLL ============
    public function store(Request $request)
    {
        $request->validate([
            'fortnight' => 'required|string',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        $fortnight = $request->fortnight;
        $period = $this->getFortnightPeriod($fortnight);

        $payroll = Payroll::create([
            'company_id' => $companyId,
            'fortnight_number' => $fortnight,
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'pay_date' => $request->pay_date ?? now()->addDays(7),
            'status' => 'Draft',
            'created_by' => auth()->id(),
        ]);

        $employees = Employee::where('company_id', $companyId)
            ->whereIn('id', $request->employee_ids)
            ->whereIn('employee_type', $allowedTypes)
            ->with(['attendanceSummaries' => function($query) use ($fortnight) {
                $query->where('fortnight_number', $fortnight);
            }])
            ->get();

        $totalGross = 0;
        $totalTax = 0;
        $totalNasfundEE = 0;
        $totalNasfundER = 0;
        $totalLoanDeductions = 0;
        $totalNet = 0;

        foreach ($employees as $employee) {
            $payrollItem = $this->calculatePayrollItem($employee, $fortnight);
            $payrollItem['payroll_id'] = $payroll->id;
            $payrollItem['employee_id'] = $employee->id;
            
            PayrollItem::create($payrollItem);

            $totalGross += $payrollItem['gross_wage'];
            $totalTax += $payrollItem['tax'];
            $totalNasfundEE += $payrollItem['nasfund_ee'];
            $totalNasfundER += $payrollItem['nasfund_er'];
            $totalLoanDeductions += $payrollItem['loan_deduction'];
            $totalNet += $payrollItem['net_pay'];
        }

        $payroll->update([
            'total_gross' => $totalGross,
            'total_tax' => $totalTax,
            'total_nasfund_ee' => $totalNasfundEE,
            'total_nasfund_er' => $totalNasfundER,
            'total_loan_deductions' => $totalLoanDeductions,
            'total_net' => $totalNet,
            'total_employees' => $employees->count(),
        ]);

        return redirect()->route('payroll.summary', ['fortnight' => $payroll->fortnight_number])
            ->with('success', 'Payroll created successfully for ' . $employees->count() . ' employees.');
    }

    // ============ CALCULATE PAYROLL ITEM ============
    private function calculatePayrollItem($employee, $fortnight)
    {
        $summary = $employee->attendanceSummaries->first();

        $regularHours = $summary ? $summary->regular_hours : 0;
        $overtimeHours = $summary ? $summary->overtime_hours : 0;
        $sundayHours = $summary ? $summary->sunday_hours : 0;
        $holidayHours = $summary ? $summary->holiday_hours : 0;
        $totalHours = $summary ? $summary->total_hours : 0;

        if ($employee->isExpatriate()) {
            // Expatriates are paid all recorded time at their standard rate.
            // Do not apply overtime, Sunday, or holiday premiums.
            $regularHours = $totalHours;
            $overtimeHours = 0;
            $sundayHours = 0;
            $holidayHours = 0;
        }

        // Keep the saved hourly rate at two decimals for display, but calculate
        // earnings from the exact monthly-salary rate. Rounding the rate first
        // turns K 2,200.00 (84 hours) into K 2,199.96 at K 26.19/hour.
        $hourlyRate = $employee->hourly_rate ?? 0;
        $calculationHourlyRate = $hourlyRate;
        $fortnightHours = (float) ($employee->fortnight_hours ?? 84);

        if ((float) $employee->monthly_salary > 0 && $fortnightHours > 0) {
            $calculationHourlyRate = ((float) $employee->monthly_salary * 12)
                / ($fortnightHours * 26);
        }

        $overtimeRate = $hourlyRate * 1.5;
        
        // Calculate FN RATE / BASIC PAY (unchanged, always the actual basic pay)
        $basicPay = round($regularHours * $calculationHourlyRate, 2);
        $overtimePay = round($overtimeHours * $calculationHourlyRate * 1.5, 2);
        $sundayPay = round($sundayHours * $calculationHourlyRate * 2, 2);
        $holidayPay = round($holidayHours * $calculationHourlyRate * 2, 2);
        $allowance = $employee->allowance ?? 0;
        
        $grossPayBeforeTax = $basicPay + $overtimePay + $sundayPay + $holidayPay + $allowance;
        // Calculate tax on BASIC PAY only
        $tax = 0;
        $regularPay = $basicPay;  // Default: REGULAR = Basic Pay (for National)
        
        if ($employee->employee_type === 'Expatriate') {
            // ✅ Tax on BASIC PAY only
            $tax = $this->calculateExpatriateTax($employee, $basicPay);
            // ✅ REGULAR = Basic Pay + Tax (grossed up for display only)
            $regularPay = $basicPay + $tax;
        } else {
            // ✅ National: Tax on GROSS PAY (all earnings combined)
            $tax = $this->calculateNationalTax($employee, $grossPayBeforeTax);
            // ✅ REGULAR stays as Basic Pay (unchanged)
            $regularPay = $basicPay;
        }
        
        // FN RATE and BASIC PAY remain as $basicPay (unchanged)
        // REGULAR is now $regularPay (which may be grossed up for expats)
        


        $nasfundEE = 0;
        $nasfundER = 0;
        if ($employee->nasfund_number) {
            $nasfundEE = $grossPayBeforeTax * 0.06;
            $nasfundER = $grossPayBeforeTax * 0.084;
        }

        $loanDeduction = $this->calculateLoanDeduction($employee);
        $otherDeductions = 0;

        // Gross Pay uses the REGULAR pay (which may be grossed up for expats)
        $grossPay = $regularPay + $overtimePay + $sundayPay + $holidayPay + $allowance;

        $totalDeductions = $tax + $nasfundEE + $loanDeduction + $otherDeductions;
        $netPay = $grossPay - $totalDeductions;

        return [
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'sunday_hours' => $sundayHours,
            'holiday_hours' => $holidayHours,
            'hours_worked' => $totalHours,
            'hourly_rate' => $hourlyRate,
            'overtime_rate' => $overtimeRate,
            'basic_pay' => $basicPay,
            'regular_pay' => $regularPay,  // ✅ REGULAR: For Expat = Basic + Tax, For National = Basic
            'overtime_pay' => $overtimePay,
            'sunday_pay' => $sundayPay,
            'holiday_pay' => $holidayPay,
            'allowance' => $allowance,
            'gross_wage' => $grossPay,
            'tax' => $tax,
            'nasfund_ee' => $nasfundEE,
            'nasfund_er' => $nasfundER,
            'loan_deduction' => $loanDeduction,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'payment_method' => $employee->payment_method ?? 'Bank Transfer',
            'bank_account' => $employee->getBankAccountDetails()['account_number'] ?? null,
            'details' => [
                'company_name' => $employee->company->name ?? '',
                'employee_type' => $employee->employee_type,
            ]
        ];
    }

    private function calculateNationalTax($employee, $grossPay)
    {
        $taxTable = TaxTable::where('employee_type', 'National')
            ->where('is_active', true)
            ->where('min_amount', '<=', $grossPay)
            ->where(function($query) use ($grossPay) {
                $query->where('max_amount', '>=', $grossPay)
                    ->orWhereNull('max_amount');
            })
            ->orderBy('min_amount', 'desc')
            ->first();

        if (!$taxTable) {
            return 0;
        }

        $threshold = $taxTable->fixed_tax ?? 769.00;
        $taxableAmount = $grossPay - $threshold;
        
        if ($taxableAmount <= 0) {
            return 0;
        }
        
        $tax = $taxableAmount * ($taxTable->tax_rate / 100);
        
        return max(0, round($tax, 2));
    }

        private function calculateExpatriateTax($employee, $basicPay)
    {
        $taxTable = TaxTable::where('employee_type', 'National')
            ->where('is_active', true)
            ->where('min_amount', '<=', $basicPay)
            ->where(function($query) use ($basicPay) {
                $query->where('max_amount', '>=', $basicPay)
                    ->orWhereNull('max_amount');
            })
            ->orderBy('min_amount', 'desc')
            ->first();

        if (!$taxTable) {
            return 0;
        }

        $threshold = $taxTable->fixed_tax ?? 769.00;
        $taxableAmount = $basicPay - $threshold;
        
        if ($taxableAmount <= 0) {
            return 0;
        }
        
        $tax = $taxableAmount * ($taxTable->tax_rate / 100);
        
        return max(0, round($tax, 2));
    }

    /**
     * Calculate tax for a given gross pay
     */
    public function calculateTax(Request $request)
    {
        $request->validate([
            'gross_pay' => 'required|numeric|min:0',
            'employee_type' => 'required|string',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $grossPay = (float) $request->gross_pay;
        $employeeType = $request->employee_type;
        
        // Use your existing tax calculation methods
        if ($employeeType === 'Expatriate') {
            $tax = $this->calculateTaxOnNet($grossPay);
        } else {
            $tax = $this->calculateTax($grossPay);
        }
        
        $nasfund = $grossPay * 0.06;
        
        return response()->json([
            'success' => true,
            'tax' => round($tax, 2),
            'nasfund' => round($nasfund, 2),
        ]);
    }

    /**
     * Calculate tax for Expatriate employees (on NET wages)
     * FIXED: Always uses Universal National/Resident tax tables
     */
    private function calculateTaxOnNet($employee, $netPay)
    {
        $taxTable = TaxTable::where('employee_type', 'National')
            ->where('is_active', true)
            ->where('min_amount', '<=', $netPay)
            ->where(function($query) use ($netPay) {
                $query->where('max_amount', '>=', $netPay)
                    ->orWhereNull('max_amount');
            })
            ->first();

        if (!$taxTable) {
            return 0;
        }

        //FORMULA - same as National but applied to NET
        $threshold = $taxTable->fixed_tax ?? 769.00;
        $taxableAmount = $netPay - $threshold;
        
        if ($taxableAmount <= 0) {
            return 0;
        }
        
        $tax = $taxableAmount * ($taxTable->tax_rate / 100);
        
        return max(0, round($tax, 2));
    }


    // ============ CALCULATE LOAN DEDUCTION ============
    private function calculateLoanDeduction($employee)
    {
        $activeLoans = Loan::where('employee_id', $employee->id)
            ->whereIn('status', ['Approved', 'Released'])
            ->where('remaining_balance', '>', 0)
            ->get();

        $totalDeduction = 0;
        
        foreach ($activeLoans as $loan) {
            $deduction = min($loan->deduction_per_cutoff, $loan->remaining_balance);
            $totalDeduction += $deduction;
            
            $loan->remaining_balance -= $deduction;
            
            if ($loan->remaining_balance <= 0) {
                $loan->status = 'Completed';
            }
            $loan->save();

            $loan->addPayment(
                $deduction,
                null,
                'Auto deduction from payroll'
            );
        }

        return $totalDeduction;
    }

    // ============ SHOW PAYROLL ============
    public function show(Payroll $payroll)
    {
        $payroll->load(['items.employee', 'company']);
        return view('payroll.show', compact('payroll'));
    }

    // ============ PAYROLL SUMMARY ============
public function summary(Request $request)
{
    $user = auth()->user();
    $companyId = $user->getCurrentCompanyId();
    $allowedTypes = $user->getAllowedEmployeeTypes();
    
    $fortnights = Payroll::where('company_id', $companyId)
        ->distinct()
        ->pluck('fortnight_number')
        ->toArray();
    
    $fortnightPeriods = [];
    foreach ($fortnights as $fn) {
        $fortnightPeriods[$fn] = $this->getFortnightPeriod($fn);
    }
    
    $selectedFortnight = $request->fortnight;
    
    if (!$selectedFortnight && count($fortnights) > 0) {
        $selectedFortnight = $fortnights[0];
    }
    
    $payroll = null;
    $payrollItems = collect();
    $period = null;
    $totalEmployees = 0;
    $totalHours = 0;
    $totalOvertimeHours = 0;
    $totalSundayHours = 0;
    $totalHolidayHours = 0;
    $totalGross = 0;
    $totalTax = 0;
    $totalNasfund = 0;
    $totalLoanDeductions = 0;
    $totalNet = 0;
    $totalOvertimePay = 0;
    $totalSundayPay = 0;
    $totalHolidayPay = 0;
    $totalAllowance = 0;
    $totalOtherDeductions = 0;
    $totalBasic = 0;
    $totalRegular = 0;
    
    if ($selectedFortnight) {
        $payroll = Payroll::where('company_id', $companyId)
            ->where('fortnight_number', $selectedFortnight)
            ->first();
        
        if ($payroll) {
            $payrollItems = $payroll->items()->with('employee')->get();
            
            // Add FN Rate to each item
            $payrollItems->each(function ($item) {
                $employee = $item->employee;
                
                if ((float) $employee->monthly_salary > 0) {
                    // Calculate from monthly salary (monthly / 2)
                    $item->fn_rate = round((float) $employee->monthly_salary / 2, 2);
                } else {
                    // Fallback to hourly_rate * 84
                    $item->fn_rate = round((float) $employee->hourly_rate * 84, 2);
                }
            });
            
            $period = [
                'start' => $payroll->period_start,
                'end' => $payroll->period_end,
            ];
            
            $totalEmployees = $payroll->total_employees;
            $totalGross = $payroll->total_gross;
            $totalTax = $payroll->total_tax;
            $totalNasfund = $payroll->total_nasfund_ee;
            $totalLoanDeductions = $payroll->total_loan_deductions ?? 0;
            $totalNet = $payroll->total_net;
            $totalHours = $payrollItems->sum('hours_worked');
            $totalOvertimeHours = $payrollItems->sum('overtime_hours');
            $totalSundayHours = $payrollItems->sum('sunday_hours');
            $totalHolidayHours = $payrollItems->sum('holiday_hours');
            $totalOvertimePay = $payrollItems->sum('overtime_pay');
            $totalSundayPay = $payrollItems->sum('sunday_pay');
            $totalHolidayPay = $payrollItems->sum('holiday_pay');
            $totalAllowance = $payrollItems->sum('allowance');
            $totalOtherDeductions = $payrollItems->sum('other_deductions');
            $totalBasic = $payrollItems->sum('regular_pay');
            $totalRegular = $payrollItems->sum('regular_pay');
        } else {
            $period = $this->getFortnightPeriod($selectedFortnight);
        }
    }
    
    $employees = Employee::where('company_id', $companyId)
        ->where('status', 'Active')
        ->whereIn('employee_type', $allowedTypes)
        ->get();

    //  ADDED THIS - Get active tax tables from database
    $taxTables = TaxTable::where('is_active', true)
        ->orderBy('employee_type')
        ->orderBy('min_amount')
        ->get()
        ->groupBy('employee_type')
        ->map(function ($tables) {
            return $tables->map(function ($table) {
                return [
                    'min' => (float) $table->min_amount,
                    'max' => $table->max_amount ? (float) $table->max_amount : null,
                    'rate' => (float) $table->tax_rate,
                    'fixed' => (float) $table->fixed_tax,
                ];
            })->values()->toArray();
        })
        ->toArray();
    
    return view('payroll.summary', compact(
        'payroll',
        'payrollItems',
        'period',
        'selectedFortnight',
        'fortnights',
        'fortnightPeriods',
        'totalEmployees',
        'totalHours',
        'totalOvertimeHours',
        'totalSundayHours',
        'totalHolidayHours',
        'totalGross',
        'totalTax',
        'totalNasfund',
        'totalLoanDeductions',
        'totalNet',
        'totalOvertimePay',
        'totalSundayPay',
        'totalHolidayPay',
        'totalAllowance',
        'totalOtherDeductions',
        'totalBasic',
        'totalRegular',
        'employees',
        'taxTables' //  ADDED THIS
    ));
}

    /**
     * Bulk update payroll items from the summary page
     */
    public function summaryBulkUpdate(Request $request)
    {
        $request->validate([
            'fortnight' => 'required|string',
            'items' => 'required|array',
            'items.*.regular_pay' => 'nullable|numeric|min:0',
            'items.*.overtime_pay' => 'nullable|numeric|min:0',
            'items.*.sunday_pay' => 'nullable|numeric|min:0',
            'items.*.holiday_pay' => 'nullable|numeric|min:0',
            'items.*.leave_pay' => 'nullable|numeric|min:0',
            'items.*.other_earnings' => 'nullable|numeric|min:0',
            'items.*.gross_wage' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.nasfund_ee' => 'nullable|numeric|min:0',
            'items.*.ncsl' => 'nullable|numeric|min:0',
            'items.*.loan_deduction' => 'nullable|numeric|min:0',
            'items.*.other_deductions' => 'nullable|numeric|min:0',
            'items.*.net_pay' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId();
        $items = $request->items;
        $updatedCount = 0;

        foreach ($items as $itemId => $data) {
            $payrollItem = PayrollItem::find($itemId);
            
            if (!$payrollItem) continue;
            if ($payrollItem->payroll->company_id !== $companyId) continue;
            if ($payrollItem->payroll->status === 'Locked') continue;

            $updateData = [];
            
            // Map the fields - FIXED: other_earnings maps to allowance
            $fieldMap = [
                'regular_pay' => 'regular_pay',
                'overtime_pay' => 'overtime_pay',
                'sunday_pay' => 'sunday_pay',
                'holiday_pay' => 'holiday_pay',
                'leave_pay' => 'leave_pay',
                'other_earnings' => 'allowance', // FIXED: maps to allowance
                'gross_wage' => 'gross_wage',
                'tax' => 'tax',
                'nasfund_ee' => 'nasfund_ee',
                'ncsl' => 'ncsl',
                'loan_deduction' => 'loan_deduction',
                'other_deductions' => 'other_deductions',
                'net_pay' => 'net_pay',
            ];
            
            foreach ($fieldMap as $requestField => $dbField) {
                if (isset($data[$requestField])) {
                    $updateData[$dbField] = round((float)$data[$requestField], 2);
                }
            }

            if (!empty($updateData)) {
                $payrollItem->update($updateData);
                $updatedCount++;
            }
        }

        // Update payroll totals
        if ($updatedCount > 0 && isset($payrollItem)) {
            $this->syncPayrollTotals($payrollItem->payroll);
        }

        return response()->json([
            'success' => true,
            'message' => $updatedCount . ' payroll item(s) updated successfully.'
        ]);
    }

    public function updateAllowance(Request $request, PayrollItem $payrollItem)
    {
        $request->validate([
            'allowance' => 'required|numeric|min:0',
        ]);

        $payrollItem->load(['employee', 'payroll']);

        if ($payrollItem->payroll->company_id !== auth()->user()->getCurrentCompanyId()) {
            abort(403, 'You are not authorized to update this payroll item.');
        }

        $allowance = round((float) $request->allowance, 2);
        $employee = $payrollItem->employee;
        $grossPayBeforeTax =
            (float) $payrollItem->regular_pay +
            (float) $payrollItem->overtime_pay +
            (float) $payrollItem->sunday_pay +
            (float) $payrollItem->holiday_pay +
            $allowance;

        $nasfundEE = $employee->nasfund_number ? round($grossPayBeforeTax * 0.06, 2) : 0;
        $nasfundER = $employee->nasfund_number ? round($grossPayBeforeTax * 0.084, 2) : 0;
        $loanDeduction = (float) ($payrollItem->loan_deduction ?? 0);
        $otherDeductions = (float) ($payrollItem->other_deductions ?? 0);

        if ($employee->employee_type === 'Expatriate') {
            $provisionalNet = $grossPayBeforeTax - $nasfundEE - $loanDeduction - $otherDeductions;
            $tax = $this->calculateTaxOnNet($employee, $provisionalNet);
            $grossPay = $grossPayBeforeTax + $tax;
        } else {
            $tax = $this->calculateNationalTax($employee, $grossPayBeforeTax);
            $grossPay = $grossPayBeforeTax;
        }

        $totalDeductions = $tax + $nasfundEE + $loanDeduction + $otherDeductions;
        $netPay = $grossPay - $totalDeductions;

        $payrollItem->update([
            'allowance' => $allowance,
            'gross_wage' => round($grossPay, 2),
            'tax' => round($tax, 2),
            'nasfund_ee' => $nasfundEE,
            'nasfund_er' => $nasfundER,
            'total_deductions' => round($totalDeductions, 2),
            'net_pay' => round($netPay, 2),
        ]);

        $this->syncPayrollTotals($payrollItem->payroll);

        return redirect()->route('payroll.summary', ['fortnight' => $payrollItem->payroll->fortnight_number])
            ->with('success', 'Allowance updated successfully.');
    }

    private function syncPayrollTotals(Payroll $payroll)
    {
        $items = $payroll->items()->get();

        $payroll->update([
            'total_gross' => $items->sum('gross_wage'),
            'total_tax' => $items->sum('tax'),
            'total_nasfund_ee' => $items->sum('nasfund_ee'),
            'total_nasfund_er' => $items->sum('nasfund_er'),
            'total_loan_deductions' => $items->sum('loan_deduction'),
            'total_deductions' => $items->sum('total_deductions'),
            'total_net' => $items->sum('net_pay'),
            'total_employees' => $items->count(),
        ]);
    }

    // ============ DELETE PAYROLL ============
    public function destroy(Payroll $payroll)
    {
        $payroll->items()->delete();
        $payroll->delete();

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll deleted successfully.');
    }

    // ============ APPROVE PAYROLL ============
    public function approve(Payroll $payroll)
    {
        $payroll->status = 'Approved';
        $payroll->approved_by = auth()->id();
        $payroll->approved_at = now();
        $payroll->save();

        return redirect()->route('payroll.summary', ['fortnight' => $payroll->fortnight_number])
        ->with('success', 'Payroll approved successfully.');
    }

    public function exportABA(Payroll $payroll)
    {
        $company = $payroll->company;
        
        $bankDetails = [
            'bank_name' => $company->bank_name,
            'bsb_number' => $company->bsb_code ?? 'BSP',
            'apca_user_id' => $company->apca_user_id ?? '000001',
            'account_number' => $company->bank_account_number,
            'account_name' => $company->bank_account_name,
            'payment_type' => 'SALARY',
            'payment_date' => now()->format('Y-m-d'),
        ];
        
        try {
            $service = new ABAGeneratorService();
            $batch = $service->generate($payroll, $company, $bankDetails);
            return $service->download($batch->id);
        } catch (\Exception $e) {
            return redirect()->route('payroll.summary', ['fortnight' => $payroll->fortnight_number])
                ->with('error', 'ABA generation failed: ' . $e->getMessage());
        }
    }
    
    // ============ HELPER METHODS ============
    public function getFortnightPeriod($fortnight)
    {
        $year = (int)substr($fortnight, 0, 2);
        $week = (int)substr($fortnight, 2);
        $fullYear = 2000 + $year;
        $start = Carbon::createFromDate($fullYear - 1, 12, 25)->addDays(($week - 1) * 14);
        $end = $start->copy()->addDays(13);
        return ['start' => $start, 'end' => $end];
    }
    
    public function getCurrentFortnight()
    {
        $year = date('y');
        $start = Carbon::createFromDate(date('Y') - 1, 12, 25);
        $daysSinceStart = $start->diffInDays(now()) + 1;
        $fortnight = ceil($daysSinceStart / 14);
        return $year . str_pad($fortnight, 2, '0', STR_PAD_LEFT);
    }

    private function getAllFortnights()
    {
        $year = date('y');
        $fortnights = [];
        for ($i = 1; $i <= 26; $i++) {
            $fortnights[] = $year . str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        return $fortnights;
    }
}

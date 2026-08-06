<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\AttendanceSummary;
use App\Models\Loan;
use App\Models\TaxTable;
use App\Exports\PayrollSummaryExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\ABAGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;


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
            ->whereHas('attendanceSummaries', function ($query) use ($fortnight) {
                $query->where('fortnight_number', $fortnight)
                    ->where('total_hours', '>', 0);
            })
            ->with(['attendanceSummaries' => function($query) use ($fortnight) {
                $query->where('fortnight_number', $fortnight);
            }])
            ->get();

        $activeLoans = Loan::where('company_id', $companyId)
            ->where('status', 'Released')
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

        $employees = Employee::where('company_id', $companyId)
            ->whereIn('id', $request->employee_ids)
            ->active()
            ->whereIn('employee_type', $allowedTypes)
            ->whereHas('attendanceSummaries', function ($query) use ($fortnight) {
                $query->where('fortnight_number', $fortnight)
                    ->where('total_hours', '>', 0);
            })
            ->with(['attendanceSummaries' => function($query) use ($fortnight) {
                $query->where('fortnight_number', $fortnight);
            }])
            ->get();

        if ($employees->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['employee_ids' => 'No selected employees have attendance for this fortnight.']);
        }

        $payroll = Payroll::create([
            'company_id' => $companyId,
            'fortnight_number' => $fortnight,
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'pay_date' => $request->pay_date ?? now()->addDays(7),
            'status' => 'Draft',
            'created_by' => auth()->id(),
        ]);

        $totalGross = 0;
        $totalTax = 0;
        $totalNasfundEE = 0;
        $totalNasfundER = 0;
        $totalLoanDeductions = 0;
        $totalNet = 0;

        foreach ($employees as $employee) {
            $payrollItem = $this->calculatePayrollItem($employee, $fortnight, $payroll->id);
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
    private function calculatePayrollItem($employee, $fortnight, $payrollId = null)
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
        $fortnightHours = (float) $employee->regular_hours_limit;

        if ((float) $employee->monthly_salary > 0 && $fortnightHours > 0) {
            $calculationHourlyRate = ((float) $employee->monthly_salary * 12)
                / ($fortnightHours * 26);
        }

        $overtimeRate = $hourlyRate * 1.5;
        
        // Calculate FN RATE / BASIC PAY (unchanged, always the actual basic pay)
        $basicPay = round($regularHours * $calculationHourlyRate, 2);
        $overtimePay = round($overtimeHours * $calculationHourlyRate * 1.5, 2);
        $sundayPay = round($sundayHours * $calculationHourlyRate * 2, 2);
        // Holiday credits are paid at the employee's standard hourly rate.
        $holidayPay = round($holidayHours * $calculationHourlyRate, 2);
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

        $loanDeduction = $this->calculateLoanDeduction($employee, $payrollId);
        $otherDeductions = 0;

        // Gross Pay uses the REGULAR pay (which may be grossed up for expats)
        $grossPay = $regularPay + $overtimePay + $sundayPay + $holidayPay + $allowance;

        $totalDeductions = $tax + $nasfundEE + $loanDeduction + $otherDeductions;
        $netPay = $grossPay - $totalDeductions;

        $amounts = $this->calculatePayrollAmounts($employee, [
            'basic_pay' => $basicPay,
            'overtime_pay' => $overtimePay,
            'sunday_pay' => $sundayPay,
            'holiday_pay' => $holidayPay,
            'allowance' => $allowance,
            'loan_deduction' => $loanDeduction,
        ]);
        $regularPay = $amounts['regular_pay'];
        $grossPay = $amounts['gross_wage'];
        $tax = $amounts['tax'];
        $nasfundEE = $amounts['nasfund_ee'];
        $nasfundER = $amounts['nasfund_er'];
        $otherDeductions = $amounts['other_deductions'];
        $totalDeductions = $amounts['total_deductions'];
        $netPay = $amounts['net_pay'];

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

    /**
     * Calculate payroll amounts from the employee's earnings and deductions.
     * This is the single source of truth used when a payroll is created and
     * when its summary is edited.
     */
    private function calculatePayrollAmounts(Employee $employee, array $values): array
    {
        $basicPay = (float) ($values['basic_pay'] ?? 0);
        $overtimePay = (float) ($values['overtime_pay'] ?? 0);
        $sundayPay = (float) ($values['sunday_pay'] ?? 0);
        $holidayPay = (float) ($values['holiday_pay'] ?? 0);
        $leavePay = (float) ($values['leave_pay'] ?? 0);
        $allowance = (float) ($values['allowance'] ?? 0);
        $loanDeduction = (float) ($values['loan_deduction'] ?? 0);
        $otherDeductions = (float) ($values['other_deductions'] ?? 0);
        $ncsl = (float) ($values['ncsl'] ?? 0);

        $earnings = $basicPay + $overtimePay + $sundayPay + $holidayPay + $leavePay + $allowance;
        $nasfundEE = $employee->nasfund_number ? round($earnings * 0.06, 2) : 0;
        $nasfundER = $employee->nasfund_number ? round($earnings * 0.084, 2) : 0;
        $preTaxDeductions = $nasfundEE + $ncsl + $loanDeduction + $otherDeductions;

        if ($employee->employee_type === 'Expatriate') {
            // Expatriate tax is employer-funded: calculate it from the
            // intended net wage, then add it back to gross pay.
            $tax = $this->calculateTaxOnNet($employee, $earnings - $preTaxDeductions);
            $regularPay = $basicPay + $tax;
            $grossWage = $earnings + $tax;
        } else {
            // National employees are taxed on all taxable earnings.
            $tax = $this->calculateNationalTax($employee, $earnings);
            $regularPay = $basicPay;
            $grossWage = $earnings;
        }

        $totalDeductions = $tax + $preTaxDeductions;

        return [
            'regular_pay' => round($regularPay, 2),
            'gross_wage' => round($grossWage, 2),
            'tax' => round($tax, 2),
            'nasfund_ee' => $nasfundEE,
            'nasfund_er' => $nasfundER,
            'other_deductions' => round($otherDeductions, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_pay' => round($grossWage - $totalDeductions, 2),
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
            $tax = $this->calculateTaxOnNet(null, $grossPay);
        } else {
            $tax = $this->calculateNationalTax(null, $grossPay);
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
    private function calculateLoanDeduction($employee, $payrollId = null)
    {
        $activeLoans = Loan::where('employee_id', $employee->id)
            // Approval authorizes the loan; release is the point at which it
            // becomes payable and eligible for a payroll deduction.
            ->where('status', 'Released')
            ->where('remaining_balance', '>', 0)
            ->get();

        $totalDeduction = 0;
        
        foreach ($activeLoans as $loan) {
            $deduction = min($loan->deduction_per_cutoff, $loan->remaining_balance);
            $totalDeduction += $deduction;
            
            $loan->addPayment(
                $deduction,
                $payrollId,
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
            // Manual ABA entries are payment instructions, not employee payroll
            // rows, so keep them out of the employee-facing payroll summary.
            $payrollItems = $payroll->items()
                ->with('employee.company')
                ->get()
                ->reject(function ($item) {
                    $details = $item->details ?? [];

                    return is_array($details)
                        && ($details['type'] ?? null) === 'manual_entry';
                })
                ->filter(function ($item) {
                    return (float) $item->hours_worked > 0;
                })
                ->values();
            
            // Add FN Rate to each item
            $payrollItems->each(function ($item) {
                $employee = $item->employee;
                
                // ✅ Check if employee exists (manual entries have null employee_id)
                if ($employee) {
                    if ((float) $employee->monthly_salary > 0) {
                        // Calculate from monthly salary (monthly / 2)
                        $item->fn_rate = round((float) $employee->monthly_salary / 2, 2);
                    } else {
                        // Fallback to hourly_rate * the employee's regular-hours
                        // limit (84, or 144 for e.g. YellowJacket Security)
                        $item->fn_rate = round((float) $employee->hourly_rate * $employee->regular_hours_limit, 2);
                    }
                } else {
                    // ✅ Manual entry - set fn_rate to 0 or net_pay
                    $item->fn_rate = round((float) $item->net_pay, 2);
                }
            });
            
            $period = [
                'start' => $payroll->period_start,
                'end' => $payroll->period_end,
            ];
            
            $totalEmployees = $payrollItems->count();
            $totalGross = $payrollItems->sum('gross_wage');
            $totalTax = $payrollItems->sum('tax');
            $totalNasfund = $payrollItems->sum('nasfund_ee');
            $totalLoanDeductions = $payrollItems->sum('loan_deduction');
            $totalNet = $payrollItems->sum('net_pay');
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
            'items' => 'required|array|min:1',
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
            
            // Check if the payroll belongs to the company
            if ($payrollItem->payroll->company_id !== $companyId) continue;
            
            // Don't allow updates to locked or approved payrolls
            if (in_array($payrollItem->payroll->status, ['Locked', 'Approved', 'Paid'])) continue;

            $payrollItem->load('employee');
            if (!$payrollItem->employee) continue;

            // Gross, tax, NASFUND, and net pay are calculated fields. Never
            // trust browser-submitted values for them.
            $earningsAndDeductions = [
                'basic_pay' => $payrollItem->basic_pay,
                'overtime_pay' => $data['overtime_pay'] ?? $payrollItem->overtime_pay,
                'sunday_pay' => $data['sunday_pay'] ?? $payrollItem->sunday_pay,
                'holiday_pay' => $data['holiday_pay'] ?? $payrollItem->holiday_pay,
                'leave_pay' => $data['leave_pay'] ?? $payrollItem->leave_pay,
                'allowance' => $data['other_earnings'] ?? $payrollItem->allowance,
                'ncsl' => $data['ncsl'] ?? $payrollItem->ncsl,
                'loan_deduction' => $data['loan_deduction'] ?? $payrollItem->loan_deduction,
                'other_deductions' => $data['other_deductions'] ?? $payrollItem->other_deductions,
            ];
            $amounts = $this->calculatePayrollAmounts($payrollItem->employee, $earningsAndDeductions);

            $payrollItem->update(array_merge($earningsAndDeductions, $amounts));
            $updatedCount++;
        }

        // Update payroll totals if any items were updated
        if ($updatedCount > 0 && isset($payrollItem)) {
            $payroll = $payrollItem->payroll;
            $this->syncPayrollTotals($payroll);
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
        $earningsAndDeductions = [
            'basic_pay' => $payrollItem->basic_pay,
            'overtime_pay' => $payrollItem->overtime_pay,
            'sunday_pay' => $payrollItem->sunday_pay,
            'holiday_pay' => $payrollItem->holiday_pay,
            'leave_pay' => $payrollItem->leave_pay,
            'allowance' => $allowance,
            'ncsl' => $payrollItem->ncsl,
            'loan_deduction' => $payrollItem->loan_deduction,
            'other_deductions' => $payrollItem->other_deductions,
        ];
        $amounts = $this->calculatePayrollAmounts($employee, $earningsAndDeductions);

        $payrollItem->update(array_merge($earningsAndDeductions, $amounts));

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
public function printPayslips($payrollId, $type)
{
    $payroll = Payroll::with(['items.employee.bankAccounts', 'company'])->findOrFail($payrollId);

    $employeeType = $type === 'national' ? 'National' : 'Expatriate';

    $payrollItems = $payroll->items()
        ->with('employee.bankAccounts')
        ->whereNotNull('employee_id')
        ->whereHas('employee', function ($q) use ($employeeType) {
            $q->where('employee_type', $employeeType);
        })
        ->get();
    
    // Set the logo
    $company = $payroll->company;
    if ($company) {
        $logoPath = null;
        
        // 1. Check database path first
        if (!empty($company->logo_path)) {
            $path = public_path($company->logo_path);
            if (file_exists($path)) {
                $logoPath = $path;
            }
        }
        
        // 2. Try company name variations
        if (!$logoPath && $company->name) {
            $companyName = strtolower(str_replace(' ', '-', trim($company->name)));
            $possiblePaths = [
                public_path('images/' . $companyName . '.jpg'),
                public_path('images/' . $companyName . '.png'),
                public_path('images/' . $companyName . '.jpeg'),
            ];
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $logoPath = $path;
                    break;
                }
            }
        }

        // 3. Fallback: Search for default logo files in public/images/
        if (!$logoPath) {
            $defaultPaths = [
                public_path('images/logo.jpg'),
                public_path('images/logo.png'),
                public_path('images/logo.jpeg'),
                public_path('images/logo.JPG'),
                public_path('images/logo.PNG'),
            ];
            foreach ($defaultPaths as $path) {
                if (file_exists($path)) {
                    $logoPath = $path;
                    break;
                }
            }
        }
        
        // Convert to base64 for DomPDF
        if ($logoPath && file_exists($logoPath)) {
            $extension = pathinfo($logoPath, PATHINFO_EXTENSION);
            // Handle jpg vs jpeg extension for MIME type
            $mimeType = strtolower($extension) === 'jpg' ? 'jpeg' : strtolower($extension);
            $imageData = base64_encode(file_get_contents($logoPath));
            $company->logo_data = 'data:image/' . $mimeType . ';base64,' . $imageData;
        } else {
            $company->logo_data = null;
        }
    }
    
    $payrollItems->each(function ($item) {
        $item->total_deductions = ($item->tax ?? 0) + 
                                  ($item->nasfund_ee ?? 0) + 
                                  ($item->loan_deduction ?? 0) + 
                                  ($item->other_deductions ?? 0);
    });
    
    $data = [
        'payroll' => $payroll,
        'payrollItems' => $payrollItems,
        'company' => $company,
        'fortnight' => $payroll->fortnight_number,
        'period_start' => $payroll->period_start,
        'period_end' => $payroll->period_end,
        'generated_date' => now(),
        'employee_type_label' => $employeeType,
    ];
    
    $pdf = Pdf::loadView('payroll.print-payslips', $data);
    $pdf->setPaper('A4', 'landscape');
    
    $pdf->setOptions([
        'defaultFont' => 'Arial',
        'isRemoteEnabled' => true,
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled' => true,
        'isFontSubsettingEnabled' => true,
        'margin_top' => 5,
        'margin_bottom' => 5,
        'margin_left' => 5,
        'margin_right' => 5,
    ]);
    
    return $pdf->download('payslips_FN' . $payroll->fortnight_number . '_' . $employeeType . '.pdf');
}
/**
 * Print signing sheet for all employees in a payroll
 */
/**
 * Print signing sheet for cash employees only
 */
public function printSigning($payrollId)
{
    $payroll = Payroll::with(['items.employee', 'company'])->findOrFail($payrollId);
    
    // Get ONLY cash employees
    $payrollItems = $payroll->items()
        ->with('employee')
        ->where('payment_method', 'Cash')
        ->orWhere('payment_method', 'Cash Payment')
        ->get();
    
    // Get company details
    $company = $payroll->company;
    
    $data = [
        'payroll' => $payroll,
        'payrollItems' => $payrollItems,
        'company' => $company,
        'fortnight' => $payroll->fortnight_number,
        'period_start' => $payroll->period_start,
        'period_end' => $payroll->period_end,
        'generated_date' => now(),
        'total_cash_employees' => $payrollItems->count(),
        'total_cash_payout' => $payrollItems->sum('net_pay'),
    ];
    
    $pdf = Pdf::loadView('payroll.print-signing', $data);
    $pdf->setPaper('A4', 'landscape');
    
    // ✅ ADD THESE OPTIONS
    $pdf->setOptions([
        'defaultFont' => 'Courier',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'isPhpEnabled' => true,
        'isFontSubsettingEnabled' => true,
    ]);
    
    return $pdf->download('for-signing_cash_' . $payroll->fortnight_number . '.pdf');
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

    // ============ UPDATE STATUS ============
    public function updateStatus(Request $request, Payroll $payroll)
    {
        $request->validate([
            'status' => 'required|in:Draft,Approved,Paid,Locked',
        ]);

        $payroll->status = $request->status;

        if ($request->status === 'Approved' && !$payroll->approved_by) {
            $payroll->approved_by = auth()->id();
            $payroll->approved_at = now();
        }

        $payroll->save();

        return redirect()->route('payroll.index')
            ->with('success', 'Payroll FN' . $payroll->fortnight_number . ' status changed to ' . $request->status . '.');
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
    
    public function exportExcel($payrollId)
    {
        $payroll = Payroll::findOrFail($payrollId);
        
        // Check authorization
        if ($payroll->company_id !== auth()->user()->getCurrentCompanyId()) {
            abort(403, 'You are not authorized to export this payroll.');
        }
        
        return Excel::download(
            new PayrollSummaryExport($payrollId), 
            'payroll_FN' . $payroll->fortnight_number . '.xlsx'
        );
    }

        public function destroy(Payroll $payroll)
    {
        $user = auth()->user();
        $companyId = $user->getCurrentCompanyId();
        
        // Check if the payroll belongs to the current company
        if ($payroll->company_id !== $companyId) {
            abort(403, 'You are not authorized to delete this payroll.');
        }
        
        // Check if payroll can be deleted (only Draft status)
        if ($payroll->status !== 'Draft') {
            return redirect()->route('payroll.index')
                ->with('error', 'Only draft payrolls can be deleted.');
        }
        
        // Delete all payroll items first (cascade will handle this if set)
        $payroll->items()->delete();
        
        // Delete the payroll
        $payroll->delete();
        
        return redirect()->route('payroll.index')
            ->with('success', 'Payroll deleted successfully.');
    }
}

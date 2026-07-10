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

class PayrollController extends Controller
{
    // ============ LIST PAYROLLS ============
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $payrolls = Payroll::where('company_id', $companyId)
            ->orderBy('period_start', 'desc')
            ->paginate(20);

        $fortnights = Payroll::where('company_id', $companyId)
            ->distinct()
            ->pluck('fortnight_number')
            ->toArray();
        
        // BUILD FORTNIGHT PERIODS
        $fortnightPeriods = [];
        foreach ($fortnights as $fn) {
            $fortnightPeriods[$fn] = $this->getFortnightPeriod($fn);
        }

        return view('payroll.index', compact('payrolls', 'fortnights', 'fortnightPeriods'));
    }

    // ============ CREATE PAYROLL ============
    public function create(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $fortnight = $request->fortnight ?? $this->getCurrentFortnight();
        $period = $this->getFortnightPeriod($fortnight);

        $allFortnights = $this->getAllFortnights();
        
        $fortnightPeriods = [];
        foreach ($allFortnights as $fn) {
            $fortnightPeriods[$fn] = $this->getFortnightPeriod($fn);
        }

        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->with(['attendanceSummaries' => function($query) use ($fortnight) {
                $query->where('fortnight_number', $fortnight);
            }])
            ->get();

        // Get active loans for each employee (for display in create view)
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

        $companyId = auth()->user()->company_id;
        $fortnight = $request->fortnight;
        $period = $this->getFortnightPeriod($fortnight);

        // Create payroll
        $payroll = Payroll::create([
            'company_id' => $companyId,
            'fortnight_number' => $fortnight,
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'pay_date' => $request->pay_date ?? now()->addDays(7),
            'status' => 'Draft',
            'created_by' => auth()->id(),
        ]);

        // Get ONLY the selected employees
        $employees = Employee::where('company_id', $companyId)
            ->whereIn('id', $request->employee_ids)
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

        return redirect()->route('payroll.show', $payroll)
            ->with('success', 'Payroll created successfully for ' . $employees->count() . ' employees.');
    }

    // ============ CALCULATE PAYROLL ITEM ============
    private function calculatePayrollItem($employee, $fortnight)
    {
        $summary = $employee->attendanceSummaries->first();

        // ✅ Get hours from summary
        $regularHours = $summary ? $summary->regular_hours : 0;
        $overtimeHours = $summary ? $summary->overtime_hours : 0;
        $sundayHours = $summary ? $summary->sunday_hours : 0;
        $holidayHours = $summary ? $summary->holiday_hours : 0;
        $totalHours = $summary ? $summary->total_hours : 0;

        // ✅ Hourly rates
        $hourlyRate = $employee->hourly_rate ?? 0;
        $overtimeRate = $hourlyRate * 1.5;
        $sundayRate = $hourlyRate * 2;
        $holidayRate = $hourlyRate * 2;

        // ✅ Calculate pay components
        $regularPay = $regularHours * $hourlyRate;
        $overtimePay = $overtimeHours * $overtimeRate;
        $sundayPay = $sundayHours * $sundayRate;
        $holidayPay = $holidayHours * $holidayRate;
        $allowance = $employee->allowance ?? 0;
        
        // ✅ Initial gross before tax (for Expatriate calculation)
        $grossPayBeforeTax = $regularPay + $overtimePay + $sundayPay + $holidayPay + $allowance;

        // ✅ NASFUND - only if employee has NASFUND number (calculated on gross before tax)
        $nasfundEE = 0;
        $nasfundER = 0;
        if ($employee->nasfund_number) {
            $nasfundEE = $grossPayBeforeTax * 0.06;   // 6% Employee
            $nasfundER = $grossPayBeforeTax * 0.084;  // 8.4% Employer
        }

        // ✅ Loan deduction
        $loanDeduction = $this->calculateLoanDeduction($employee);
        $otherDeductions = 0;

        // ============================================================
        // ✅ TAX CALCULATION - SOW Compliant
        // National: Tax calculated directly from wages
        // Expatriate: Tax calculated from net wages and added to gross wage
        // ============================================================
        $tax = 0;
        $grossPay = $grossPayBeforeTax;

        if ($employee->employee_type === 'Expatriate') {
            // Expatriate: Tax calculated on NET wages
            // 1. Calculate provisional net (gross - other deductions)
            $provisionalNet = $grossPayBeforeTax - $nasfundEE - $loanDeduction - $otherDeductions;
            
            // 2. Calculate tax on net wages
            $tax = $this->calculateTaxOnNet($employee, $provisionalNet);
            
            // 3. Add tax back to gross (as required by SOW)
            $grossPay = $grossPayBeforeTax + $tax;
        } else {
            // National: Tax calculated directly from gross wages
            $tax = $this->calculateTax($employee, $grossPayBeforeTax);
            $grossPay = $grossPayBeforeTax;
        }

        // ✅ Total deductions and net pay
        $totalDeductions = $tax + $nasfundEE + $loanDeduction + $otherDeductions;
        $netPay = $grossPay - $totalDeductions;

        return [
            // ✅ Hours
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'sunday_hours' => $sundayHours,
            'holiday_hours' => $holidayHours,
            'hours_worked' => $totalHours,

            // ✅ Rates
            'hourly_rate' => $hourlyRate,
            'overtime_rate' => $overtimeRate,

            // ✅ Pay components
            'regular_pay' => $regularPay,
            'overtime_pay' => $overtimePay,
            'sunday_pay' => $sundayPay,
            'holiday_pay' => $holidayPay,
            'allowance' => $allowance,
            'gross_wage' => $grossPay,

            // ✅ Deductions
            'tax' => $tax,
            'nasfund_ee' => $nasfundEE,
            'nasfund_er' => $nasfundER,
            'loan_deduction' => $loanDeduction,
            'other_deductions' => $otherDeductions,
            'total_deductions' => $totalDeductions,

            // ✅ Net
            'net_pay' => $netPay,

            // ✅ Payment
            'payment_method' => $employee->payment_method ?? 'Bank Transfer',
            'bank_account' => $employee->getBankAccountDetails()['account_number'] ?? null,

            // ✅ Details
            'details' => [
                'company_name' => $employee->company->name ?? '',
                'employee_type' => $employee->employee_type,
            ]
        ];
    }

    /**
     * Calculate tax for National employees (directly from gross wages)
     */
    private function calculateTax($employee, $grossPay)
    {
        $taxTable = TaxTable::where('company_id', $employee->company_id)
            ->where('employee_type', $employee->employee_type)
            ->where('is_active', true)
            ->where('min_amount', '<=', $grossPay)
            ->where(function($query) use ($grossPay) {
                $query->where('max_amount', '>=', $grossPay)
                    ->orWhereNull('max_amount');
            })
            ->first();

        if (!$taxTable) {
            return 0;
        }

        // Tax = (Income × Rate) - Offset
        $tax = ($grossPay * $taxTable->tax_rate / 100) - $taxTable->fixed_tax;

        return max(0, $tax);
    }

    /**
     * Calculate tax for Expatriate employees (on NET wages)
     * SOW: Expatriate Employee Tax calculated from net wages and added to gross wage
     */
    private function calculateTaxOnNet($employee, $netPay)
    {
        $taxTable = TaxTable::where('company_id', $employee->company_id)
            ->where('employee_type', $employee->employee_type)
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

        // Tax = (Net × Rate) - Offset
        $tax = ($netPay * $taxTable->tax_rate / 100) - $taxTable->fixed_tax;

        return max(0, $tax);
    }

    // ============ CALCULATE LOAN DEDUCTION ============
    private function calculateLoanDeduction($employee)
    {
        // Use remaining_balance instead of balance, and check for Approved/Released status
        $activeLoans = Loan::where('employee_id', $employee->id)
            ->whereIn('status', ['Approved', 'Released'])
            ->where('remaining_balance', '>', 0)
            ->get();

        $totalDeduction = 0;
        
        foreach ($activeLoans as $loan) {
            // Use remaining_balance and deduction_per_cutoff
            $deduction = min($loan->deduction_per_cutoff, $loan->remaining_balance);
            $totalDeduction += $deduction;
            
            // Update the loan balance using remaining_balance
            $loan->remaining_balance -= $deduction;
            
            if ($loan->remaining_balance <= 0) {
                $loan->status = 'Completed';
            }
            $loan->save();

            // Create a loan payment record
            $loan->addPayment(
                $deduction,
                null, // payroll_id will be linked later
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
        $companyId = auth()->user()->company_id;
        
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
        
        if ($selectedFortnight) {
            $payroll = Payroll::where('company_id', $companyId)
                ->where('fortnight_number', $selectedFortnight)
                ->first();
            
            if ($payroll) {
                $payrollItems = $payroll->items()->with('employee')->get();
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
            } else {
                $period = $this->getFortnightPeriod($selectedFortnight);
            }
        }
        
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->get();
        
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
            'employees'
        ));
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

        return redirect()->route('payroll.show', $payroll)
            ->with('success', 'Payroll approved successfully.');
    }

    // ============ EXPORT ABA ============
    public function exportABA(Payroll $payroll)
    {
        // Get bank transfer payments
        $bankPayments = $payroll->items()
            ->where('payment_method', 'Bank Transfer')
            ->with('employee')
            ->get();

        if ($bankPayments->isEmpty()) {
            return redirect()->route('payroll.show', $payroll)
                ->with('info', 'No bank transfer payments to export.');
        }

        $filename = "aba_export_{$payroll->id}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($bankPayments) {
            $handle = fopen('php://output', 'w');

            // Headers
            fputcsv($handle, ['Account Name', 'Account Number', 'Bank Name', 'BSB Code', 'Amount', 'Reference']);

            // Data rows
            foreach ($bankPayments as $item) {
                $employee = $item->employee;
                $bankAccount = $employee->getBankAccountDetails();

                fputcsv($handle, [
                    $bankAccount['account_name'] ?? $employee->full_name,
                    $bankAccount['account_number'] ?? '',
                    $bankAccount['bank_name'] ?? '',
                    $bankAccount['bsb_code'] ?? '',
                    number_format($item->net_pay, 2),
                    $employee->employee_number,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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
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

        return redirect()->route('payroll.show', $payroll)
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

        $hourlyRate = $employee->hourly_rate ?? 0;
        $overtimeRate = $hourlyRate * 1.5;
        $sundayRate = $hourlyRate * 2;
        $holidayRate = $hourlyRate * 2;

        $regularPay = $regularHours * $hourlyRate;
        $overtimePay = $overtimeHours * $overtimeRate;
        $sundayPay = $sundayHours * $sundayRate;
        $holidayPay = $holidayHours * $holidayRate;
        $allowance = $employee->allowance ?? 0;
        
        $grossPayBeforeTax = $regularPay + $overtimePay + $sundayPay + $holidayPay + $allowance;

        $nasfundEE = 0;
        $nasfundER = 0;
        if ($employee->nasfund_number) {
            $nasfundEE = $grossPayBeforeTax * 0.06;
            $nasfundER = $grossPayBeforeTax * 0.084;
        }

        $loanDeduction = $this->calculateLoanDeduction($employee);
        $otherDeductions = 0;

        $tax = 0;
        $grossPay = $grossPayBeforeTax;

        if ($employee->employee_type === 'Expatriate') {
            $provisionalNet = $grossPayBeforeTax - $nasfundEE - $loanDeduction - $otherDeductions;
            $tax = $this->calculateTaxOnNet($employee, $provisionalNet);
            $grossPay = $grossPayBeforeTax + $tax;
        } else {
            $tax = $this->calculateTax($employee, $grossPayBeforeTax);
            $grossPay = $grossPayBeforeTax;
        }

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
            'regular_pay' => $regularPay,
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
     * Calculate tax for ALL employees using Resident/National rates
     * ✅ FIXED: Always uses National tax tables regardless of employee type
     */
    private function calculateTax($employee, $grossPay)
    {
        // ✅ ALWAYS use National/Resident tax tables for ALL employees
        $taxTable = TaxTable::where('company_id', $employee->company_id)
            ->where('employee_type', 'National')
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

        $tax = ($grossPay * $taxTable->tax_rate / 100) - $taxTable->fixed_tax;
        return max(0, $tax);
    }

    /**
     * Calculate tax for Expatriate employees (on NET wages)
     * ✅ FIXED: Always uses National/Resident tax tables for ALL employees
     */
    private function calculateTaxOnNet($employee, $netPay)
    {
        // ✅ ALWAYS use National/Resident tax tables for ALL employees
        $taxTable = TaxTable::where('company_id', $employee->company_id)
            ->where('employee_type', 'National')
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

        $tax = ($netPay * $taxTable->tax_rate / 100) - $taxTable->fixed_tax;
        return max(0, $tax);
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
            ->whereIn('employee_type', $allowedTypes)
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
            return redirect()->route('payroll.show', $payroll)
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

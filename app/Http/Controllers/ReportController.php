<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\NasfundContribution;
use App\Models\LeaveRecord;
use App\Models\PayIncreaseHistory;
use App\Models\DisciplineRecord;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NasfundExport;
use App\Exports\SwtExport;
use App\Exports\EarningsExport;
use App\Exports\ProfileExport;

class ReportController extends Controller
{
    /**
     * NASFUND Report Index
     */
    public function nasfundIndex(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        
        // Get available fortnights
        $fortnights = Payroll::where('company_id', $companyId)
            ->distinct()
            ->orderBy('fortnight_number', 'desc')
            ->pluck('fortnight_number')
            ->toArray();
        
        // Build fortnight periods
        $fortnightPeriods = [];
        foreach ($fortnights as $fn) {
            $fortnightPeriods[$fn] = $this->getFortnightPeriod($fn);
        }
        
        $selectedFortnight = $request->fortnight ?? ($fortnights[0] ?? null);
        $reportData = [];
        $summary = [];
        
        if ($selectedFortnight) {
            $reportData = $this->getNasfundData($companyId, $selectedFortnight);
            $summary = $this->calculateNasfundSummary($reportData);
        }
        
        return view('reports.nasfund.index', compact(
            'company',
            'fortnights',
            'fortnightPeriods',
            'selectedFortnight',
            'reportData',
            'summary'
        ));
    }
    
    /**
     * Export NASFUND Report
     */
    public function exportNasfund(Request $request)
    {
        $request->validate([
            'fortnight' => 'required|string',
            'format' => 'required|in:pdf,excel,csv'
        ]);
        
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        $fortnight = $request->fortnight;
        $format = $request->format;
        
        $reportData = $this->getNasfundData($companyId, $fortnight);
        $summary = $this->calculateNasfundSummary($reportData);
        $period = $this->getFortnightPeriod($fortnight);
        
        $filename = "NASFUND_{$company->code}_{$fortnight}_" . date('Ymd');
        
        switch ($format) {
            case 'pdf':
                return $this->exportNasfundPDF($company, $reportData, $summary, $period, $fortnight, $filename);
            case 'excel':
                return $this->exportNasfundExcel($company, $reportData, $summary, $period, $fortnight, $filename);
            case 'csv':
                return $this->exportNasfundCSV($company, $reportData, $summary, $period, $fortnight, $filename);
            default:
                return back()->with('error', 'Invalid export format.');
        }
    }
    
/**
 * Get NASFUND data with user type filter
 */
private function getNasfundData($companyId, $fortnight)
{
    // Get payroll for the fortnight
    $payroll = Payroll::where('company_id', $companyId)
        ->where('fortnight_number', $fortnight)
        ->first();
    
    if (!$payroll) {
        return collect(); // ✅ Return empty collection, NOT []
    }
    
    // Get the current user and their allowed employee types
    $user = auth()->user();
    $allowedTypes = $user->getAllowedEmployeeTypes();
    
    // Get payroll items with employee data
    $items = PayrollItem::where('payroll_id', $payroll->id)
        ->with(['employee'])
        ->get();
    
    // ✅ Always return a Collection, not an array
    $result = collect();
    
    foreach ($items as $item) {
        // Only include employees with NASFUND number AND allowed employee type
        if ($item->employee 
            && $item->employee->nasfund_number
            && in_array($item->employee->employee_type, $allowedTypes)) {
            
            $result->push((object) [
                'employee_number' => $item->employee->employee_number,
                'full_name' => $item->employee->full_name,
                'nasfund_number' => $item->employee->nasfund_number,
                'gross_wage' => $item->gross_wage,
                'ee_contribution' => $item->nasfund_ee,
                'er_contribution' => $item->nasfund_er,
                'total_contribution' => $item->nasfund_ee + $item->nasfund_er,
                'employee_type' => $item->employee->employee_type,
            ]);
        }
    }
    
    return $result; // ✅ Always returns a Collection
}
    
    /**
     * Calculate NASFUND summary
     */
    private function calculateNasfundSummary($reportData)
    {
        return (object) [
            'total_employees' => $reportData->count(),
            'total_gross' => $reportData->sum('gross_wage'),
            'total_ee' => $reportData->sum('ee_contribution'),
            'total_er' => $reportData->sum('er_contribution'),
            'total_contributions' => $reportData->sum('total_contribution'),
        ];
    }
    
    /**
     * Export NASFUND PDF
     */
    private function exportNasfundPDF($company, $reportData, $summary, $period, $fortnight, $filename)
    {
        $pdf = Pdf::loadView('reports.nasfund.pdf', compact(
            'company',
            'reportData',
            'summary',
            'period',
            'fortnight'
        ));
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Export NASFUND Excel
     */
    private function exportNasfundExcel($company, $reportData, $summary, $period, $fortnight, $filename)
    {
        return Excel::download(
            new NasfundExport($company, $reportData, $summary, $period, $fortnight),
            $filename . '.xlsx'
        );
    }
    
    /**
     * Export NASFUND CSV
     */
    private function exportNasfundCSV($company, $reportData, $summary, $period, $fortnight, $filename)
    {
        return Excel::download(
            new NasfundExport($company, $reportData, $summary, $period, $fortnight),
            $filename . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }
    
    /**
     * ============================================================
     * SWT REPORT
     * ============================================================
     */
    
    /**
     * SWT Report Index
     */
    public function swtIndex(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        
        // Get available months from payroll data
        $months = Payroll::where('company_id', $companyId)
            ->selectRaw('DISTINCT DATE_FORMAT(pay_date, "%Y-%m") as month')
            ->orderBy('month', 'desc')
            ->pluck('month')
            ->toArray();
        
        // Build month options with formatted display
        $monthOptions = [];
        foreach ($months as $month) {
            $date = Carbon::createFromFormat('Y-m', $month);
            $monthOptions[$month] = $date->format('F Y');
        }
        
        $selectedMonth = $request->month ?? ($months[0] ?? null);
        $reportData = [];
        $summary = [];
        
        if ($selectedMonth) {
            $reportData = $this->getSwtData($companyId, $selectedMonth);
            $summary = $this->calculateSwtSummary($reportData);
        }
        
        // Get available years for filter
        $years = Payroll::where('company_id', $companyId)
            ->selectRaw('DISTINCT YEAR(pay_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        return view('reports.swt.index', compact(
            'company',
            'monthOptions',
            'selectedMonth',
            'reportData',
            'summary',
            'years'
        ));
    }
    
    /**
     * Export SWT Report
     */
    public function exportSwt(Request $request)
    {
        $request->validate([
            'month' => 'required|string',
            'format' => 'required|in:pdf,excel,csv'
        ]);
        
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        $month = $request->month;
        $format = $request->format;
        
        $reportData = $this->getSwtData($companyId, $month);
        $summary = $this->calculateSwtSummary($reportData);
        $monthFormatted = Carbon::createFromFormat('Y-m', $month)->format('F Y');
        
        $filename = "SWT_{$company->code}_{$month}_" . date('Ymd');
        
        switch ($format) {
            case 'pdf':
                return $this->exportSwtPDF($company, $reportData, $summary, $month, $monthFormatted, $filename);
            case 'excel':
                return $this->exportSwtExcel($company, $reportData, $summary, $month, $monthFormatted, $filename);
            case 'csv':
                return $this->exportSwtCSV($company, $reportData, $summary, $month, $monthFormatted, $filename);
            default:
                return back()->with('error', 'Invalid export format.');
        }
    }
    
    /**
     * Get SWT data with user type filter
     */
    private function getSwtData($companyId, $month)
    {
        // Get all payrolls for the month
        $payrolls = Payroll::where('company_id', $companyId)
            ->whereRaw('DATE_FORMAT(pay_date, "%Y-%m") = ?', [$month])
            ->with(['items.employee'])
            ->get();
        
        if ($payrolls->isEmpty()) {
            return collect();
        }
        
        // ✅ Get current user's allowed employee types
        $user = auth()->user();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        // Aggregate payroll items by employee
        $employeeData = [];
        
        foreach ($payrolls as $payroll) {
            foreach ($payroll->items as $item) {
                // ✅ Skip if no employee or not allowed type
                if (!$item->employee || !in_array($item->employee->employee_type, $allowedTypes)) {
                    continue;
                }
                
                $empId = $item->employee_id;
                
                if (!isset($employeeData[$empId])) {
                    $employeeData[$empId] = (object) [
                        'employee_number' => $item->employee->employee_number,
                        'full_name' => $item->employee->full_name,
                        'total_gross' => 0,
                        'total_tax' => 0,
                        'payroll_count' => 0,
                    ];
                }
                
                $employeeData[$empId]->total_gross += $item->gross_wage;
                $employeeData[$empId]->total_tax += $item->tax;
                $employeeData[$empId]->payroll_count++;
            }
        }
        
        return collect($employeeData)->values();
    }
    
    /**
     * Calculate SWT summary
     */
    private function calculateSwtSummary($reportData)
    {
        return (object) [
            'total_employees' => $reportData->count(),
            'total_gross' => $reportData->sum('total_gross'),
            'total_tax' => $reportData->sum('total_tax'),
            'total_payrolls' => $reportData->sum('payroll_count'),
        ];
    }
    
    /**
     * Export SWT PDF
     */
    private function exportSwtPDF($company, $reportData, $summary, $month, $monthFormatted, $filename)
    {
        $pdf = Pdf::loadView('reports.swt.pdf', compact(
            'company',
            'reportData',
            'summary',
            'month',
            'monthFormatted'
        ));
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Export SWT Excel
     */
    private function exportSwtExcel($company, $reportData, $summary, $month, $monthFormatted, $filename)
    {
        return Excel::download(
            new SwtExport($company, $reportData, $summary, $month, $monthFormatted),
            $filename . '.xlsx'
        );
    }
    
    /**
     * Export SWT CSV
     */
    private function exportSwtCSV($company, $reportData, $summary, $month, $monthFormatted, $filename)
    {
        return Excel::download(
            new SwtExport($company, $reportData, $summary, $month, $monthFormatted),
            $filename . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }
    
    /**
     * ============================================================
     * SUMMARY OF EARNINGS REPORT (ANNUAL)
     * ============================================================
     */
    
    /**
     * Summary of Earnings Report Index (Annual)
     */
    public function earningsIndex(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        
        // Get available years from payroll data
        $years = Payroll::where('company_id', $companyId)
            ->selectRaw('DISTINCT YEAR(period_start) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        $selectedYear = $request->year ?? ($years[0] ?? null);
        $reportData = [];
        $summary = [];
        $fortnightData = [];
        
        if ($selectedYear) {
            $reportData = $this->getEarningsData($companyId, $selectedYear);
            $summary = $this->calculateEarningsSummary($reportData);
            $fortnightData = $this->getFortnightlyEarningsData($companyId, $selectedYear);
        }
        
        return view('reports.earnings.index', compact(
            'company',
            'years',
            'selectedYear',
            'reportData',
            'summary',
            'fortnightData'
        ));
    }
    
    /**
     * Export Summary of Earnings Report
     */
    public function exportEarnings(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'format' => 'required|in:pdf,excel,csv'
        ]);
        
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        $year = $request->year;
        $format = $request->format;
        
        $reportData = $this->getEarningsData($companyId, $year);
        $summary = $this->calculateEarningsSummary($reportData);
        $fortnightData = $this->getFortnightlyEarningsData($companyId, $year);
        
        $filename = "Earnings_Summary_{$company->code}_{$year}_" . date('Ymd');
        
        switch ($format) {
            case 'pdf':
                return $this->exportEarningsPDF($company, $reportData, $summary, $fortnightData, $year, $filename);
            case 'excel':
                return $this->exportEarningsExcel($company, $reportData, $summary, $fortnightData, $year, $filename);
            case 'csv':
                return $this->exportEarningsCSV($company, $reportData, $summary, $fortnightData, $year, $filename);
            default:
                return back()->with('error', 'Invalid export format.');
        }
    }
    
    /**
     * Get earnings data with user type filter
     */
    private function getEarningsData($companyId, $year)
    {
        // Get all payrolls for the year
        $payrolls = Payroll::where('company_id', $companyId)
            ->whereYear('period_start', $year)
            ->with(['items.employee'])
            ->get();
        
        if ($payrolls->isEmpty()) {
            return collect();
        }
        
        // ✅ Get current user's allowed employee types
        $user = auth()->user();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        // Aggregate payroll items by employee
        $employeeData = [];
        
        foreach ($payrolls as $payroll) {
            foreach ($payroll->items as $item) {
                // ✅ Skip if no employee or not allowed type
                if (!$item->employee || !in_array($item->employee->employee_type, $allowedTypes)) {
                    continue;
                }
                
                $empId = $item->employee_id;
                
                if (!isset($employeeData[$empId])) {
                    $employeeData[$empId] = (object) [
                        'employee_number' => $item->employee->employee_number,
                        'full_name' => $item->employee->full_name,
                        'total_gross' => 0,
                        'total_tax' => 0,
                        'total_net' => 0,
                        'total_nasfund_ee' => 0,
                        'total_nasfund_er' => 0,
                        'payroll_count' => 0,
                        'fortnights' => [],
                    ];
                }
                
                $employeeData[$empId]->total_gross += $item->gross_wage;
                $employeeData[$empId]->total_tax += $item->tax;
                $employeeData[$empId]->total_net += $item->net_pay;
                $employeeData[$empId]->total_nasfund_ee += $item->nasfund_ee;
                $employeeData[$empId]->total_nasfund_er += $item->nasfund_er;
                $employeeData[$empId]->payroll_count++;
                
                // Store fortnight data
                $employeeData[$empId]->fortnights[] = (object) [
                    'fortnight' => $payroll->fortnight_number,
                    'period_start' => $payroll->period_start,
                    'period_end' => $payroll->period_end,
                    'gross_wage' => $item->gross_wage,
                    'tax' => $item->tax,
                    'net_pay' => $item->net_pay,
                    'nasfund_ee' => $item->nasfund_ee,
                    'nasfund_er' => $item->nasfund_er,
                ];
            }
        }
        
        return collect($employeeData)->values();
    }
    
    /**
     * Get fortnightly earnings data for the year
     */
    private function getFortnightlyEarningsData($companyId, $year)
    {
        $payrolls = Payroll::where('company_id', $companyId)
            ->whereYear('period_start', $year)
            ->orderBy('period_start')
            ->with(['items'])
            ->get();
        
        $fortnightData = [];
        
        foreach ($payrolls as $payroll) {
            $fortnightData[] = (object) [
                'fortnight_number' => $payroll->fortnight_number,
                'period_start' => $payroll->period_start,
                'period_end' => $payroll->period_end,
                'total_gross' => $payroll->total_gross,
                'total_tax' => $payroll->total_tax,
                'total_net' => $payroll->total_net,
                'total_employees' => $payroll->total_employees,
                'status' => $payroll->status,
            ];
        }
        
        return collect($fortnightData);
    }
    
    /**
     * Calculate earnings summary
     */
    private function calculateEarningsSummary($reportData)
    {
        return (object) [
            'total_employees' => $reportData->count(),
            'total_gross' => $reportData->sum('total_gross'),
            'total_tax' => $reportData->sum('total_tax'),
            'total_net' => $reportData->sum('total_net'),
            'total_nasfund_ee' => $reportData->sum('total_nasfund_ee'),
            'total_nasfund_er' => $reportData->sum('total_nasfund_er'),
            'total_payrolls' => $reportData->sum('payroll_count'),
        ];
    }
    
    /**
     * Export Earnings PDF
     */
    private function exportEarningsPDF($company, $reportData, $summary, $fortnightData, $year, $filename)
    {
        $pdf = Pdf::loadView('reports.earnings.pdf', compact(
            'company',
            'reportData',
            'summary',
            'fortnightData',
            'year'
        ));
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Export Earnings Excel
     */
    private function exportEarningsExcel($company, $reportData, $summary, $fortnightData, $year, $filename)
    {
        return Excel::download(
            new EarningsExport($company, $reportData, $summary, $fortnightData, $year),
            $filename . '.xlsx'
        );
    }
    
    /**
     * Export Earnings CSV
     */
    private function exportEarningsCSV($company, $reportData, $summary, $fortnightData, $year, $filename)
    {
        return Excel::download(
            new EarningsExport($company, $reportData, $summary, $fortnightData, $year),
            $filename . '.csv',
            \Maatwebsite\Excel\Excel::CSV
        );
    }
    
    /**
     * ============================================================
     * EMPLOYEE PROFILE REPORT
     * ============================================================
     */
    
    /**
     * Employee Profile Report Index
     */
    public function profileIndex(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $company = Company::find($companyId);
        
        // ✅ Get current user's allowed employee types
        $user = auth()->user();
        $allowedTypes = $user->getAllowedEmployeeTypes();
        
        // Get employees for selection - filter by user type
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'Active')
            ->whereIn('employee_type', $allowedTypes)
            ->orderBy('full_name')
            ->get(['id', 'employee_number', 'full_name']);
        
        $selectedEmployee = $request->employee_id;
        $profileData = null;
        
        if ($selectedEmployee) {
            $profileData = $this->getEmployeeProfileData($selectedEmployee);
        }
        
        return view('reports.profile.index', compact(
            'company',
            'employees',
            'selectedEmployee',
            'profileData'
        ));
    }
    
    /**
     * Export Employee Profile Report
     */
    public function exportProfile(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'format' => 'required|in:pdf,excel'
        ]);
        
        $employeeId = $request->employee_id;
        $format = $request->format;
        
        $profileData = $this->getEmployeeProfileData($employeeId);
        
        if (!$profileData) {
            return back()->with('error', 'Employee not found.');
        }
        
        $employee = $profileData->employee;
        $company = $employee->company;
        
        $filename = "Employee_Profile_{$employee->employee_number}_{$employee->full_name}_" . date('Ymd');
        
        switch ($format) {
            case 'pdf':
                return $this->exportProfilePDF($company, $profileData, $filename);
            case 'excel':
                return $this->exportProfileExcel($company, $profileData, $filename);
            default:
                return back()->with('error', 'Invalid export format.');
        }
    }
    
    /**
     * Get complete employee profile data
     */
    private function getEmployeeProfileData($employeeId)
    {
        $employee = Employee::with([
            'company',
            'department',
            'position',
            'bankAccounts',
            'documents',
            'leaveRecords',
            'payIncreaseHistory',
            'disciplineRecords',
            'payrollItems' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ])->find($employeeId);
        
        if (!$employee) {
            return null;
        }
        
        // ✅ Check if user can view this employee
        $user = auth()->user();
        if (!$user->canViewEmployee($employee)) {
            abort(403, 'You are not authorized to view this employee.');
        }
        
        // Calculate leave balances
        $leaveBalance = $this->calculateLeaveBalance($employee);
        
        // Calculate total earnings
        $totalEarnings = $employee->payrollItems->sum('gross_wage');
        $totalTax = $employee->payrollItems->sum('tax');
        $totalNet = $employee->payrollItems->sum('net_pay');
        
        // Calculate service length
        $serviceLength = $this->calculateServiceLength($employee);
        
        return (object) [
            'employee' => $employee,
            'leave_balance' => $leaveBalance,
            'total_earnings' => $totalEarnings,
            'total_tax' => $totalTax,
            'total_net' => $totalNet,
            'service_length' => $serviceLength,
            'payroll_count' => $employee->payrollItems->count(),
            'leave_count' => $employee->leaveRecords->count(),
            'document_count' => $employee->documents->count(),
            'discipline_count' => $employee->disciplineRecords->count(),
            'pay_increase_count' => $employee->payIncreaseHistory->count(),
        ];
    }
    
    /**
     * Calculate leave balance
     */
    private function calculateLeaveBalance($employee)
    {
        // Annual Leave: 1 day earned every 1.5 months, max 9 days per year
        $joiningDate = $employee->joining_date;
        if (!$joiningDate) {
            return (object) ['earned' => 0, 'taken' => 0, 'balance' => 0];
        }
        
        $months = $joiningDate->diffInMonths(now());
        $earned = floor($months / 1.5);
        $earned = min($earned, 9);
        
        $taken = $employee->leaveRecords->sum('leave_taken') ?? 0;
        $balance = max(0, $earned - $taken);
        
        return (object) [
            'earned' => $earned,
            'taken' => $taken,
            'balance' => $balance,
        ];
    }
    
    /**
     * Calculate service length
     */
    private function calculateServiceLength($employee)
    {
        if (!$employee->joining_date) {
            return (object) ['years' => 0, 'months' => 0, 'days' => 0, 'formatted' => 'N/A'];
        }
        
        $diff = $employee->joining_date->diff(now());
        
        return (object) [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d,
            'formatted' => $diff->y . ' years, ' . $diff->m . ' months, ' . $diff->d . ' days',
        ];
    }
    
    /**
     * Export Profile PDF
     */
    private function exportProfilePDF($company, $profileData, $filename)
    {
        $pdf = Pdf::loadView('reports.profile.pdf', compact(
            'company',
            'profileData'
        ));
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Export Profile Excel
     */
    private function exportProfileExcel($company, $profileData, $filename)
    {
        return Excel::download(
            new ProfileExport($company, $profileData),
            $filename . '.xlsx'
        );
    }
    
    /**
     * Helper: Get fortnight period
     */
    private function getFortnightPeriod($fortnight)
    {
        $year = (int)substr($fortnight, 0, 2);
        $week = (int)substr($fortnight, 2);
        $fullYear = 2000 + $year;
        $start = Carbon::createFromDate($fullYear - 1, 12, 25)->addDays(($week - 1) * 14);
        $end = $start->copy()->addDays(13);
        
        return (object) [
            'start' => $start,
            'end' => $end,
            'formatted' => $start->format('d M Y') . ' - ' . $end->format('d M Y'),
        ];
    }
}
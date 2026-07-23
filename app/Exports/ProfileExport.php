<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class ProfileExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $company;
    protected $profileData;
    
    public function __construct($company, $profileData)
    {
        $this->company = $company;
        $this->profileData = $profileData;
    }
    
    public function collection()
    {
        $employee = $this->profileData->employee;
        $rows = collect();
        
        // Personal Details
        $rows->push(['EMPLOYEE PROFILE']);
        $rows->push([]);
        $rows->push(['Personal Details']);
        $rows->push(['Employee Number', $employee->employee_number]);
        $rows->push(['Full Name', $employee->full_name]);
        $rows->push(['Gender', $employee->gender ?? 'N/A']);
        $rows->push(['Date of Birth', $employee->date_of_birth ? Carbon::parse($employee->date_of_birth)->format('d M Y') : 'N/A']);
        $rows->push(['Age', $employee->age ?? 'N/A']);
        $rows->push(['Employee Type', $employee->employee_type ?? 'N/A']);
        $rows->push(['Status', $employee->status ?? 'N/A']);
        $rows->push([]);
        
        // Employment Details
        $rows->push(['Employment Details']);
        $rows->push(['Department', $employee->department->name ?? 'N/A']);
        $rows->push(['Position', $employee->position_name ?? 'N/A']);
        $rows->push(['Joining Date', $employee->joining_date ? Carbon::parse($employee->joining_date)->format('d M Y') : 'N/A']);
        $rows->push(['Service Length', $this->profileData->service_length->formatted]);
        $rows->push(['End Date', $employee->end_date ? Carbon::parse($employee->end_date)->format('d M Y') : 'N/A']);
        $rows->push([]);
        
        // Banking Details
        $rows->push(['Banking Details']);
        foreach ($employee->bankAccounts as $bank) {
            $rows->push([
                'Account Name', $bank->account_name,
                'Account Number', $bank->account_number,
                'Bank', $bank->bank_name,
                'BSB', $bank->bsb_code,
                'Preferred', $bank->is_preferred ? 'Yes' : 'No'
            ]);
        }
        $rows->push([]);
        
        // Leave Summary
        $rows->push(['Leave Summary']);
        $rows->push(['Leave Days Earned', $this->profileData->leave_balance->earned]);
        $rows->push(['Leave Days Taken', $this->profileData->leave_balance->taken]);
        $rows->push(['Leave Balance', $this->profileData->leave_balance->balance]);
        $rows->push([]);
        
        // Payroll Summary
        $rows->push(['Payroll Summary']);
        $rows->push(['Total Payrolls', $this->profileData->payroll_count]);
        $rows->push(['Total Gross Earnings', number_format($this->profileData->total_earnings, 2)]);
        $rows->push(['Total Tax', number_format($this->profileData->total_tax, 2)]);
        $rows->push(['Total Net Pay', number_format($this->profileData->total_net, 2)]);
        $rows->push([]);
        
        // Pay Increase History
        $rows->push(['Pay Increase History']);
        if ($employee->payIncreaseHistory->count() > 0) {
            $rows->push(['Date', 'Previous Rate', 'New Rate', 'Reason']);
            foreach ($employee->payIncreaseHistory as $history) {
                $rows->push([
                    Carbon::parse($history->effective_date)->format('d M Y'),
                    number_format($history->previous_rate, 2),
                    number_format($history->new_rate, 2),
                    $history->reason ?? 'N/A'
                ]);
            }
        } else {
            $rows->push(['No pay increase records found.']);
        }
        $rows->push([]);
        
        // Discipline Records
        $rows->push(['Discipline Records']);
        if ($employee->disciplineRecords->count() > 0) {
            $rows->push(['Date', 'Offense', 'Action Taken', 'Status']);
            foreach ($employee->disciplineRecords as $record) {
                $rows->push([
                    Carbon::parse($record->date_issued)->format('d M Y'),
                    $record->offense ?? 'N/A',
                    $record->action_taken ?? 'N/A',
                    $record->status ?? 'N/A'
                ]);
            }
        } else {
            $rows->push(['No discipline records found.']);
        }
        $rows->push([]);
        
        // Leave Records
        $rows->push(['Leave Records']);
        if ($employee->leaveRecords->count() > 0) {
            $rows->push(['Date', 'Type', 'Days', 'Status']);
            foreach ($employee->leaveRecords as $leave) {
                $rows->push([
                    Carbon::parse($leave->date)->format('d M Y'),
                    $leave->type ?? 'N/A',
                    $leave->days_taken ?? 0,
                    $leave->status ?? 'N/A'
                ]);
            }
        } else {
            $rows->push(['No leave records found.']);
        }
        $rows->push([]);
        
        // Recent Payroll
        $rows->push(['Recent Payroll History']);
        if ($employee->payrollItems->count() > 0) {
            $rows->push(['Date', 'Fortnight', 'Gross', 'Tax', 'Net', 'Method']);
            foreach ($employee->payrollItems->take(10) as $item) {
                $rows->push([
                    Carbon::parse($item->created_at)->format('d M Y'),
                    $item->payroll->fortnight_number ?? 'N/A',
                    number_format($item->gross_wage, 2),
                    number_format($item->tax, 2),
                    number_format($item->net_pay, 2),
                    $item->payment_method ?? 'N/A'
                ]);
            }
        } else {
            $rows->push(['No payroll records found.']);
        }
        
        // Footer
        $rows->push([]);
        $rows->push(['Report generated: ' . now()->format('d M Y H:i:s')]);
        $rows->push(['Generated By: ' . auth()->user()->name]);
        
        return $rows;
    }
    
    public function headings(): array
    {
        return [];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Style header rows
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1a1f36']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Style section headers
        $sheet->getStyle('A3:E3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB']
            ],
        ]);
    }
    
    public function title(): string
    {
        return 'Employee Profile';
    }
}

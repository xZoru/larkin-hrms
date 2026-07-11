<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class EarningsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $company;
    protected $reportData;
    protected $summary;
    protected $fortnightData;
    protected $year;
    
    public function __construct($company, $reportData, $summary, $fortnightData, $year)
    {
        $this->company = $company;
        $this->reportData = $reportData;
        $this->summary = $summary;
        $this->fortnightData = $fortnightData;
        $this->year = $year;
    }
    
    public function collection()
    {
        $rows = collect();
        
        foreach ($this->reportData as $item) {
            $rows->push([
                $item->employee_number,
                $item->full_name,
                number_format($item->total_gross, 2),
                number_format($item->total_tax, 2),
                number_format($item->total_net, 2),
                number_format($item->total_nasfund_ee, 2),
                number_format($item->total_nasfund_er, 2),
                $item->payroll_count,
            ]);
        }
        
        // Add summary row
        $rows->push([
            'TOTAL',
            $this->summary->total_employees . ' Employees',
            number_format($this->summary->total_gross, 2),
            number_format($this->summary->total_tax, 2),
            number_format($this->summary->total_net, 2),
            number_format($this->summary->total_nasfund_ee, 2),
            number_format($this->summary->total_nasfund_er, 2),
            $this->summary->total_payrolls,
        ]);
        
        // Add blank row and fortnight summary header
        $rows->push([]);
        $rows->push(['FORTNIGHTLY SUMMARY FOR ' . $this->year]);
        $rows->push([
            'Fortnight', 'Period', 'Employees', 'Gross Wage', 'Tax', 'Net Pay', 'Status'
        ]);
        
        foreach ($this->fortnightData as $fn) {
            $rows->push([
                $fn->fortnight_number,
                Carbon::parse($fn->period_start)->format('d M') . ' - ' . Carbon::parse($fn->period_end)->format('d M'),
                $fn->total_employees,
                number_format($fn->total_gross, 2),
                number_format($fn->total_tax, 2),
                number_format($fn->total_net, 2),
                $fn->status,
            ]);
        }
        
        return $rows;
    }
    
    public function headings(): array
    {
        return [
            'Employee #',
            'Employee Name',
            'Total Gross',
            'Total Tax',
            'Total Net',
            'NASFUND EE (6%)',
            'NASFUND ER (8.4%)',
            'Payrolls',
        ];
    }
    
    public function map($row): array
    {
        return $row;
    }
    
    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1a1f36']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Style the total row
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:H{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB']
            ],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
        
        // Add borders to all cells
        $sheet->getStyle('A1:H' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
    }
    
    public function title(): string
    {
        return 'Earnings Summary ' . $this->year;
    }
}
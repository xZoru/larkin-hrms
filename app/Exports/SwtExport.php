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

class SwtExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $company;
    protected $reportData;
    protected $summary;
    protected $month;
    protected $monthFormatted;
    
    public function __construct($company, $reportData, $summary, $month, $monthFormatted)
    {
        $this->company = $company;
        $this->reportData = $reportData;
        $this->summary = $summary;
        $this->month = $month;
        $this->monthFormatted = $monthFormatted;
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
                $item->payroll_count,
            ]);
        }
        
        // Add summary row
        $rows->push([
            'TOTAL',
            $this->summary->total_employees . ' Employees',
            number_format($this->summary->total_gross, 2),
            number_format($this->summary->total_tax, 2),
            $this->summary->total_payrolls,
        ]);
        
        return $rows;
    }
    
    public function headings(): array
    {
        return [
            'Employee #',
            'Employee Name',
            'Gross Wages',
            'Tax',
            'Payroll Count',
        ];
    }
    
    public function map($row): array
    {
        return $row;
    }
    
    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:E1')->applyFromArray([
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
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Style the total row
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:E{$lastRow}")->applyFromArray([
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
        $sheet->getStyle('A1:E' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
    }
    
    public function title(): string
    {
        return 'SWT Report';
    }
}
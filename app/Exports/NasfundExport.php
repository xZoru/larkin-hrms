<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class NasfundExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $company;
    protected $reportData;
    protected $summary;
    protected $period;
    protected $fortnight;
    
    public function __construct($company, $reportData, $summary, $period, $fortnight)
    {
        $this->company = $company;
        $this->reportData = $reportData;
        $this->summary = $summary;
        $this->period = $period;
        $this->fortnight = $fortnight;
    }
    
    public function collection()
    {
        // Add data rows
        $rows = collect();
        
        foreach ($this->reportData as $item) {
            $rows->push([
                $item->employee_number,
                $item->full_name,
                $item->nasfund_number,
                number_format($item->gross_wage, 2),
                number_format($item->ee_contribution, 2),
                number_format($item->er_contribution, 2),
                number_format($item->total_contribution, 2),
            ]);
        }
        
        // Add summary row
        $rows->push([
            'TOTAL',
            $this->summary->total_employees . ' Employees',
            '',
            number_format($this->summary->total_gross, 2),
            number_format($this->summary->total_ee, 2),
            number_format($this->summary->total_er, 2),
            number_format($this->summary->total_contributions, 2),
        ]);
        
        return $rows;
    }
    
    public function headings(): array
    {
        return [
            'Employee #',
            'Employee Name',
            'NASFUND #',
            'Gross Wage',
            'EE Contribution (6%)',
            'ER Contribution (8.4%)',
            'Total Contribution',
        ];
    }
    
    public function map($row): array
    {
        return $row;
    }
    
    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:G1')->applyFromArray([
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
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Style the total row
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:G{$lastRow}")->applyFromArray([
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
        $sheet->getStyle('A1:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
    }
    
    public function title(): string
    {
        return 'NASFUND Report';
    }
}
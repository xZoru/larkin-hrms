<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ABAExport implements FromCollection, WithTitle, ShouldAutoSize, WithEvents
{
    protected $batch;
    protected $payrollItems;

    public function __construct($batch, $payrollItems)
    {
        $this->batch = $batch;
        $this->payrollItems = $payrollItems;
    }

    public function collection()
    {
        $data = [];
        
        // Header rows - each as a single array with 2 columns
        $data[] = ['Company Name:', $this->batch->company->name ?? ''];
        $data[] = ['Type of Payment:', $this->batch->metadata['payment_type'] ?? 'SALARY'];
        
        $date = isset($this->batch->metadata['payment_date']) 
            ? date('n/j/Y', strtotime($this->batch->metadata['payment_date'])) 
            : date('n/j/Y');
        $data[] = ['Date:', $date];
        
        $bsb = $this->batch->bsb_number ?? '';
        if ($bsb && strlen($bsb) >= 6) {
            $bsb = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        }
        $data[] = ['Company BSB:', $bsb];
        $data[] = ['Company Account:', $this->batch->account_number ?? ''];
        $data[] = ['Total Amount:', number_format($this->batch->total_amount, 2)];
        $data[] = ['Debit Description:', $this->batch->metadata['debit_description'] ?? 'PAYROLL'];
        
        // Empty row
        $data[] = [];
        
        // Table headers
        $data[] = ['BSB', 'Account Number', 'Amount', 'Account Name', 'Description'];
        
        // Data rows
        foreach ($this->payrollItems as $item) {
            $employee = $item->employee;
            $bankAccount = $employee->bankAccounts()->where('is_active', true)->first();
            
            $bsb = $bankAccount->bsb_code ?? '';
            if ($bsb && strlen($bsb) >= 6) {
                $bsb = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
            }
            
            $data[] = [
                $bsb,
                $bankAccount->account_number ?? '',
                number_format($item->net_pay, 2),
                strtoupper($bankAccount->account_name ?? $employee->full_name),
                $this->batch->metadata['debit_description'] ?? 'FN' . ($this->batch->payroll->fortnight_number ?? ''),
            ];
        }
        
        return collect($data);
    }

    public function title(): string
    {
        return 'ABA Payments';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Count total rows
                $totalRows = count($this->payrollItems) + 10; // 7 header + 1 empty + 1 table header + data + total
                
                // === HEADER STYLING ===
                $sheet->getStyle('A1:A7')->getFont()->setBold(true);
                
                // === MERGE HEADER CELLS ===
                $sheet->mergeCells('A1:B1');
                $sheet->mergeCells('A2:B2');
                $sheet->mergeCells('A3:B3');
                $sheet->mergeCells('A4:B4');
                $sheet->mergeCells('A5:B5');
                $sheet->mergeCells('A6:B6');
                $sheet->mergeCells('A7:B7');

                // === TABLE HEADER STYLING ===
                $headerRow = 9;
                $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF4B5563');
                $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getFont()->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // === TABLE BORDERS ===
                $startDataRow = 10;
                $lastDataRow = $startDataRow + count($this->payrollItems) - 1;
                
                if ($lastDataRow >= $startDataRow) {
                    $sheet->getStyle('A' . $headerRow . ':E' . $lastDataRow)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);
                }

                // === ALIGNMENT ===
                if ($lastDataRow >= $startDataRow) {
                    $sheet->getStyle('A' . $startDataRow . ':A' . $lastDataRow)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('B' . $startDataRow . ':B' . $lastDataRow)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('C' . $startDataRow . ':C' . $lastDataRow)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // === COLUMN WIDTHS ===
                $sheet->getColumnDimension('A')->setWidth(14);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(16);
                $sheet->getColumnDimension('D')->setWidth(35);
                $sheet->getColumnDimension('E')->setWidth(16);

                // === TOTAL ROW ===
                if ($lastDataRow >= $startDataRow) {
                    $totalRow = $lastDataRow + 1;
                    $sheet->setCellValue('B' . $totalRow, 'TOTAL:');
                    $sheet->setCellValue('C' . $totalRow, number_format($this->batch->total_amount, 2));
                    $sheet->getStyle('B' . $totalRow . ':C' . $totalRow)->getFont()->setBold(true);
                    $sheet->getStyle('B' . $totalRow . ':C' . $totalRow)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF3F4F6');
                }
            },
        ];
    }
}
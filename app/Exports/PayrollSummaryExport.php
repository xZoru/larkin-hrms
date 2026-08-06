<?php
// app/Exports/PayrollSummaryExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class PayrollSummaryExport implements FromCollection, WithStyles, WithColumnWidths, WithEvents
{
    protected $payrollId;
    protected $payrollItems;
    protected $company;
    protected $payroll;

    // Number of employee data rows (not counting the totals row). Set inside
    // collection() and used by registerEvents() to know where the table lands
    // after the header block is inserted above it.
    protected $dataRowCount = 0;

    // Kina accounting number format used throughout the summary boxes.
    protected $kinaFormat = '_-"K"* #,##0.00_-;\-"K"* #,##0.00_-;_-"K"* "-"??_-;_-@_-';

    public function __construct($payrollId)
    {
        $this->payrollId = $payrollId;
        $this->loadData();
    }

    protected function loadData()
    {
        $this->payroll = \App\Models\Payroll::with(['items.employee', 'company'])->findOrFail($this->payrollId);
        $this->payrollItems = $this->payroll->items()->with('employee')->get();
        $this->company = $this->payroll->company;

        // Add FN Rate to each item
        $this->payrollItems->each(function ($item) {
            $employee = $item->employee;
            if ($employee) {
                if ((float) $employee->monthly_salary > 0) {
                    $item->fn_rate = round((float) $employee->monthly_salary / 2, 2);
                } else {
                    $item->fn_rate = round((float) $employee->hourly_rate * 84, 2);
                }
            } else {
                $item->fn_rate = round((float) $item->net_pay, 2);
            }
        });
    }

    public function collection()
    {
        $rows = collect();
        $totals = [
            'fn_rate' => 0, 'basic_pay' => 0, 'regular' => 0, 'over_time' => 0,
            'sunday_ot' => 0, 'holiday_ot' => 0, 'final_pay' => 0, 'other' => 0,
            'gross_total' => 0, 'fn_tax' => 0, 'npf' => 0, 'ncsl' => 0,
            'cash_adv' => 0, 'net_pay' => 0,
        ];

        foreach ($this->payrollItems as $item) {
            $employee = $item->employee;

            if ($employee) {
                $employeeNumber = $employee->employee_number ?? '';
                $employeeName = $employee->full_name ?? 'N/A';
            } else {
                $details = $item->details;
                $employeeNumber = $details['account_number'] ?? 'MANUAL';
                $employeeName = $details['account_name'] ?? 'Manual Entry';
            }

            $row = [
                'employee_no' => $employeeNumber,
                'employee_name' => $employeeName,
                'fn_rate' => (float) ($item->fn_rate ?? 0),
                'basic_pay' => (float) ($item->basic_pay ?? 0),
                'regular' => (float) ($item->regular_pay ?? 0),
                'over_time' => (float) ($item->overtime_pay ?? 0),
                'sunday_ot' => (float) ($item->sunday_pay ?? 0),
                'holiday_ot' => (float) ($item->holiday_pay ?? 0),
                'final_pay' => 0,
                'other' => (float) ($item->allowance ?? 0),
                'gross_total' => (float) ($item->gross_wage ?? 0),
                'fn_tax' => (float) ($item->tax ?? 0),
                'npf' => (float) ($item->nasfund_ee ?? 0),
                'ncsl' => 0,
                'cash_adv' => (float) ($item->loan_deduction ?? 0),
                'net_pay' => (float) ($item->net_pay ?? 0),
            ];

            $rows->push($row);

            foreach ($totals as $key => $value) {
                $totals[$key] += $row[$key];
            }
        }

        $this->dataRowCount = $rows->count();

        // Totals row
        $rows->push([
            'employee_no' => 'TOTAL',
            'employee_name' => '',
            'fn_rate' => $totals['fn_rate'],
            'basic_pay' => $totals['basic_pay'],
            'regular' => $totals['regular'],
            'over_time' => $totals['over_time'],
            'sunday_ot' => $totals['sunday_ot'],
            'holiday_ot' => $totals['holiday_ot'],
            'final_pay' => $totals['final_pay'],
            'other' => $totals['other'],
            'gross_total' => $totals['gross_total'],
            'fn_tax' => $totals['fn_tax'],
            'npf' => $totals['npf'],
            'ncsl' => $totals['ncsl'],
            'cash_adv' => $totals['cash_adv'],
            'net_pay' => $totals['net_pay'],
        ]);

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setTitle('Payroll Summary');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 28,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 12,
            'J' => 10,
            'K' => 14,
            'L' => 12,
            'M' => 12,
            'N' => 10,
            'O' => 13,
            'P' => 14,
            'Q' => 4,
            'R' => 4,
            'S' => 4,
            'T' => 18,
            'U' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'P';

                // Push the employee data (currently rows 1..dataRowCount+1) down to
                // make room for the title / payroll-date / bank-summary / two-row
                // table header block that sits above it in the target layout.
                $sheet->insertNewRowBefore(1, 7);

                $headerRow1 = 6;
                $headerRow2 = 7;
                $firstDataRow = 8;
                $lastDataRow = $firstDataRow + $this->dataRowCount - 1;
                $totalRowNum = $lastDataRow + 1;

                // ---- Company title, top-left ----
                $sheet->mergeCells('C1:F3');
                $sheet->setCellValue('C1', $this->company->name ?? 'LARKIN POM');
                $sheet->getStyle('C1')->getFont()->setName('Arial Black')->setSize(16);
                $sheet->getStyle('C1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // ---- Company logo ----
                $logoPath = null;

                // 1. Check SQLite database logo_path value
                if (!empty($this->company->logo_path)) {
                    $resolved = public_path(ltrim($this->company->logo_path, '/'));
                    if (file_exists($resolved) && !is_dir($resolved)) {
                        $logoPath = $resolved;
                    }
                }

                // 2. Fall back to static committed public images (.png or .jpg)
                if (!$logoPath) {
                    if (file_exists(public_path('images/logo.png'))) {
                        $logoPath = public_path('images/logo.png');
                    } elseif (file_exists(public_path('images/logo.jpg'))) {
                        $logoPath = public_path('images/logo.jpg');
                    }
                }

                if ($logoPath) {
                    try {
                        $drawing = new Drawing();
                        $drawing->setName('Company Logo');
                        $drawing->setDescription('Company Logo');
                        $drawing->setPath($logoPath);
                        $drawing->setResizeProportional(true);
                        $drawing->setHeight(120);
                        $drawing->setCoordinates('A1');
                        $drawing->setOffsetX(5);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($sheet);

                        $sheet->getRowDimension(1)->setRowHeight(30);
                        $sheet->getRowDimension(2)->setRowHeight(30);
                        $sheet->getRowDimension(3)->setRowHeight(30);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('Payroll export: failed to embed logo', [
                            'payroll_id' => $this->payrollId,
                            'logo_path' => $logoPath,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning('Payroll export: logo file not found on disk', [
                        'payroll_id' => $this->payrollId,
                        'company_id' => $this->company->id ?? null,
                    ]);
                }

                // ---- "FN PAYROLL" / pay date ----
                $sheet->setCellValue('D4', 'FN PAYROLL');
                $sheet->getStyle('D4')->getFont()->setName('Arial Black')->setSize(11);

                $sheet->setCellValue('G4', 'DATE');
                $sheet->getStyle('G4')->getFont()->setBold(true)->setSize(10);

                $payDate = $this->payroll->pay_date ?? $this->payroll->period_end ?? null;
                if ($payDate) {
                    $sheet->setCellValue('H4', Carbon::parse($payDate));
                    $sheet->getStyle('H4')->getNumberFormat()->setFormatCode('d-mmm');
                }
                $sheet->getStyle('H4')->getFont()->setBold(true)->setSize(10);

                // ---- Bank vs Cash payout split ----
                $bankTotal = 0;
                $cashTotal = 0;
                foreach ($this->payrollItems as $item) {
                    $method = strtolower($item->employee->payment_method ?? 'bank');
                    if ($method === 'cash') {
                        $cashTotal += (float) ($item->net_pay ?? 0);
                    } else {
                        $bankTotal += (float) ($item->net_pay ?? 0);
                    }
                }

                $sheet->mergeCells('O1:P3');

                $sheet->setCellValue('O4', 'Bank');
                $sheet->setCellValue('P4', $bankTotal);
                $sheet->setCellValue('O5', 'Cash');
                $sheet->setCellValue('P5', $cashTotal);
                $sheet->getStyle('P4:P5')->getNumberFormat()->setFormatCode($this->kinaFormat);

                // ---- Bank Payout Summary panel, top-right ----
                $sheet->mergeCells('T2:U2');
                $sheet->setCellValue('T2', 'BANK PAYOUT SUMMARY');
                $sheet->getStyle('T2')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('T2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue('T3', 'Column1');
                $sheet->setCellValue('U3', 'Amount');
                $sheet->getStyle('T3:U3')->getFont()->setBold(true);

                // National vs Expat split
                $nationalTotal = 0;
                $expatTotal = 0;
                foreach ($this->payrollItems as $item) {
                    $nationality = strtolower($item->employee->nationality ?? 'national');
                    $netPay = (float) ($item->net_pay ?? 0);

                    if (str_contains($nationality, 'expat')) {
                        $expatTotal += $netPay;
                    } else {
                        $nationalTotal += $netPay;
                    }
                }

                // Site / department breakdown
                $locationTotals = [];
                foreach ($this->payrollItems as $item) {
                    $location = $item->employee->location ?? 'Other';
                    $locationTotals[$location] = ($locationTotals[$location] ?? 0) + (float) ($item->net_pay ?? 0);
                }

                $row = 4;

                $sheet->setCellValue("T{$row}", 'National');
                $sheet->setCellValue("U{$row}", $nationalTotal);
                $sheet->getStyle("T{$row}:U{$row}")->getFont()->setBold(true);
                $row++;

                $sheet->setCellValue("T{$row}", 'Expat');
                $sheet->setCellValue("U{$row}", $expatTotal);
                $sheet->getStyle("T{$row}:U{$row}")->getFont()->setBold(true);
                $row++;
                $row++; // blank spacer row between the nationality split and site breakdown

                $sheet->setCellValue("T{$row}", 'By Location');
                $sheet->getStyle("T{$row}")->getFont()->setItalic(true);
                $row++;

                $grandTotal = $nationalTotal + $expatTotal;
                foreach ($locationTotals as $location => $amount) {
                    $sheet->setCellValue("T{$row}", $location);
                    $sheet->setCellValue("U{$row}", $amount);
                    $row++;
                }
                $row++; // blank spacer row, matches the sample layout
                $sheet->setCellValue("T{$row}", 'TOTAL');
                $sheet->setCellValue("U{$row}", $grandTotal);
                $sheet->getStyle("T{$row}")->getFont()->setBold(true);
                $sheet->getStyle("U4:U{$row}")->getNumberFormat()->setFormatCode($this->kinaFormat);

                // ---- Main table header, rows 6 & 7 ----
                $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");
                $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");
                $sheet->mergeCells("C{$headerRow1}:J{$headerRow1}");
                $sheet->mergeCells("K{$headerRow1}:K{$headerRow2}");
                $sheet->mergeCells("L{$headerRow1}:O{$headerRow1}");
                $sheet->mergeCells("P{$headerRow1}:P{$headerRow2}");

                $sheet->setCellValue("A{$headerRow1}", 'EMP. NO.');
                $sheet->setCellValue("B{$headerRow1}", 'Employee Name');
                $sheet->setCellValue("C{$headerRow1}", 'GROSS');
                $sheet->setCellValue("K{$headerRow1}", 'Gross Total');
                $sheet->setCellValue("L{$headerRow1}", 'DEDUCTIONS');
                $sheet->setCellValue("P{$headerRow1}", 'Net Pay');

                $subHeaders = [
                    'C' => 'FN Rate', 'D' => 'Basic Pay', 'E' => 'Regular', 'F' => 'Over Time',
                    'G' => 'Sunday OT', 'H' => 'Holiday Pay', 'I' => 'Final Pay', 'J' => 'Other',
                    'L' => 'FN Tax', 'M' => 'NPF (6%)', 'N' => 'NCSL', 'O' => 'Cash Adv',
                ];
                foreach ($subHeaders as $col => $label) {
                    $sheet->setCellValue("{$col}{$headerRow2}", $label);
                }

                $sheet->getStyle("A{$headerRow1}:{$lastColumn}{$headerRow2}")
                    ->getFont()->setBold(true)->setSize(9);
                $sheet->getStyle("A{$headerRow1}:{$lastColumn}{$headerRow2}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$headerRow1}:{$lastColumn}{$headerRow2}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // ---- Data rows: light-green fill + currency formatting ----
                $sheet->getStyle("A{$firstDataRow}:{$lastColumn}{$lastDataRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C2F9BF');

                // Cash-paid employees are intentionally left white so they are
                // distinct from the existing green bank-transfer rows.
                foreach ($this->payrollItems->values() as $index => $item) {
                    if (strtolower($item->employee->payment_method ?? '') === 'cash') {
                        $rowNumber = $firstDataRow + $index;
                        $sheet->getStyle("A{$rowNumber}:{$lastColumn}{$rowNumber}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                    }
                }

                $sheet->getStyle("C{$firstDataRow}:{$lastColumn}{$lastDataRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');

                $sheet->getStyle("A{$firstDataRow}:A{$lastDataRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("C{$firstDataRow}:{$lastColumn}{$lastDataRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // ---- Totals row: bold + yellow fill ----
                $sheet->getStyle("A{$totalRowNum}:{$lastColumn}{$totalRowNum}")
                    ->getFont()->setBold(true);
                $sheet->getStyle("A{$totalRowNum}:{$lastColumn}{$totalRowNum}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                $sheet->getStyle("A{$totalRowNum}:{$lastColumn}{$totalRowNum}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("C{$totalRowNum}:{$lastColumn}{$totalRowNum}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("A{$totalRowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Auto-filter on the sub-header row, freeze the first two columns
                $sheet->setAutoFilter("A{$headerRow2}:{$lastColumn}{$headerRow2}");
                $sheet->freezePane("C{$firstDataRow}");

                $sheet->getSheetView()->setZoomScale(85);
            },
        ];
    }
}

<?php

namespace App\Exports;

use App\Models\Payroll;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class AllPayrollsExport implements FromCollection, WithColumnWidths, WithEvents, WithTitle
{
    private array $sections = [];
    /** @var array<int, string> Branch name keyed by the exported payroll-item object. */
    private array $itemBranchNames = [];
    private string $kinaFormat = '_-"K"* #,##0.00_-;\-"K"* #,##0.00_-;_-"K"* "-"??_-;_-@_-';

    public function __construct(private int $companyId, private string $fortnight)
    {
    }

    public function title(): string
    {
        return 'All Payruns';
    }

    public function collection()
    {
        $rows = collect();
        $this->sections = [];
        $payrolls = Payroll::with(['items.employee.assignments.branch', 'company', 'branch'])
            ->where('company_id', $this->companyId)
            ->where('fortnight_number', $this->fortnight)
            ->orderBy('period_start')
            ->orderBy('id')
            ->get();

        $payroll = $payrolls->first();
        $this->itemBranchNames = [];
        $items = $payrolls->flatMap(function ($payrun) {
            // The payrun's branch is the authoritative source for the payout
            // grouping. Employee assignments can change after a payrun is made.
            $branchName = $payrun->branch?->name ?? 'Main Office / Unassigned';

            return $payrun->items->each(function ($item) use ($branchName) {
                $this->itemBranchNames[spl_object_id($item)] = $branchName;
            });
        })->values();

        // Build one consolidated table. The first payrun supplies the report
        // heading and pay date, while every payrun's employee rows contribute
        // to the same totals and payout summary.
        for ($row = 0; $row < 7; $row++) {
            $rows->push(array_fill(0, 16, null));
        }

        foreach ($items as $item) {
            $employee = $item->employee;
            $fnRate = $employee && (float) $employee->monthly_salary > 0
                ? (float) $employee->monthly_salary / 2
                : (float) ($employee?->hourly_rate ?? 0) * 84;

            $rows->push([
                $employee?->employee_number ?? data_get($item->details, 'account_number', 'MANUAL'),
                $employee?->full_name ?? data_get($item->details, 'account_name', 'Manual Entry'),
                round($fnRate, 2), (float) ($item->basic_pay ?? 0), (float) ($item->regular_pay ?? 0),
                (float) ($item->overtime_pay ?? 0), (float) ($item->sunday_pay ?? 0),
                (float) ($item->holiday_pay ?? 0), 0, (float) ($item->allowance ?? 0),
                (float) ($item->gross_wage ?? 0), (float) ($item->tax ?? 0),
                (float) ($item->nasfund_ee ?? 0), 0, (float) ($item->loan_deduction ?? 0),
                (float) ($item->net_pay ?? 0),
            ]);
        }

        $rows->push([
            'TOTAL', '', 0, 0, 0, 0, 0, 0, 0, 0,
            (float) $items->sum('gross_wage'), (float) $items->sum('tax'),
            (float) $items->sum('nasfund_ee'), 0, (float) $items->sum('loan_deduction'),
            (float) $items->sum('net_pay'),
        ]);

        $this->sections[] = ['baseRow' => 1, 'payroll' => $payroll, 'items' => $items];

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 28, 'C' => 12, 'D' => 12, 'E' => 12, 'F' => 12,
            'G' => 12, 'H' => 12, 'I' => 12, 'J' => 10, 'K' => 14, 'L' => 12,
            'M' => 12, 'N' => 10, 'O' => 13, 'P' => 14, 'Q' => 4, 'R' => 4,
            'S' => 4, 'T' => 18, 'U' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach ($this->sections as $section) {
                    $this->renderPayrollSection($sheet, $section['baseRow'], $section['payroll'], $section['items']);
                }

                $sheet->getSheetView()->setZoomScale(85);
            },
        ];
    }

    private function renderPayrollSection($sheet, int $base, Payroll $payroll, $items): void
    {
        $headerRow1 = $base + 5;
        $headerRow2 = $base + 6;
        $firstDataRow = $base + 7;
        $totalRow = $firstDataRow + $items->count();
        $lastColumn = 'P';
        $company = $payroll->company;

        $sheet->mergeCells("C{$base}:F" . ($base + 2));
        $sheet->setCellValue("C{$base}", $company->name ?? 'Company');
        $sheet->getStyle("C{$base}")->getFont()->setName('Arial Black')->setSize(16);
        $sheet->getStyle("C{$base}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $this->addLogo($sheet, $company, $base);
        for ($row = $base; $row <= $base + 2; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(30);
        }

        $sheet->setCellValue('D' . ($base + 3), 'FN PAYROLL');
        $sheet->getStyle('D' . ($base + 3))->getFont()->setName('Arial Black')->setSize(11);
        $sheet->setCellValue('G' . ($base + 3), 'DATE');
        $sheet->getStyle('G' . ($base + 3))->getFont()->setBold(true);
        $payDate = $payroll->pay_date ?? $payroll->period_end;
        if ($payDate) {
            $sheet->setCellValue('H' . ($base + 3), Carbon::parse($payDate));
            $sheet->getStyle('H' . ($base + 3))->getNumberFormat()->setFormatCode('d-mmm');
        }

        $bankTotal = $items->filter(fn ($item) => strtolower($item->employee?->payment_method ?? 'bank') !== 'cash')->sum('net_pay');
        $cashTotal = $items->filter(fn ($item) => strtolower($item->employee?->payment_method ?? 'bank') === 'cash')->sum('net_pay');
        $sheet->setCellValue('O' . ($base + 3), 'Bank');
        $sheet->setCellValue('P' . ($base + 3), $bankTotal);
        $sheet->setCellValue('O' . ($base + 4), 'Cash');
        $sheet->setCellValue('P' . ($base + 4), $cashTotal);
        $sheet->getStyle('P' . ($base + 3) . ':P' . ($base + 4))->getNumberFormat()->setFormatCode($this->kinaFormat);

        $this->renderBankPayoutSummary($sheet, $base, $items, $payroll->period_end);

        foreach (['A', 'B', 'K', 'P'] as $column) {
            $sheet->mergeCells("{$column}{$headerRow1}:{$column}{$headerRow2}");
        }
        $sheet->mergeCells("C{$headerRow1}:J{$headerRow1}");
        $sheet->mergeCells("L{$headerRow1}:O{$headerRow1}");
        foreach ([
            "A{$headerRow1}" => 'EMP. NO.', "B{$headerRow1}" => 'Employee Name',
            "C{$headerRow1}" => 'GROSS', "K{$headerRow1}" => 'Gross Total',
            "L{$headerRow1}" => 'DEDUCTIONS', "P{$headerRow1}" => 'Net Pay',
        ] as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        foreach ([
            'C' => 'FN Rate', 'D' => 'Basic Pay', 'E' => 'Regular', 'F' => 'Over Time',
            'G' => 'Sunday OT', 'H' => 'Holiday Pay', 'I' => 'Final Pay', 'J' => 'Other',
            'L' => 'FN Tax', 'M' => 'NPF (6%)', 'N' => 'NCSL', 'O' => 'Cash Adv',
        ] as $column => $value) {
            $sheet->setCellValue("{$column}{$headerRow2}", $value);
        }

        $sheet->getStyle("A{$headerRow1}:P{$headerRow2}")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("A{$headerRow1}:P{$headerRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$headerRow1}:P{$headerRow2}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        if ($items->isNotEmpty()) {
            $sheet->getStyle("A{$firstDataRow}:P" . ($totalRow - 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('C2F9BF');
            foreach ($items->values() as $index => $item) {
                if (strtolower($item->employee?->payment_method ?? '') === 'cash') {
                    $row = $firstDataRow + $index;
                    $sheet->getStyle("A{$row}:P{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                }
            }
            $sheet->getStyle("C{$firstDataRow}:P" . ($totalRow - 1))->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $sheet->getStyle("A{$totalRow}:P{$totalRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$totalRow}:P{$totalRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        $sheet->getStyle("A{$totalRow}:P{$totalRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("C{$totalRow}:P{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
    }

    private function renderBankPayoutSummary($sheet, int $base, $items, $periodEnd): void
    {
        $national = $items->filter(fn ($item) => !str_contains(strtolower($item->employee?->nationality ?? 'national'), 'expat'))->sum('net_pay');
        $expat = $items->filter(fn ($item) => str_contains(strtolower($item->employee?->nationality ?? ''), 'expat'))->sum('net_pay');
        // Keep Main Office employees (including those without a branch
        // assignment) distinct from the individual branch payments.
        $mainOfficeLabels = ['', 'unassigned', 'main office', 'pom'];
        $branchNameFor = fn ($item) => $this->itemBranchNames[spl_object_id($item)]
            ?? $item->employee?->branchNameOn($periodEnd)
            ?? 'Unassigned';
        $mainOffice = $items
            ->filter(fn ($item) => in_array(
                strtolower(trim($branchNameFor($item))),
                $mainOfficeLabels,
                true
            ))
            ->sum('net_pay');
        $branches = $items
            ->reject(fn ($item) => in_array(
                strtolower(trim($branchNameFor($item))),
                $mainOfficeLabels,
                true
            ))
            ->groupBy($branchNameFor)
            ->map(fn ($group) => $group->sum('net_pay'))
            ->sortKeys();
        $row = $base + 1;
        $sheet->mergeCells("T{$row}:U{$row}");
        $sheet->setCellValue("T{$row}", 'BANK PAYOUT SUMMARY');
        $sheet->getStyle("T{$row}")->getFont()->setBold(true);
        $sheet->getStyle("T{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;
        $sheet->setCellValue("T{$row}", 'Column1');
        $sheet->setCellValue("U{$row}", 'Amount');
        $sheet->getStyle("T{$row}:U{$row}")->getFont()->setBold(true);
        foreach (['National' => $national, 'Expat' => $expat] as $label => $amount) {
            $row++;
            $sheet->setCellValue("T{$row}", $label);
            $sheet->setCellValue("U{$row}", $amount);
        }
        $row += 2;
        $sheet->setCellValue("T{$row}", 'By Branch');
        $sheet->getStyle("T{$row}")->getFont()->setItalic(true);
        $row++;
        $sheet->setCellValue("T{$row}", 'Main Office / Unassigned');
        $sheet->setCellValue("U{$row}", $mainOffice);
        foreach ($branches as $branch => $amount) {
            $row++;
            $sheet->setCellValue("T{$row}", $branch);
            $sheet->setCellValue("U{$row}", $amount);
        }
        $row += 2;
        $sheet->setCellValue("T{$row}", 'TOTAL');
        $sheet->setCellValue("U{$row}", $national + $expat);
        $sheet->getStyle("T{$row}:U{$row}")->getFont()->setBold(true);
        $sheet->getStyle("U" . ($base + 3) . ":U{$row}")->getNumberFormat()->setFormatCode($this->kinaFormat);
    }

    private function addLogo($sheet, $company, int $base): void
    {
        $logoPath = !empty($company?->logo_path) ? public_path(ltrim($company->logo_path, '/')) : null;
        if (!$logoPath || !file_exists($logoPath) || is_dir($logoPath)) {
            $logoPath = file_exists(public_path('images/logo.png')) ? public_path('images/logo.png') : public_path('images/logo.jpg');
        }
        if (!file_exists($logoPath) || is_dir($logoPath)) {
            return;
        }

        $drawing = new Drawing();
        $drawing->setName('Company Logo');
        $drawing->setPath($logoPath);
        $drawing->setResizeProportional(true);
        $drawing->setHeight(120);
        $drawing->setCoordinates("A{$base}");
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);
    }
}

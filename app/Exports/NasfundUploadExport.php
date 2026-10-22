<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/** XLSX layout matching the client's FN upload workbook. */
class NasfundUploadExport implements FromArray, WithEvents, WithTitle
{
    public function __construct(private $company, private $reportData)
    {
    }

    public function array(): array
    {
        $rows = [
            ['00', 'Employer Name', 'Employer Number', 'Date of Reg', 'Bank Statement Reference'],
            ['01', $this->company->name ?? '', '', '', ''],
            [
                '02', 'Client Number', 'Member Surname', 'Given Name(s)', 'Payroll Number',
                'Date of Birth', 'Gross Pay', 'Housing %', 'Mem Vol %', 'Emp Vol %',
                'Sal Sac %', 'Employer Cont', 'Member Cont', 'Housing Rep', 'Member Vol',
                'Employer Vol', 'Salary Sac', 'Total Cont', 'Date Employed', 'Company',
            ],
        ];

        foreach ($this->reportData as $item) {
            $row = count($rows) + 1;
            $rows[] = [
                '03',
                $item->nasfund_number ?? '',
                $item->last_name ?? '',
                $item->first_name ?? $item->full_name ?? '',
                $item->employee_number ?? '',
                $this->excelDate($item->date_of_birth ?? null),
                (float) ($item->gross_wage ?? 0),
                0, 0, 0, 0,
                "=ROUND(G{$row}*8.4%,2)",
                "=ROUND(G{$row}*6%,2)",
                "=ROUND(G{$row}*H{$row}%,2)",
                "=ROUND(G{$row}*I{$row}%,2)",
                "=ROUND(G{$row}*J{$row}%,2)",
                "=ROUND(G{$row}*K{$row}%,2)",
                "=SUM(L{$row}:Q{$row})",
                $this->excelDate($item->joining_date ?? null),
                $item->company_name ?? '',
            ];
        }

        $lastEmployeeRow = count($rows);
        $hasEmployees = $lastEmployeeRow > 3;
        $totals = [5, '', '', '', '', '', $hasEmployees ? "=SUM(G4:G{$lastEmployeeRow})" : 0];
        foreach (['H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'] as $column) {
            $totals[] = $hasEmployees ? "=SUM({$column}4:{$column}{$lastEmployeeRow})" : 0;
        }
        $totals[] = '';
        $totals[] = '';
        $rows[] = $totals;

        return $rows;
    }

    public function title(): string
    {
        return 'FN Upload';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:T3')->getFont()->setBold(true);
                $sheet->getStyle('A1:T3')->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE8EDF3');
                $sheet->getStyle("A1:T{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD0D7DE');
                $sheet->getStyle("A4:T{$lastRow}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("F4:F{$lastRow}")->getNumberFormat()->setFormatCode('dd/mm/yy');
                $sheet->getStyle("S4:S{$lastRow}")->getNumberFormat()->setFormatCode('dd/mm/yy');
                $sheet->getStyle("G4:G{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("L4:R{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("A{$lastRow}:T{$lastRow}")->getFont()->setBold(true);
                foreach ($this->reportData as $index => $item) {
                    $row = $index + 4;
                    $color = match ($item->company_code ?? '') {
                        'LKE-POM' => 'FFF1F7FF',
                        'LKE-LAE' => 'FFF1F9F2',
                        'ADF' => 'FFFFF8E8',
                        default => null,
                    };
                    if ($color) {
                        $sheet->getStyle("A{$row}:T{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB($color);
                    }
                }
                $sheet->freezePane('A4');
                $sheet->setAutoFilter('A3:T' . max(3, $lastRow - 1));
                foreach (range(1, 20) as $column) {
                    $letter = Coordinate::stringFromColumnIndex($column);
                    $sheet->getColumnDimension($letter)->setAutoSize(true);
                }
            },
        ];
    }

    private function excelDate($value): ?string
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d/m/y') : null;
    }
}

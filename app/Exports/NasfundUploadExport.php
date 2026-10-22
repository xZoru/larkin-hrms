<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/** Fills the client's FN upload workbook without rebuilding its layout or styles. */
class NasfundUploadExport
{
    public function __construct(private $company, private $reportData)
    {
    }

    public function download(string $filename)
    {
        $template = resource_path('templates/FN15 - Upload.xlsx');
        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getSheetByName('FN14') ?? $spreadsheet->getActiveSheet();
        $totalStyles = [];
        for ($column = 1; $column <= 20; $column++) {
            $letter = Coordinate::stringFromColumnIndex($column);
            $totalStyles[$letter] = clone $sheet->getStyle("{$letter}40");
        }

        // Keep the employer name, number, registration date, and bank reference
        // supplied in the client's template intact.

        $firstRow = 4;
        $templateLastEmployeeRow = 39;
        $totalRow = $firstRow + count($this->reportData);
        $lastRowToClear = max($templateLastEmployeeRow + 1, $totalRow);

        // Remove the sample employees while retaining all template formatting.
        for ($row = $firstRow; $row <= $lastRowToClear; $row++) {
            for ($column = 1; $column <= 20; $column++) {
                $cell = Coordinate::stringFromColumnIndex($column) . $row;
                $sheet->setCellValue($cell, null);
            }
        }

        $employeeOffset = 0;
        foreach ($this->reportData as $item) {
            $row = $firstRow + $employeeOffset++;
            if ($row > $templateLastEmployeeRow) {
                $this->copyRowStyle($sheet, $templateLastEmployeeRow, $row, 20);
            }

            $sheet->setCellValue("A{$row}", '03');
            $sheet->setCellValue("B{$row}", $item->nasfund_number ?? '');
            $sheet->setCellValue("C{$row}", $item->last_name ?? '');
            $sheet->setCellValue("D{$row}", $item->first_name ?? $item->full_name ?? '');
            $sheet->setCellValue("E{$row}", $item->employee_number ?? '');
            $sheet->setCellValue("F{$row}", $this->excelDate($item->date_of_birth ?? null));
            $sheet->setCellValue("G{$row}", (float) ($item->gross_pay ?? 0));
            foreach (['H', 'I', 'J', 'K'] as $column) {
                $sheet->setCellValue("{$column}{$row}", 0);
            }
            $sheet->setCellValue("L{$row}", "=ROUND(G{$row}*8.4%,2)");
            $sheet->setCellValue("M{$row}", "=ROUND(G{$row}*6%,2)");
            $sheet->setCellValue("N{$row}", "=ROUND(G{$row}*H{$row}%,2)");
            $sheet->setCellValue("O{$row}", "=ROUND(G{$row}*I{$row}%,2)");
            $sheet->setCellValue("P{$row}", "=ROUND(G{$row}*J{$row}%,2)");
            $sheet->setCellValue("Q{$row}", "=ROUND(G{$row}*K{$row}%,2)");
            $sheet->setCellValue("R{$row}", "=SUM(L{$row}:Q{$row})");
            $sheet->setCellValue("S{$row}", $this->excelDate($item->joining_date ?? null));

            // Keep the template's original A:S appearance, and tint only the
            // added source-company cell so consolidated rows remain identifiable.
            $sheet->setCellValue("T{$row}", $item->company_name ?? '');
            $sheet->duplicateStyle($sheet->getStyle("S{$row}"), "T{$row}");
            $color = match ($item->company_code ?? '') {
                'LKE-POM' => 'FFF1F7FF',
                'LKE-LAE' => 'FFF1F9F2',
                'ADF' => 'FFFFF8E8',
                default => null,
            };
            if ($color) {
                $sheet->getStyle("T{$row}")->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($color);
            }
        }

        // The supplied sheet has no Company column; append it without changing
        // any of the original upload columns or their formatting.
        $sheet->setCellValue('T3', 'Company');
        $sheet->duplicateStyle($sheet->getStyle('S3'), 'T3');
        $sheet->getColumnDimension('T')->setWidth(34);

        if (count($this->reportData) > 0) {
            $this->writeTotals($sheet, $totalRow, $firstRow, $totalRow - 1, $totalStyles);
        } else {
            $this->writeTotals($sheet, $firstRow, $firstRow, $firstRow - 1, $totalStyles);
            foreach (range('G', 'R') as $column) {
                $sheet->setCellValue("{$column}{$firstRow}", 0);
            }
        }

        $spreadsheet->getCalculationEngine()->disableCalculationCache();
        $spreadsheet->getProperties()->setTitle('NASFUND FN Upload');

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function writeTotals($sheet, int $targetRow, int $firstDataRow, int $lastDataRow, array $totalStyles): void
    {
        foreach ($totalStyles as $column => $style) {
            $sheet->duplicateStyle($style, "{$column}{$targetRow}");
        }

        $sheet->setCellValue("A{$targetRow}", 5);
        foreach (['G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'] as $column) {
            $sheet->setCellValue("{$column}{$targetRow}", $lastDataRow >= $firstDataRow
                ? "=SUM({$column}{$firstDataRow}:{$column}{$lastDataRow})"
                : 0);
        }
    }

    private function copyRowStyle($sheet, int $sourceRow, int $targetRow, int $lastColumn): void
    {
        for ($column = 1; $column <= $lastColumn; $column++) {
            $letter = Coordinate::stringFromColumnIndex($column);
            $sheet->duplicateStyle($sheet->getStyle("{$letter}{$sourceRow}"), "{$letter}{$targetRow}");
        }
        $sheet->getRowDimension($targetRow)->setRowHeight($sheet->getRowDimension($sourceRow)->getRowHeight());
    }

    private function excelDate($value): ?float
    {
        return $value ? ExcelDate::PHPToExcel(\Carbon\Carbon::parse($value)) : null;
    }
}

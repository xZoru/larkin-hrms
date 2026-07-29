<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TimesheetExport implements FromArray, WithEvents, ShouldAutoSize
{
    protected $fortnight;
    protected $period;
    protected $employees;
    protected $attendanceLogs;
    protected $summaries;
    protected $companyName;
    protected $dates;

    public function __construct($fortnight, $period, $employees, $attendanceLogs, $summaries, $companyName = 'Paragon Tech Limited')
    {
        $this->fortnight = $fortnight;
        $this->period = $period;
        $this->employees = $employees;
        $this->attendanceLogs = $attendanceLogs;
        $this->summaries = $summaries;
        $this->companyName = $companyName;
        
        $this->dates = [];
        for ($i = 0; $i < 14; $i++) {
            $this->dates[] = $this->period['start']->copy()->addDays($i);
        }
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->employees as $employee) {
            $employeeLogs = $this->attendanceLogs->get($employee->id, collect());
            $summary = $this->summaries->get($employee->id);
            
            $row = [
                $employee->employee_number ?? '',
                $employee->full_name ?? '',
                number_format($summary->regular_hours ?? 0, 2),
                number_format($summary->overtime_hours ?? 0, 2),
                number_format($summary->sunday_hours ?? 0, 2),
                number_format($summary->holiday_hours ?? 0, 2),
            ];

            foreach ($this->dates as $date) {
                $dateKey = $date->format('Y-m-d');
                $log = $employeeLogs->get($dateKey);
                $row[] = $log ? number_format($log->hours_worked, 2) : '0.00';
            }

            $data[] = $row;
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $totalColumns = 20; // A to T
                
                // ============================================
                // INSERT 6 HEADER ROWS
                // ============================================
                $sheet->insertNewRowBefore(1, 6);
                
                // ROW 1: Company Name (merged A1:T1)
                $sheet->mergeCells('A1:T1');
                $sheet->setCellValue('A1', $this->companyName);
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                // ROW 2: TIMESHEET (merged A2:T2)
                $sheet->mergeCells('A2:T2');
                $sheet->setCellValue('A2', 'TIMESHEET');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                // ROW 3: FROM ... TO ... (separate columns like template)
                $fromDate = $this->period['start']->format('j-M-y');
                $toDate = $this->period['end']->format('j-M-y');
                $sheet->setCellValue('A3', 'FROM');
                $sheet->setCellValue('B3', $fromDate);
                $sheet->setCellValue('C3', 'TO');
                $sheet->setCellValue('D3', $toDate);
                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                // ROW 4: Empty
                $sheet->getRowDimension(4)->setRowHeight(5);
                
                // ============================================
                // ROW 5: Day Names (Thu, Fri, Sat, Sun, etc.)
                // Starting from column G (7th column)
                // ============================================
                $dayStartColumn = 7; // Column G
                foreach ($this->dates as $index => $date) {
                    $columnIndex = $dayStartColumn + $index;
                    $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                    $dayName = $date->format('D'); // Thu, Fri, Sat, Sun, etc.
                    $sheet->setCellValue($columnLetter . '5', $dayName);
                    
                    // Sunday = red text (like template)
                    if ($date->isSunday()) {
                        $sheet->getStyle($columnLetter . '5')->applyFromArray([
                            'font' => ['color' => ['rgb' => 'FF0000'], 'bold' => true],
                        ]);
                    }
                }
                
                // ============================================
                // ROW 6: Headers - EMP NO, EMPLOYEE NAME, REG, OT Hrs(1.5), Sun OT(2.0), HOL
                // Then dates (09-Jul, 10-Jul, etc.) - NO YEAR
                // ============================================
                $sheet->setCellValue('A6', 'EMP NO');
                $sheet->setCellValue('B6', 'EMPLOYEE NAME');
                $sheet->setCellValue('C6', 'REG');
                $sheet->setCellValue('D6', "OT Hrs\n(1.5)");
                $sheet->setCellValue('E6', "Sun OT\n(2.0)");
                $sheet->setCellValue('F6', 'HOL');
                
                foreach ($this->dates as $index => $date) {
                    $columnIndex = $dayStartColumn + $index;
                    $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                    // Format: 09-Jul (no year, like template)
                    $sheet->setCellValue($columnLetter . '6', $date->format('d-M'));
                    
                    // Sunday = red background with white text (like template)
                    if ($date->isSunday()) {
                        $sheet->getStyle($columnLetter . '6')->applyFromArray([
                            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FF0000']],
                        ]);
                    }
                }
                
                // Style header row (row 6) - gray background like template
                $sheet->getStyle('A6:T6')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'D9D9D9']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // Wrap text in OT Hrs and Sun OT columns
                $sheet->getStyle('D6:E6')->getAlignment()->setWrapText(true);
                
                // ============================================
                // DATA ROWS (row 7+)
                // ============================================
                $dataStartRow = 7;
                $dataEndRow = $sheet->getHighestRow();
                
                // Apply borders to all data
                $sheet->getStyle('A' . $dataStartRow . ':T' . $dataEndRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Employee Name column (B) left aligned
                $sheet->getStyle('B' . $dataStartRow . ':B' . $dataEndRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                // EMP NO column (A) bold
                $sheet->getStyle('A' . $dataStartRow . ':A' . $dataEndRow)->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                
                // ============================================
                // SUNDAY DATA ROWS (light red background)
                // ============================================
                foreach ($this->dates as $index => $date) {
                    if ($date->isSunday()) {
                        $columnIndex = $dayStartColumn + $index;
                        $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                        
                        for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                            $sheet->getStyle($columnLetter . $row)->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFE6E6']],
                            ]);
                        }
                    }
                }
                
                // ============================================
                // COLUMN WIDTHS
                // ============================================
                $sheet->getColumnDimension('A')->setWidth(12);  // EMP NO
                $sheet->getColumnDimension('B')->setWidth(25);  // EMPLOYEE NAME
                $sheet->getColumnDimension('C')->setWidth(8);   // REG
                $sheet->getColumnDimension('D')->setWidth(12);  // OT Hrs (1.5)
                $sheet->getColumnDimension('E')->setWidth(12);  // Sun OT (2.0)
                $sheet->getColumnDimension('F')->setWidth(8);   // HOL
                
                // Day columns (G to T)
                for ($i = 0; $i < 14; $i++) {
                    $columnIndex = $dayStartColumn + $i;
                    $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->getColumnDimension($columnLetter)->setWidth(10);
                }
            },
        ];
    }
}
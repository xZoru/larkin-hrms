<?php

namespace App\Exports;

/** Builds the NASFUND upload CSV in the client's FN upload layout. */
class NasfundUploadCsvExport
{
    public function __construct(private $reportData)
    {
    }

    public function download(string $filename)
    {
        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            $write = static fn (array $row) => fputcsv($output, $row);
            $empty = array_fill(0, 14, '');

            $write(array_merge(['00', 'Employer Name', 'Employer Number', 'Date of Reg', 'Bank Statement Reference'], $empty));
            $write(array_merge(['01', 'LARKIN ENTERPRISES LTD', '131934', '22/08/2019', '24092026131934'], $empty));
            $write([
                '02', 'Client Number', 'Member Surname', 'Given Name(s)', 'Payroll Number',
                'Date of Birth', 'Gross Pay', 'Housing %', 'Mem Vol %', 'Emp Vol %', 'Sal Sac %',
                'Employer Cont', 'Member Cont', 'Housing Rep', 'Member Vol', 'Employer Vol',
                'Salary Sac', 'Total Cont', 'Date Employed',
            ]);

            $grossTotal = $employerTotal = $memberTotal = $contributionTotal = 0.0;
            foreach ($this->reportData as $item) {
                $gross = (float) ($item->gross_pay ?? 0);
                $employer = (float) ($item->er_contribution ?? 0);
                $member = (float) ($item->ee_contribution ?? 0);
                $total = $employer + $member;
                $grossTotal += $gross;
                $employerTotal += $employer;
                $memberTotal += $member;
                $contributionTotal += $total;

                $write([
                    '03', $item->nasfund_number ?? '', $item->last_name ?? '',
                    $item->first_name ?? $item->full_name ?? '', $this->payrollNumber($item->employee_number ?? ''),
                    $this->date($item->date_of_birth ?? null), $this->grossMoney($gross),
                    '', '', '', '', $this->contributionMoney($employer), $this->contributionMoney($member),
                    '0', '0', '0', '0', $this->contributionMoney($total),
                    $this->date($item->joining_date ?? null),
                ]);
            }

            $write([
                '05', '', '', '', '', '', $this->totalMoney($grossTotal), '-', '-', '-', '-',
                $this->totalMoney($employerTotal), $this->totalMoney($memberTotal), '-', '-', '-', '-',
                $this->totalMoney($contributionTotal), '',
            ]);
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function date($value): string
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '';
    }

    private function payrollNumber($value): string
    {
        $value = (string) $value;

        return preg_match('/^LKP-(\d+)$/i', $value, $matches) ? $matches[1] : $value;
    }

    private function grossMoney(float $value): string
    {
        return ' ' . number_format($value, 2, '.', '') . ' ';
    }

    private function contributionMoney(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function totalMoney(float $value): string
    {
        return ' ' . number_format($value, 2, '.', ',') . ' ';
    }
}

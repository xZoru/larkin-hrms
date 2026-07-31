<?php

namespace App\Services;

use App\Models\ABABatch;
use App\Models\Company;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ABAGeneratorService
{
    /**
     * Generate an ABA file for a payroll.
     */
    public function generate(
        Payroll $payroll,
        Company $company,
        array $bankDetails
    ): ABABatch {
        /*
         * 1. Get regular payroll items with employees and bank accounts.
         */
        $regularItems = $payroll->items()
            ->with(['employee.bankAccounts'])
            ->whereNotNull('employee_id')
            ->where('net_pay', '>', 0)
            ->get()
            ->filter(function ($item) {
                if (!$item->employee || !$item->employee->bankAccounts) {
                    return false;
                }

                $activeAccount = $item->employee->bankAccounts
                    ->where('is_active', true)
                    ->first()
                    ?? $item->employee->bankAccounts->first();

                return $activeAccount !== null;
            });

        /*
         * 2. Get manual payment entries.
         */
        $manualItems = $payroll->items()
            ->whereNull('employee_id')
            ->where('details->type', 'manual_entry')
            ->where('net_pay', '>', 0)
            ->get()
            ->map(function ($item) {
                $details = is_array($item->details)
                    ? $item->details
                    : [];

                $item->virtual_bank_account = (object) [
                    'bsb_code' => $details['bsb'] ?? '',
                    'account_number' => $details['account_number'] ?? '',
                    'account_name' => $details['account_name']
                        ?? 'MANUAL ENTRY',
                    'details' => $details,
                ];

                return $item;
            });

        /*
         * 3. Merge regular and manual payments.
         */
        $payrollItems = $regularItems
            ->merge($manualItems)
            ->values();

        if ($payrollItems->isEmpty()) {
            throw new RuntimeException(
                'No employees or manual entries with valid bank details '
                . 'were found for this payroll.'
            );
        }

        /*
         * 4. Generate ABA text.
         */
        $content = $this->generateABAContent(
            $payrollItems,
            $payroll,
            $company,
            $bankDetails
        );

        $batchNumber = $this->generateBatchNumber();
        $filename = 'ABA_' . $batchNumber . '.aba';

        $paymentDate = Carbon::parse(
            $bankDetails['payment_date']
                ?? now()->format('Y-m-d')
        );

        /*
         * 5. Save batch information.
         */
        $batch = ABABatch::create([
            'company_id' => $company->id,
            'payroll_id' => $payroll->id,
            'batch_number' => $batchNumber,

            'bank_name' => $bankDetails['bank_name']
                ?? $company->bank_name
                ?? 'BSP Bank',

            'bank_code' => $bankDetails['bank_code']
                ?? 'BSP',

            'apca_user_id' => $bankDetails['apca_user_id']
                ?? $company->apca_user_id
                ?? '000001',

            'bsb_number' => $bankDetails['bsb_number']
                ?? $company->bsb_code
                ?? '088-950',

            'account_number' => $bankDetails['account_number']
                ?? $company->bank_account_number
                ?? '7009276416',

            'account_name' => $bankDetails['account_name']
                ?? $company->bank_account_name
                ?? $company->name,

            'total_amount' => $payrollItems->sum('net_pay'),

            /*
             * This database value represents employee/manual payments only.
             * The ABA footer separately includes the balancing record.
             */
            'total_transactions' => $payrollItems->count(),

            'processing_date' => $paymentDate,
            'status' => 'generated',
            'generated_by' => auth()->id(),

            'metadata' => [
                'payment_type' => $bankDetails['payment_type']
                    ?? 'SALARY',

                'debit_description' => $bankDetails[
                    'debit_description'
                ] ?? 'PAYROLL',

                'payment_date' => $paymentDate->format('Y-m-d'),
            ],

            'filename' => $filename,
        ]);

        /*
         * 6. Write the ABA file.
         */
        $path = 'aba/' . $filename;

        Storage::disk('public')->put($path, $content);

        $batch->update([
            'file_path' => $path,
        ]);

        return $batch;
    }

    /**
     * Generate all ABA records.
     */
    private function generateABAContent(
        $payrollItems,
        Payroll $payroll,
        Company $company,
        array $bankDetails
    ): string {
        $lines = [];

        /*
         * Fixed 46-character trace/remitter section.
         */
        $tracerReference = $this->formatTracerReference(
            $company,
            $bankDetails
        );

        /*
         * Type 0 header record.
         */
        $lines[] = $this->formatHeader(
            $company,
            $bankDetails
        );

        $transactionCount = 0;
        $totalAmountToea = 0;

        /*
         * Type 1 employee/manual payment records.
         */
        foreach ($payrollItems as $item) {
            $isManual = isset($item->virtual_bank_account);

            if ($isManual) {
                $bankAccount = $item->virtual_bank_account;
                $employee = null;
            } else {
                $employee = $item->employee;

                $bankAccount = $employee->bankAccounts
                    ->where('is_active', true)
                    ->first()
                    ?? $employee->bankAccounts->first();
            }

            if (!$bankAccount) {
                continue;
            }

            $amountToea = $this->moneyToToea(
                $item->net_pay
            );

            $lines[] = $this->formatDetailRecord(
                bankAccount: $bankAccount,
                employee: $employee,
                amountToea: $amountToea,
                debitDescription: $bankDetails[
                    'debit_description'
                ] ?? '',
                tracerReference: $tracerReference,
                payroll: $payroll,
                isManual: $isManual
            );

            $transactionCount++;
            $totalAmountToea += $amountToea;
        }

        if ($transactionCount === 0) {
            throw new RuntimeException(
                'No valid payment records were generated.'
            );
        }

        /*
         * Type 1 balancing/contra record.
         */
        $lines[] = $this->formatBalancingRecord(
            company: $company,
            bankDetails: $bankDetails,
            totalAmountToea: $totalAmountToea,
            tracerReference: $tracerReference,
            payroll: $payroll
        );

        /*
         * Footer count includes all Type 1 records:
         * employee/manual records + one balancing record.
         */
        $transactionCount++;

        /*
         * Type 7 footer record.
         */
        $lines[] = $this->formatTrailerRecord(
            transactionCount: $transactionCount,
            totalAmountToea: $totalAmountToea
        );

        /*
         * Final strict validation.
         */
        foreach ($lines as $index => $line) {
            $this->validateRecord(
                $line,
                'ABA line ' . ($index + 1)
            );
        }

        /*
         * Use Windows CRLF line endings and include a final CRLF.
         */
        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * Create the 46-character trace/remitter field.
     *
     * Structure:
     * BSB                    7
     * Fixed zeroes           5
     * Company account       10
     * Company name          16
     * Fixed zeroes           8
     * Total                 46
     */
    private function formatTracerReference(
        Company $company,
        array $bankDetails
    ): string {
        $bsb = $this->formatBsb(
            $bankDetails['bsb_number']
                ?? $company->bsb_code
                ?? '088950'
        );

        $account = $bankDetails['account_number']
            ?? $company->bank_account_number
            ?? '7009276416';

        $account = $this->digitsOnly($account);

        if ($account === '') {
            throw new RuntimeException(
                'Company trace account number is missing.'
            );
        }

        if (strlen($account) > 10) {
            throw new RuntimeException(
                'Company trace account number must not exceed '
                . '10 digits. Current value: ' . $account
            );
        }

        $traceAccount = str_pad(
            $account,
            10,
            '0',
            STR_PAD_LEFT
        );

        $companyName = $bankDetails['account_name']
            ?? $company->bank_account_name
            ?? $company->name
            ?? 'LARKIN ENTERPRISES LIMITED';

        $trace =
            $bsb
            . '00000'
            . $traceAccount
            . $this->formatText($companyName, 16)
            . '00000000';

        if (strlen($trace) !== 46) {
            throw new RuntimeException(
                'Trace reference must be exactly 46 characters. '
                . 'Actual length: ' . strlen($trace)
            );
        }

        return $trace;
    }

    /**
     * Record Type 0: Descriptive header record.
     *
     * Structure:
     * Record type             1
     * Filler                 17
     * Reel sequence           2
     * Bank code               3
     * Filler                  7
     * User/company name      26
     * User ID                 6
     * Description            12
     * Processing date         8
     * Filler                 50
     * Total                 132
     */
    private function formatHeader(
        Company $company,
        array $bankDetails
    ): string {
        $bankCode = strtoupper(
            $bankDetails['bank_code'] ?? 'BSP'
        );

        $userName = $bankDetails['account_name']
            ?? $company->bank_account_name
            ?? $company->name
            ?? 'LARKIN ENTERPRISES LIMITED';

        $apcaId = $bankDetails['apca_user_id']
            ?? $company->apca_user_id
            ?? '000001';

        $apcaId = $this->digitsOnly($apcaId);

        if (strlen($apcaId) > 6) {
            throw new RuntimeException(
                'ABA/APCA User ID must not exceed six digits.'
            );
        }

        $description = $bankDetails['payment_type']
            ?? 'SALARY';

        $paymentDate = Carbon::parse(
            $bankDetails['payment_date']
                ?? now()->format('Y-m-d')
        );

        $line =
            '0'
            . str_repeat(' ', 17)
            . '01'
            . $this->formatText($bankCode, 3)
            . str_repeat(' ', 7)
            . $this->formatText($userName, 26)
            . str_pad($apcaId, 6, '0', STR_PAD_LEFT)
            . $this->formatText($description, 12)
            . $paymentDate->format('Ymd')
            . str_repeat(' ', 50);

        $this->validateRecord(
            $line,
            'Header record'
        );

        return $line;
    }

    /**
     * Record Type 1: Employee or manual payment record.
     *
     * Structure:
     * Record type             1
     * BSB                     7
     * Account number         15
     * Indicator               1
     * Transaction code        2
     * Amount                  10
     * Account name           32
     * Lodgement reference    18
     * Trace/remitter field   46
     * Total                 132
     */
    private function formatDetailRecord(
        object $bankAccount,
        $employee,
        int $amountToea,
        string $debitDescription,
        string $tracerReference,
        Payroll $payroll,
        bool $isManual = false
    ): string {
        $bsb = $this->formatBsb(
            $bankAccount->bsb_code ?? ''
        );

        $accountNumber = $this->digitsOnly(
            $bankAccount->account_number ?? ''
        );

        if ($accountNumber === '') {
            throw new RuntimeException(
                'Employee payment account number is missing.'
            );
        }

        if (strlen($accountNumber) > 15) {
            throw new RuntimeException(
                'Employee payment account number exceeds '
                . '15 digits: ' . $accountNumber
            );
        }

        $accountNumber = str_pad(
            $accountNumber,
            15,
            '0',
            STR_PAD_LEFT
        );

        if ($isManual && isset($bankAccount->details)) {
            $details = is_array($bankAccount->details)
                ? $bankAccount->details
                : [];

            $accountName = $details['account_name']
                ?? $bankAccount->account_name
                ?? 'MANUAL ENTRY';
        } else {
            $accountName = $bankAccount->account_name
                ?? $employee?->full_name
                ?? '';
        }

        if (trim((string) $accountName) === '') {
            throw new RuntimeException(
                'Employee payment account name is missing.'
            );
        }

        /*
         * Use the supplied debit description when available.
         * Otherwise use the fortnight number.
         */
        $reference = trim($debitDescription);

        if ($reference === '') {
            $reference = 'FN'
                . $payroll->fortnight_number;
        }

        $line =
            '1'
            . $bsb
            . $accountNumber
            . ' '
            . '53'
            . $this->formatNumericField(
                $amountToea,
                10,
                'Employee payment amount'
            )
            . $this->formatText($accountName, 32)
            . $this->formatText($reference, 18)
            . $tracerReference;

        $this->validateRecord(
            $line,
            'Employee payment record'
        );

        return $line;
    }

    /**
     * Record Type 1: Company balancing/contra record.
     */
    private function formatBalancingRecord(
        Company $company,
        array $bankDetails,
        int $totalAmountToea,
        string $tracerReference,
        Payroll $payroll
    ): string {
        $bsb = $this->formatBsb(
            $bankDetails['bsb_number']
                ?? $company->bsb_code
                ?? '088950'
        );

        $account = $bankDetails['account_number']
            ?? $company->bank_account_number
            ?? '7009276416';

        $account = $this->digitsOnly($account);

        if ($account === '') {
            throw new RuntimeException(
                'Company balancing account number is missing.'
            );
        }

        if (strlen($account) > 15) {
            throw new RuntimeException(
                'Company balancing account number exceeds '
                . '15 digits: ' . $account
            );
        }

        $account = str_pad(
            $account,
            15,
            '0',
            STR_PAD_LEFT
        );

        $companyName = $bankDetails['account_name']
            ?? $company->bank_account_name
            ?? $company->name
            ?? 'LARKIN ENTERPRISES LIMITED';

        $reference = $bankDetails['tracer_reference']
            ?? $bankDetails['debit_description']
            ?? 'FN' . $payroll->fortnight_number;

        $line =
            '1'
            . $bsb
            . $account
            . ' '
            . '13'
            . $this->formatNumericField(
                $totalAmountToea,
                10,
                'Balancing amount'
            )
            . $this->formatText($companyName, 32)
            . $this->formatText($reference, 18)
            . $tracerReference;

        $this->validateRecord(
            $line,
            'Balancing record'
        );

        return $line;
    }

    /**
     * Record Type 7: File trailer/footer.
     *
     * Structure:
     * Record type             1
     * Fixed BSB               7
     * Filler                 12
     * Net total              10
     * Credit total           10
     * Debit total            10
     * Filler                 24
     * Record count            6
     * Filler                 52
     * Total                 132
     */
    private function formatTrailerRecord(
        int $transactionCount,
        int $totalAmountToea
    ): string {
        $line =
            '7'
            . '999-999'
            . str_repeat(' ', 12)
            . $this->formatNumericField(
                0,
                10,
                'Footer net total'
            )
            . $this->formatNumericField(
                $totalAmountToea,
                10,
                'Footer credit total'
            )
            . $this->formatNumericField(
                $totalAmountToea,
                10,
                'Footer debit total'
            )
            . str_repeat(' ', 24)
            . $this->formatNumericField(
                $transactionCount,
                6,
                'Footer transaction count'
            )
            . str_repeat(' ', 52);

        $this->validateRecord(
            $line,
            'Footer record'
        );

        return $line;
    }

    /**
     * Convert a BSB/branch code to the fixed format 000-000.
     */
    private function formatBsb(?string $value): string
    {
        $digits = $this->digitsOnly($value);

        if ($digits === '') {
            throw new RuntimeException(
                'BSB/branch code is missing.'
            );
        }

        /*
         * Retain the final six digits when a value contains
         * unexpected prefixes.
         */
        if (strlen($digits) > 6) {
            $digits = substr($digits, -6);
        }

        $digits = str_pad(
            $digits,
            6,
            '0',
            STR_PAD_LEFT
        );

        return substr($digits, 0, 3)
            . '-'
            . substr($digits, 3, 3);
    }

    /**
     * Format a fixed-width text field.
     */
    private function formatText(
        ?string $value,
        int $width
    ): string {
        $value = strtoupper(
            trim((string) $value)
        );

        /*
         * ABA files should contain ordinary single-byte text.
         */
        $converted = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $value
        );

        if ($converted !== false) {
            $value = $converted;
        }

        /*
         * Remove line breaks, tabs and repeated whitespace.
         */
        $value = preg_replace(
            '/[\r\n\t]+/',
            ' ',
            $value
        );

        $value = preg_replace(
            '/\s+/',
            ' ',
            $value
        );

        return str_pad(
            substr($value, 0, $width),
            $width,
            ' ',
            STR_PAD_RIGHT
        );
    }

    /**
     * Format an integer as a zero-filled numeric field.
     */
    private function formatNumericField(
        int $value,
        int $width,
        string $fieldName
    ): string {
        if ($value < 0) {
            throw new RuntimeException(
                "{$fieldName} cannot be negative."
            );
        }

        $stringValue = (string) $value;

        if (strlen($stringValue) > $width) {
            throw new RuntimeException(
                "{$fieldName} exceeds its {$width}-digit ABA field."
            );
        }

        return str_pad(
            $stringValue,
            $width,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Convert Kina to an integer number of toea.
     */
    private function moneyToToea($amount): int
    {
        $amountToea = (int) round(
            ((float) $amount) * 100
        );

        if ($amountToea <= 0) {
            throw new RuntimeException(
                'ABA payment amount must be greater than zero.'
            );
        }

        if ($amountToea > 9999999999) {
            throw new RuntimeException(
                'ABA payment amount exceeds the 10-digit amount field.'
            );
        }

        return $amountToea;
    }

    /**
     * Return only numeric characters.
     */
    private function digitsOnly($value): string
    {
        return preg_replace(
            '/\D/',
            '',
            (string) $value
        ) ?? '';
    }

    /**
     * Validate one complete ABA record.
     */
    private function validateRecord(
        string $record,
        string $label
    ): void {
        if (
            str_contains($record, "\r")
            || str_contains($record, "\n")
        ) {
            throw new RuntimeException(
                "{$label} contains an invalid line break."
            );
        }

        $length = strlen($record);

        if ($length !== 132) {
            throw new RuntimeException(
                "{$label} must be exactly 132 characters. "
                . "Actual length: {$length}."
            );
        }
    }

    /**
     * Generate an internal batch identifier.
     */
    private function generateBatchNumber(): string
    {
        return 'ABA-'
            . date('Ymd')
            . '-'
            . strtoupper(Str::random(6));
    }

    /**
     * Check whether an ABA batch already exists.
     */
    public function existsForPayroll($payrollId): bool
    {
        return ABABatch::where(
            'payroll_id',
            $payrollId
        )->exists();
    }

    /**
     * Retrieve ABA generation history.
     */
    public function getHistory($companyId = null)
    {
        $query = ABABatch::with([
            'company',
            'payroll',
            'generator',
        ]);

        if ($companyId !== null) {
            $query->where(
                'company_id',
                $companyId
            );
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Read generated ABA file contents.
     */
    public function getContent($batchId): string
    {
        $batch = ABABatch::findOrFail($batchId);

        if (
            !$batch->file_path
            || !Storage::disk('public')->exists(
                $batch->file_path
            )
        ) {
            throw new RuntimeException(
                'ABA file not found.'
            );
        }

        return Storage::disk('public')->get(
            $batch->file_path
        );
    }

    /**
     * Download a generated ABA file.
     */
    public function download($batchId)
    {
        $batch = ABABatch::findOrFail($batchId);

        if (
            !$batch->file_path
            || !Storage::disk('public')->exists(
                $batch->file_path
            )
        ) {
            throw new RuntimeException(
                'ABA file not found.'
            );
        }

        $content = Storage::disk('public')->get(
            $batch->file_path
        );

        $filename = $batch->filename
            ?? 'ABA_' . $batch->batch_number . '.aba';

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=us-ascii',
            'Content-Disposition' =>
                'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
            'Cache-Control' =>
                'no-store, no-cache, must-revalidate',
        ]);
    }
}
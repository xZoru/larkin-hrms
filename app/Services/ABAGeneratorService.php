<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\Company;
use App\Models\ABABatch;
use App\Models\PayrollItem;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ABAGeneratorService
{
    /**
     * Generate ABA file for a payroll
     */
    public function generate(Payroll $payroll, Company $company, array $bankDetails)
    {
        // Get bankable employees
        $payrollItems = $payroll->items()
            ->with(['employee.bankAccounts' => function($query) {
                $query->where('is_active', true)
                    ->orderBy('is_preferred', 'desc')
                    ->orderBy('priority', 'asc');
            }])
            ->where('net_pay', '>', 0)
            ->get()
            ->filter(function($item) {
                return $item->employee && 
                    $item->employee->bankAccounts && 
                    $item->employee->bankAccounts->isNotEmpty();
            });

        if ($payrollItems->isEmpty()) {
            throw new \Exception('No employees with valid bank details found for this payroll.');
        }

        // Generate ABA content
        $content = $this->generateABAContent($payrollItems, $payroll, $company, $bankDetails);
        
        // Generate batch number
        $batchNumber = $this->generateBatchNumber();
        $filename = 'ABA_' . $batchNumber . '.aba';
        
        // Create batch record
        $batch = ABABatch::create([
            'company_id' => $company->id,
            'payroll_id' => $payroll->id,
            'batch_number' => $batchNumber,
            'bank_name' => $bankDetails['bank_name'] ?? $company->bank_name ?? 'BSP Bank',
            'bsb_number' => $bankDetails['bsb_number'] ?? $company->bsb_code ?? '088-950',
            'account_number' => $bankDetails['account_number'] ?? $company->bank_account_number ?? '7009276416',
            'account_name' => $bankDetails['account_name'] ?? $company->bank_account_name ?? $company->name,
            'total_amount' => $payrollItems->sum('net_pay'),
            'total_transactions' => $payrollItems->count(),
            'processing_date' => now(),
            'status' => 'generated',
            'generated_by' => auth()->id(),
            'metadata' => [
                'payment_type' => $bankDetails['payment_type'] ?? 'SALARY',
                'debit_description' => $bankDetails['debit_description'] ?? 'PAYROLL',
                'payment_date' => $bankDetails['payment_date'] ?? now()->format('Y-m-d'),
            ],
            'filename' => $filename,
        ]);

        // Save file
        $path = 'aba/' . $filename;
        Storage::disk('public')->put($path, $content);
        
        $batch->update([
            'file_path' => $path,
        ]);

        return $batch;
    }

    /**
     * Generate ABA content with exact 120-character fixed-width format
     */
    private function generateABAContent($payrollItems, $payroll, $company, $bankDetails)
    {
        $lines = [];
        
        // ============================================================
        // LINE 1: HEADER RECORD (120 characters)
        // ============================================================
        $header = $this->formatHeader($company, $bankDetails);
        $lines[] = $this->ensure120Chars($header);

        // ============================================================
        // LINE 2 - N: DETAIL RECORDS (120 characters each)
        // ============================================================
        $transactionCount = 0;
        $totalAmount = 0;
        $totalCreditAmount = 0;

        foreach ($payrollItems as $item) {
            $employee = $item->employee;
            $bankAccount = $employee->bankAccounts()->where('is_active', true)->first();
            
            if (!$bankAccount) {
                continue;
            }

            $detail = $this->formatDetailRecord(
                $bankAccount,
                $employee,
                $item->net_pay,
                $bankDetails['payment_type'] ?? 'SALARY'
            );
            
            $lines[] = $this->ensure120Chars($detail);
            
            $transactionCount++;
            $totalAmount += $item->net_pay;
            $totalCreditAmount += $item->net_pay;
        }

        // ============================================================
        // LINE N+1: TRAILER RECORD (120 characters)
        // ============================================================
        $trailer = $this->formatTrailerRecord($transactionCount, $totalAmount, $totalCreditAmount);
        $lines[] = $this->ensure120Chars($trailer);

        // Join lines with CRLF (Windows line endings)
        return implode("\r\n", $lines);
    }

    /**
     * Format Header Record - 120 characters
     * 
     * Positions (1-based):
     * 1: '0' (Record Type)
     * 2-18: 17 blank spaces
     * 19-20: '01' (Reel Sequence Number)
     * 21-23: 'BSP' (Name of User's Financial Institution)
     * 24-30: 7 blank spaces
     * 31-56: User Name (26 chars, left-justified)
     * 57-62: '000000' (User Identification Number)
     * 63-74: Description (12 chars, left-justified)
     * 75-80: Date (DDMMYY)
     * 81-120: 40 blank trailing spaces
     */
    private function formatHeader($company, $bankDetails)
    {
        $line = '';
        
        // Position 1: Record Type
        $line .= '0';
        
        // Position 2-18: 17 blank spaces
        $line .= str_repeat(' ', 17);
        
        // Position 19-20: Reel Sequence Number
        $line .= '01';
        
        // Position 21-23: Financial Institution Code
        $line .= 'BSP';
        
        // Position 24-30: 7 blank spaces
        $line .= str_repeat(' ', 7);
        
        // Position 31-56: User Name (26 chars, left-justified)
        $userName = $bankDetails['account_name'] ?? $company->name ?? 'LARKIN ENTERPRISES LTD';
        $userName = strtoupper(substr($userName, 0, 26));
        $line .= str_pad($userName, 26, ' ', STR_PAD_RIGHT);
        
        // Position 57-62: User Identification Number (6 digits)
        $line .= '000000';
        
        // Position 63-74: Description (12 chars, left-justified)
        $description = $bankDetails['payment_type'] ?? 'SALARY';
        $description = strtoupper(substr($description, 0, 12));
        $line .= str_pad($description, 12, ' ', STR_PAD_RIGHT);
        
        // Position 75-80: Date (DDMMYY)
        $date = $bankDetails['payment_date'] ?? now()->format('Y-m-d');
        $dateObj = \Carbon\Carbon::parse($date);
        $line .= $dateObj->format('dmy');
        
        // Position 81-120: 40 blank trailing spaces
        $line .= str_repeat(' ', 40);
        
        return $line;
    }

    /**
     * Format Detail Record - 120 characters
     * 
     * Positions (1-based):
     * 1: '1' (Record Type)
     * 2-8: BSB with hyphen (XXX-XXX)
     * 9-17: Account Number (9 chars, right-aligned, space-padded)
     * 18: ' ' (1 blank space indicator)
     * 19-20: Transaction Code ('53' for Salary)
     * 21-30: Amount in cents (10 digits, right-aligned, zero-padded)
     * 31-62: Account Name (32 chars, left-aligned, uppercase)
     * 63-120: Pad remaining fields with spaces (58 chars)
     */
    private function formatDetailRecord($bankAccount, $employee, $amount, $paymentType)
    {
        $line = '';
        
        // Position 1: Record Type
        $line .= '1';
        
        // Position 2-8: BSB with hyphen (XXX-XXX)
        $bsb = $bankAccount->bsb_code ?? '';
        $bsb = preg_replace('/[^0-9]/', '', $bsb);
        $bsb = str_pad(substr($bsb, 0, 6), 6, '0', STR_PAD_LEFT);
        $line .= substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        
        // Position 9-17: Account Number (9 chars, right-aligned, space-padded)
        $accountNumber = $bankAccount->account_number ?? '';
        $accountNumber = substr($accountNumber, 0, 9);
        $line .= str_pad($accountNumber, 9, ' ', STR_PAD_LEFT);
        
        // Position 18: 1 blank space indicator
        $line .= ' ';
        
        // Position 19-20: Transaction Code ('53' for Salary)
        $txnCode = $this->getTransactionCode($paymentType);
        $line .= $txnCode;
        
        // Position 21-30: Amount in cents (10 digits, right-aligned, zero-padded)
        $amountCents = round($amount * 100);
        $line .= str_pad($amountCents, 10, '0', STR_PAD_LEFT);
        
        // Position 31-62: Account Name (32 chars, left-aligned, uppercase)
        $accountName = $bankAccount->account_name ?? $employee->full_name ?? '';
        $accountName = strtoupper(substr($accountName, 0, 32));
        $line .= str_pad($accountName, 32, ' ', STR_PAD_RIGHT);
        
        // Position 63-120: Pad remaining with spaces (58 chars)
        $line .= str_repeat(' ', 58);
        
        return $line;
    }

    /**
     * Get transaction code based on payment type
     */
    private function getTransactionCode($paymentType)
    {
        $codes = [
            'SALARY' => '53',
            'WAGES' => '53',
            'COMMISSION' => '54',
            'BONUS' => '55',
            'SUPERANNUATION' => '56',
            'PENSION' => '57',
            'DIVIDEND' => '58',
            'INTEREST' => '59',
        ];
        
        return $codes[strtoupper($paymentType)] ?? '53';
    }

    /**
     * Format Trailer Record - 120 characters
     * 
     * Positions (1-based):
     * 1: '7' (Record Type)
     * 2-8: '999-999' (BSB Format Filler)
     * 9-20: 12 blank spaces
     * 21-30: Net Total Amount (10 digits of cents, zero-padded)
     * 31-40: Credit Total Amount (10 digits of cents, zero-padded)
     * 41-50: Debit Total Amount (10 digits of cents, zero-padded)
     * 51-74: 24 blank spaces
     * 75-80: Total count of Type 1 records (6 digits, zero-padded)
     * 81-120: 40 blank trailing spaces
     */
    private function formatTrailerRecord($transactionCount, $totalAmount, $totalCreditAmount)
    {
        $line = '';
        
        // Position 1: Record Type
        $line .= '7';
        
        // Position 2-8: BSB Format Filler
        $line .= '999-999';
        
        // Position 9-20: 12 blank spaces
        $line .= str_repeat(' ', 12);
        
        // Position 21-30: Net Total Amount (10 digits of cents, zero-padded)
        $totalAmountCents = round($totalAmount * 100);
        $line .= str_pad($totalAmountCents, 10, '0', STR_PAD_LEFT);
        
        // Position 31-40: Credit Total Amount (10 digits of cents, zero-padded)
        $creditAmountCents = round($totalCreditAmount * 100);
        $line .= str_pad($creditAmountCents, 10, '0', STR_PAD_LEFT);
        
        // Position 41-50: Debit Total Amount (10 digits of cents, zero-padded)
        $line .= str_pad(0, 10, '0', STR_PAD_LEFT);
        
        // Position 51-74: 24 blank spaces
        $line .= str_repeat(' ', 24);
        
        // Position 75-80: Total count of Type 1 records (6 digits, zero-padded)
        $line .= str_pad($transactionCount, 6, '0', STR_PAD_LEFT);
        
        // Position 81-120: 40 blank trailing spaces
        $line .= str_repeat(' ', 40);
        
        return $line;
    }

    /**
     * Ensure a line is exactly 120 characters
     */
    private function ensure120Chars($line)
    {
        // Remove any existing line breaks
        $line = preg_replace('/\r\n|\r|\n/', '', $line);
        
        // Ensure exactly 120 characters
        $length = strlen($line);
        
        if ($length > 120) {
            // Truncate to 120 characters
            $line = substr($line, 0, 120);
        } elseif ($length < 120) {
            // Pad with spaces to reach 120
            $line = str_pad($line, 120, ' ');
        }
        
        return $line;
    }

    /**
     * Generate unique batch number
     */
    private function generateBatchNumber()
    {
        return 'ABA-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    /**
     * Check if ABA exists for payroll
     */
    public function existsForPayroll($payrollId)
    {
        return ABABatch::where('payroll_id', $payrollId)->exists();
    }

    /**
     * Get history
     */
    public function getHistory($companyId = null)
    {
        $query = ABABatch::with(['company', 'payroll', 'generator']);
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Get content of ABA file
     */
    public function getContent($batchId)
    {
        $batch = ABABatch::findOrFail($batchId);
        
        if (!$batch->file_path || !Storage::disk('public')->exists($batch->file_path)) {
            throw new \Exception('ABA file not found.');
        }
        
        return Storage::disk('public')->get($batch->file_path);
    }

    /**
     * Download ABA file
     */
    public function download($batchId)
    {
        $batch = ABABatch::findOrFail($batchId);
        
        if (!$batch->file_path || !Storage::disk('public')->exists($batch->file_path)) {
            throw new \Exception('ABA file not found.');
        }
        
        $content = Storage::disk('public')->get($batch->file_path);
        $filename = $batch->filename ?? 'ABA_' . $batch->batch_number . '.aba';
        
        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
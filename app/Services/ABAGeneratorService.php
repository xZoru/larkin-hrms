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
     * STRIPPED BACK - ONE-WAY format (No Contra/Corporate Debit line)
     */
    private function generateABAContent($payrollItems, $payroll, $company, $bankDetails)
    {
        $lines = [];
        
        // LINE 1: HEADER RECORD (120 characters)
        $header = $this->formatHeader($company, $bankDetails);
        $lines[] = $this->padTo120($header);

        // LINE 2 - N: DETAIL RECORDS (120 characters each)
        $transactionCount = 0;
        $totalAmount = 0;

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
                $bankDetails['payment_type'] ?? 'SALARY',
                $bankDetails['debit_description'] ?? ''  // ✅ PASS DESCRIPTION HERE
            );
            
            $lines[] = $this->padTo120($detail);
            
            $transactionCount++;
            $totalAmount += $item->net_pay;
        }

        // LINE N+1: TRAILER RECORD (120 characters)
        $trailer = $this->formatTrailerRecord($transactionCount, $totalAmount);
        $lines[] = $this->padTo120($trailer);

        return implode("\r\n", $lines);
    }

    /**
     * Format Header Record - 120 characters (STRIPPED BACK)
     * 
     * Format: 
     * Position 1: '0'
     * Positions 2-18: 17 spaces
     * Positions 19-20: '01'
     * Positions 21-23: 'BSP'
     * Positions 24-30: 7 spaces
     * Positions 31-56: Company Name (26 chars, SLAMS directly against APCA - no space)
     * Positions 57-62: '000000' (APCA User ID - directly after name)
     * Positions 63-74: 'SALARY' (12 chars)
     * Positions 75-80: Date (DDMMYY)
     * Positions 81-120: 40 spaces
     */
    private function formatHeader($company, $bankDetails)
    {
        $line = '';
        
        // Position 1: Record Type
        $line .= '0';
        
        // Positions 2-18: 17 blank spaces
        $line .= str_repeat(' ', 17);
        
        // Positions 19-20: Reel Sequence Number
        $line .= '01';
        
        // Positions 21-23: Financial Institution Code
        $line .= 'BSP';
        
        // Positions 24-30: 7 blank spaces
        $line .= str_repeat(' ', 7);
        
        // ✅ Positions 31-56: Company Name (EXACTLY 26 chars, SLAMS directly against APCA)
        $userName = $bankDetails['account_name'] ?? $company->name ?? 'LARKIN ENTERPRISES LTD';
        $userName = strtoupper($userName);
        $userName = str_pad(substr($userName, 0, 26), 26, ' ', STR_PAD_RIGHT);
        $line .= $userName;
        
        // ✅ Positions 57-62: APCA User ID (6 zeros - no space before or after)
        $line .= '000000';
        
        // Positions 63-74: Description (12 chars, left-justified)
        $description = $bankDetails['payment_type'] ?? 'SALARY';
        $description = strtoupper(substr($description, 0, 12));
        $line .= str_pad($description, 12, ' ', STR_PAD_RIGHT);
        
        // Positions 75-80: Date (DDMMYY)
        $date = $bankDetails['payment_date'] ?? now()->format('Y-m-d');
        $dateObj = \Carbon\Carbon::parse($date);
        $line .= $dateObj->format('dmy');
        
        // Positions 81-120: 40 blank trailing spaces
        $line .= str_repeat(' ', 40);
        
        return $line;
    }

    /**
     * Format Detail Record - 120 characters (STRIPPED BACK - NO CONTRA)
     * 
     * Format:
     * Position 1: '1' (Record Type)
     * Positions 2-8: BSB with hyphen (XXX-XXX)
     * Positions 9-17: Account Number (9 chars, left-justified)
     * Position 18: ' ' (space)
     * Positions 19-20: '53' (Transaction Code)
     * Positions 21-30: Amount in cents (10 digits, right-aligned, zero-padded)
     * Positions 31-62: Employee Name (32 chars, left-aligned, uppercase)
     * Positions 63-120: 58 blank spaces (NO corporate codes or references)
     */
    private function formatDetailRecord($bankAccount, $employee, $amount, $paymentType, $debitDescription = '')
    {
        $line = '';
        
        // Position 1: Record Type
        $line .= '1';
        
        // Positions 2-8: BSB with hyphen (XXX-XXX)
        $bsb = $bankAccount->bsb_code ?? '';
        $bsb = preg_replace('/[^0-9]/', '', $bsb);
        if (strlen($bsb) > 6) {
            $bsb = substr($bsb, -6);
        }
        $bsb = str_pad($bsb, 6, '0', STR_PAD_LEFT);
        $line .= substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        
        // Positions 9-17: Account Number (9 chars, left-justified)
        $accountNumber = $bankAccount->account_number ?? '';
        $accountNumber = substr($accountNumber, 0, 9);
        $line .= str_pad($accountNumber, 9, ' ', STR_PAD_RIGHT);
        
        // Position 18: Space
        $line .= ' ';
        
        // Positions 19-20: Transaction Code
        $txnCode = $this->getTransactionCode($paymentType);
        $line .= $txnCode;
        
        // Positions 21-30: Amount in cents (10 digits, right-aligned, zero-padded)
        $amountCents = round($amount * 100);
        $line .= str_pad($amountCents, 10, '0', STR_PAD_LEFT);
        
        // Positions 31-62: Employee Name (32 chars, left-aligned, uppercase)
        $accountName = $bankAccount->account_name ?? $employee->full_name ?? '';
        $accountName = strtoupper(substr($accountName, 0, 32));
        $line .= str_pad($accountName, 32, ' ', STR_PAD_RIGHT);
        
        // ✅ Positions 63-80: Description (18 chars) - This is the REFERENCE field
        $description = strtoupper(substr($debitDescription, 0, 18));
        $line .= str_pad($description, 18, ' ', STR_PAD_RIGHT);
        
        // ✅ Positions 81-120: 40 blank spaces (remaining)
        $line .= str_repeat(' ', 40);
        
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
     * Format:
     * Position 1: '7' (Record Type)
     * Positions 2-8: '999-999' (BSB Format Filler)
     * Positions 9-20: 12 blank spaces
     * Positions 21-30: Net Total Amount (10 digits of cents, zero-padded)
     * Positions 31-40: Credit Total Amount (10 digits of cents, zero-padded)
     * Positions 41-50: Debit Total Amount (10 digits of cents, zero-padded)
     * Positions 51-74: 24 blank spaces
     * Positions 75-80: Total count (6 digits, zero-padded)
     * Positions 81-120: 40 blank trailing spaces
     */
    private function formatTrailerRecord($transactionCount, $totalAmount)
    {
        $line = '';
        
        // Position 1: Record Type
        $line .= '7';
        
        // Positions 2-8: BSB Format Filler
        $line .= '999-999';
        
        // Positions 9-20: 12 blank spaces
        $line .= str_repeat(' ', 12);
        
        // Positions 21-30: Net Total Amount (10 digits of cents, zero-padded)
        $totalAmountCents = round($totalAmount * 100);
        $line .= str_pad($totalAmountCents, 10, '0', STR_PAD_LEFT);
        
        // Positions 31-40: Credit Total Amount (10 digits of cents, zero-padded)
        $line .= str_pad($totalAmountCents, 10, '0', STR_PAD_LEFT);
        
        // Positions 41-50: Debit Total Amount (10 digits of cents, zero-padded)
        $line .= str_pad(0, 10, '0', STR_PAD_LEFT);
        
        // Positions 51-74: 24 blank spaces
        $line .= str_repeat(' ', 24);
        
        // Positions 75-80: Total count (6 digits, zero-padded)
        $line .= str_pad($transactionCount, 6, '0', STR_PAD_LEFT);
        
        // Positions 81-120: 40 blank trailing spaces
        $line .= str_repeat(' ', 40);
        
        return $line;
    }

    /**
     * Pad a line to exactly 120 characters
     */
    private function padTo120($line)
    {
        // Remove any existing line breaks
        $line = preg_replace('/\r\n|\r|\n/', '', $line);
        
        // Ensure exactly 120 characters
        $length = strlen($line);
        
        if ($length > 120) {
            // Truncate if longer
            $line = substr($line, 0, 120);
        } elseif ($length < 120) {
            // Pad with spaces if shorter
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
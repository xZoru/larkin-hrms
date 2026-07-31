<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\Company;
use App\Models\ABABatch;
use App\Models\PayrollItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ABAGeneratorService
{
    public function generate(Payroll $payroll, Company $company, array $bankDetails)
    {
        // 1. Get regular payroll items with employees & bank accounts
        $regularItems = $payroll->items()
            ->with(['employee.bankAccounts'])
            ->whereNotNull('employee_id')
            ->where('net_pay', '>', 0)
            ->get()
            ->filter(function($item) {
                if (!$item->employee || !$item->employee->bankAccounts) {
                    return false;
                }
                
                // Get active account or fallback to first available account
                $activeAccount = $item->employee->bankAccounts
                    ->where('is_active', true)
                    ->first() ?? $item->employee->bankAccounts->first();
                
                return !is_null($activeAccount);
            });

        // 2. Get manual entries (employee_id = null)
        $manualItems = $payroll->items()
            ->whereNull('employee_id')
            ->where('details->type', 'manual_entry')
            ->where('net_pay', '>', 0)
            ->get()
            ->map(function($item) {
                $details = $item->details;
                $item->virtual_bank_account = (object) [
                    'bsb_code' => $details['bsb'] ?? '',
                    'account_number' => $details['account_number'] ?? '',
                    'account_name' => $details['account_name'] ?? 'MANUAL ENTRY',
                    'details' => $details,
                ];
                return $item;
            });

        // 3. Merge both collections
        $payrollItems = $regularItems->merge($manualItems);

        if ($payrollItems->isEmpty()) {
            throw new \Exception('No employees or manual entries with valid bank details found for this payroll.');
        }

        $content = $this->generateABAContent($payrollItems, $payroll, $company, $bankDetails);
        
        $batchNumber = $this->generateBatchNumber();
        $filename = 'ABA_' . $batchNumber . '.aba';
        
        $batch = ABABatch::create([
            'company_id' => $company->id,
            'payroll_id' => $payroll->id,
            'batch_number' => $batchNumber,
            'bank_name' => $bankDetails['bank_name'] ?? $company->bank_name ?? 'BSP Bank',
            'bank_code' => $bankDetails['bank_code'] ?? 'BSP',
            'apca_user_id' => $bankDetails['apca_user_id'] ?? '000001',
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

        $path = 'aba/' . $filename;
        Storage::disk('public')->put($path, $content);
        
        $batch->update([
            'file_path' => $path,
        ]);

        return $batch;
    }

    private function generateABAContent($payrollItems, $payroll, $company, $bankDetails)
    {
        $lines = [];
        
        // Tracer Reference
        $tracerReference = $this->formatTracerReference($company, $bankDetails);
        
        // 1. Header Record (Type 0)
        $lines[] = $this->formatHeader($company, $bankDetails);

        // 2. Detail Records (Type 1)
        $transactionCount = 0;
        $totalAmount = 0;

        foreach ($payrollItems as $item) {
            $isManual = isset($item->virtual_bank_account);
            
            if ($isManual) {
                // Manual entry
                $bankAccount = $item->virtual_bank_account;
                $employee = null;
                $amount = $item->net_pay;
            } else {
                // Regular employee (uses already loaded collection without running new SQL queries)
                $employee = $item->employee;
                $bankAccount = $employee->bankAccounts
                    ->where('is_active', true)
                    ->first() ?? $employee->bankAccounts->first();
                $amount = $item->net_pay;
            }
            
            if (!$bankAccount) {
                continue;
            }

            $detail = $this->formatDetailRecord(
                $bankAccount,
                $employee,
                $amount,
                $bankDetails['payment_type'] ?? 'SALARY',
                $bankDetails['debit_description'] ?? '',
                $tracerReference,
                $payroll,
                $isManual
            );
            
            $lines[] = $detail;
            
            $transactionCount++;
            $totalAmount += $amount;
        }

        // 3. Contra / Balancing Record (Type 1)
        $lines[] = $this->formatTracerRecord($company, $bankDetails, $totalAmount, $tracerReference, $payroll);
        $transactionCount++;

        // 4. Trailer / Footer Record (Type 7)
        $lines[] = $this->formatTrailerRecord($transactionCount, $totalAmount);

        // 5. Mandatory Strict 132-Character Validation Check (BSP bank format)
        //    Standard CEMTEX/ABA records are 120 characters, but BSP's generator
        //    requires every record to be padded out to 132 characters. If any
        //    line does not match, stop generation rather than emit an invalid file.
        foreach ($lines as $index => $line) {
            $length = strlen($line);
            if ($length !== 132) {
                $lineType = match ($index) {
                    0 => 'Header Record (Type 0)',
                    count($lines) - 1 => 'Trailer/Footer Record (Type 7)',
                    default => "Detail Record (Line " . ($index + 1) . ")",
                };

                throw new \Exception(
                    "ABA File Generation Error: {$lineType} must be exactly 132 characters long (BSP format). Current length is {$length}."
                );
            }
        }

        // 6. Join lines using Windows CRLF (\r\n) with a trailing newline
        return implode("\r\n", $lines) . "\r\n";
    }

    /**
     * Format Tracer Reference
     * Output: 088-950000007009 LARKIN ENTERPRISES LIMITED  (42 chars: BSB 7 + Account 9 + Remitter Name 26)
     */
    private function formatTracerReference($company, $bankDetails)
    {
        // Get BSB (6 digits)
        $bsb = $bankDetails['bsb_number'] ?? $company->bsb_code ?? '088950';
        $bsb = preg_replace('/[^0-9]/', '', $bsb);
        $bsb = str_pad($bsb, 6, '0', STR_PAD_LEFT);
        $bsbFormatted = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        
        // Get Account Number (9 digits per ABA spec)
        $account = $bankDetails['account_number'] ?? $company->bank_account_number ?? '7009276416';
        $account = preg_replace('/[^0-9]/', '', $account);
        $account = str_pad(substr($account, 0, 9), 9, '0', STR_PAD_LEFT);
        
        $companyName = $bankDetails['account_name'] ?? $company->bank_account_name ?? $company->name ?? 'LARKIN ENTERPRISES LIMITED';
        $companyName = strtoupper($companyName);
        $companyName = str_pad(substr($companyName, 0, 26), 26, ' ');
        
        // BSB(7) + Account(9) + Remitter Name(26) = 42 characters total.
        return $bsbFormatted . $account . $companyName;
    }

    /**
     * Record Type 0: Descriptive Header Record (Exact length: 132 characters, BSP format)
     */
    private function formatHeader($company, $bankDetails)
    {
        $line = '';
        
        $line .= '0';                                                                            // Record Type (1)
        $line .= str_repeat(' ', 17);                                                           // Sequence/Filler (17)
        $line .= '01';                                                                          // Reel Sequence (2)
        
        $bankCode = $bankDetails['bank_code'] ?? 'BSP';
        $line .= str_pad(substr($bankCode, 0, 3), 3, ' ');                                      // Bank Name (3)
        $line .= str_repeat(' ', 7);                                                            // Reserved Filler (7)
        
        $userName = $bankDetails['account_name'] ?? $company->bank_account_name ?? $company->name ?? 'LARKIN ENTERPRISES LIMITED';
        $userName = strtoupper($userName);
        $line .= str_pad(substr($userName, 0, 26), 26, ' ', STR_PAD_RIGHT);                    // User Name (26)
        
        $apcaId = $bankDetails['apca_user_id'] ?? $company->apca_user_id ?? '000001';
        $line .= str_pad(substr($apcaId, 0, 6), 6, '0', STR_PAD_LEFT);                         // User ID (6)
        
        $description = $bankDetails['payment_type'] ?? 'SALARY';
        $description = strtoupper(substr($description, 0, 12));
        $line .= str_pad($description, 12, ' ', STR_PAD_RIGHT);                                // Description (12)
        
        $date = $bankDetails['payment_date'] ?? now()->format('Y-m-d');
        $dateObj = \Carbon\Carbon::parse($date);
        $line .= $dateObj->format('Ymd');                                                       // Processing Date DDMMYY (6)
        
        // 1 + 17 + 2 + 3 + 7 + 26 + 6 + 12 + 6 = 80 characters.
        // Requires exactly 52 trailing spaces to reach 132 characters total (BSP format).
        $line .= str_repeat(' ', 52);                                                           // Trailing padding (52)
        
        return str_pad(substr($line, 0, 132), 132, ' ', STR_PAD_RIGHT);
    }

    /**
     * Record Type 1: Payment Detail Record (Exact length: 132 characters, BSP format)
     */
    private function formatDetailRecord($bankAccount, $employee, $amount, $paymentType, $debitDescription, $tracerReference, $payroll, $isManual = false)
    {
        $line = '';
        
        $line .= '1';                                                                            // Record Type (1)
        
        // BSB
        $bsb = $bankAccount->bsb_code ?? '';
        $bsb = preg_replace('/[^0-9]/', '', $bsb);
        if (strlen($bsb) > 6) {
            $bsb = substr($bsb, -6);
        }
        $bsb = str_pad($bsb, 6, '0', STR_PAD_LEFT);
        $bsbFormatted = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        $line .= str_pad($bsbFormatted, 7, '-', STR_PAD_RIGHT);                                 // BSB Number (7)
        
        // Account Number (9 digits per ABA spec)
        $accountNumber = $bankAccount->account_number ?? '';
        $accountNumber = preg_replace('/[^0-9]/', '', $accountNumber);
        $accountNumber = substr($accountNumber, 0, 9);
        $line .= str_pad($accountNumber, 9, '0', STR_PAD_LEFT);                                 // Account Number (9)
        
        $line .= ' ';                                                                           // Indicator (1)
        $line .= '53';                                                                          // Transaction Code (2)
        
        $amountCents = round($amount * 100);
        $line .= str_pad($amountCents, 10, '0', STR_PAD_LEFT);                                 // Amount (10)
        
        // Employee / Payee Name (30 chars)
        if ($isManual && isset($bankAccount->details)) {
            $accountName = $bankAccount->details['account_name'] ?? 'MANUAL ENTRY';
        } else {
            $accountName = $bankAccount->account_name ?? $employee->full_name ?? '';
        }
        $accountName = strtoupper(substr($accountName, 0, 30));
        $line .= str_pad($accountName, 30, ' ', STR_PAD_RIGHT);                                // Account Name (30)
        
        // Lodgement Reference (18 chars)
        $fortnightRef = 'FN' . $payroll->fortnight_number;
        $description = strtoupper(substr($fortnightRef, 0, 18));
        $line .= str_pad($description, 18, ' ', STR_PAD_RIGHT);                                // Description / Reference (18)
        
        // Trace BSB(7) + Trace Account(9) + Remitter Name(26)
        $line .= $tracerReference;                                                             // Tracer Reference (42)
        
        // 1 + 7 + 9 + 1 + 2 + 10 + 30 + 18 + 42 = 120 characters, +12 filler = 132 (BSP format).
        return str_pad(substr($line, 0, 132), 132, ' ', STR_PAD_RIGHT);
    }

    /**
     * Record Type 1: Balancing / Contra Record (Exact length: 132 characters, BSP format)
     */
    private function formatTracerRecord($company, $bankDetails, $totalAmount, $tracerReference, $payroll)
    {
        $line = '';
        
        $line .= '1';                                                                            // Record Type (1)
        
        // Tracer BSB
        $bsb = $bankDetails['bsb_number'] ?? $company->bsb_code ?? '088950';
        $bsb = preg_replace('/[^0-9]/', '', $bsb);
        $bsb = str_pad($bsb, 6, '0', STR_PAD_LEFT);
        $bsbFormatted = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        $line .= str_pad($bsbFormatted, 7, '-', STR_PAD_RIGHT);                                 // BSB Number (7)
        
        // Tracer Account (9 digits per ABA spec)
        $account = $bankDetails['account_number'] ?? $company->bank_account_number ?? '7009276416';
        $account = preg_replace('/[^0-9]/', '', $account);
        $account = substr($account, 0, 9);
        $line .= str_pad($account, 9, '0', STR_PAD_LEFT);                                      // Account Number (9)
        
        $line .= ' ';                                                                           // Indicator (1)
        $line .= '13';                                                                          // Transaction Code - Contra (2)
        
        $totalAmountCents = round($totalAmount * 100);
        $line .= str_pad($totalAmountCents, 10, '0', STR_PAD_LEFT);                             // Amount (10)
        
        // Company Name (30 chars)
        $userName = $bankDetails['account_name'] ?? $company->bank_account_name ?? $company->name ?? 'LARKIN ENTERPRISES LIMITED';
        $userName = strtoupper($userName);
        $line .= str_pad(substr($userName, 0, 30), 30, ' ', STR_PAD_RIGHT);                    // Company Name (30)
        
        // Description
        $fortnightRef = 'FN' . $payroll->fortnight_number;
        $tracerRef = $bankDetails['tracer_reference'] ?? $fortnightRef;
        $tracerRef = strtoupper(substr($tracerRef, 0, 18));
        $line .= str_pad($tracerRef, 18, ' ', STR_PAD_RIGHT);                                  // Reference (18)
        
        // Trace BSB(7) + Trace Account(9) + Remitter Name(26)
        $line .= $tracerReference;                                                             // Tracer Reference (42)
        
        // 1 + 7 + 9 + 1 + 2 + 10 + 30 + 18 + 42 = 120 characters, +12 filler = 132 (BSP format).
        return str_pad(substr($line, 0, 132), 132, ' ', STR_PAD_RIGHT);
    }

    /**
     * Record Type 7: File Trailer / Footer Record (Exact length: 132 characters, BSP format)
     */
    private function formatTrailerRecord($transactionCount, $totalAmount)
    {
        $line = '';
        
        $line .= '7';                                                                            // Record Type (1)
        $line .= '999-999';                                                                     // BSB Filler (7)
        $line .= str_repeat(' ', 12);                                                           // Reserved Filler (12)
        
        $totalAmountCents = round($totalAmount * 100);
        $line .= str_pad(0, 10, '0', STR_PAD_LEFT);                                             // Net Total (10)
        $line .= str_pad($totalAmountCents, 10, '0', STR_PAD_LEFT);                             // Credit Total (10)
        $line .= str_pad($totalAmountCents, 10, '0', STR_PAD_LEFT);                             // Debit Total (10)
        $line .= str_repeat(' ', 24);                                                           // Reserved Filler (24)
        $line .= str_pad($transactionCount, 6, '0', STR_PAD_LEFT);                             // Item Count (6)
        
        // 1 + 7 + 12 + 10 + 10 + 10 + 24 + 6 = 80 characters.
        // Requires exactly 52 trailing spaces to reach 132 characters total (BSP format).
        $line .= str_repeat(' ', 52);                                                           // Trailing padding (52)
        
        return str_pad(substr($line, 0, 132), 132, ' ', STR_PAD_RIGHT);
    }

    private function generateBatchNumber()
    {
        return 'ABA-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    private function getTransactionCode($paymentType)
    {
        return '53';
    }

    public function existsForPayroll($payrollId)
    {
        return ABABatch::where('payroll_id', $payrollId)->exists();
    }

    public function getHistory($companyId = null)
    {
        $query = ABABatch::with(['company', 'payroll', 'generator']);
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getContent($batchId)
    {
        $batch = ABABatch::findOrFail($batchId);
        
        if (!$batch->file_path || !Storage::disk('public')->exists($batch->file_path)) {
            throw new \Exception('ABA file not found.');
        }
        
        return Storage::disk('public')->get($batch->file_path);
    }

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
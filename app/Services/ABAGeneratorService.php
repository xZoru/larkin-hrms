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

        $content = $this->generateABAContent($payrollItems, $payroll, $company, $bankDetails);
        
        $batchNumber = $this->generateBatchNumber();
        $filename = 'ABA_' . $batchNumber . '.aba';
        
        $batch = ABABatch::create([
            'company_id' => $company->id,
            'payroll_id' => $payroll->id,
            'batch_number' => $batchNumber,
            'bank_name' => $bankDetails['bank_name'] ?? $company->bank_name ?? 'BSP Bank',
            'bank_code' => $bankDetails['bank_code'] ?? 'BSP',  // NEW
            'apca_user_id' => $bankDetails['apca_user_id'] ?? '000001',  // NEW
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
        
        //  Get file format from bankDetails (default: STANDARD)
        $fileFormat = $bankDetails['aba_file_format'] ?? 'STANDARD';
        
        // Tracer Reference
        $tracerReference = $this->formatTracerReference($company, $bankDetails);
        
        // Header
        $header = $this->formatHeader($company, $bankDetails);
        $lines[] = $this->padLine($header, $fileFormat);  // ✅ Changed from padTo120

        // Detail Records
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
                $bankDetails['debit_description'] ?? '',
                $tracerReference,
                $payroll
            );
            
            $lines[] = $this->padLine($detail, $fileFormat);  // ✅ Changed from padTo120
            
            $transactionCount++;
            $totalAmount += $item->net_pay;
        }

        // Contra Record
        $tracerRecord = $this->formatTracerRecord($company, $bankDetails, $totalAmount, $tracerReference, $payroll);
        $lines[] = $this->padLine($tracerRecord, $fileFormat);  // ✅ Changed from padTo120
        $transactionCount++;

        // Trailer
        $trailer = $this->formatTrailerRecord($transactionCount, $totalAmount);
        $lines[] = $this->padLine($trailer, $fileFormat);  // ✅ Changed from padTo120

        return implode("\r\n", $lines);
    }

    /**
     * Format Tracer Reference
     * Output: 088-950000007009276416LARKIN ENTERPRIS00000000
     */
    private function formatTracerReference($company, $bankDetails)
    {
        // Get BSB (6 digits)
        $bsb = $bankDetails['bsb_number'] ?? $company->bsb_code ?? '088950';
        $bsb = preg_replace('/[^0-9]/', '', $bsb);
        $bsb = str_pad($bsb, 6, '0', STR_PAD_LEFT);
        $bsbFormatted = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        
        // Get Account Number (15 digits)
        $account = $bankDetails['account_number'] ?? $company->bank_account_number ?? '7009276416';
        $account = preg_replace('/[^0-9]/', '', $account);
        $account = str_pad(substr($account, 0, 15), 15, '0', STR_PAD_LEFT);
        
        //  FIX: Use $company->name directly (this is correct in the header!)
        $companyName = $company->name ?? 'LARKIN ENTERPRISES LIMITED';
        
        // Convert to uppercase
        $companyName = strtoupper($companyName);
        
        // Take first 16 characters
        $companyName = substr($companyName, 0, 16);
        
        // Pad to exactly 16 characters
        $companyName = str_pad($companyName, 16, ' ');
        
        // Padding (8 zeros)
        $padding = str_repeat('0', 8);
        
        // Build tracer reference
        return $bsbFormatted . $account . $companyName . $padding;
    }

    private function formatHeader($company, $bankDetails)
    {
        $line = '';
        
        $line .= '0';
        $line .= str_repeat(' ', 17);
        $line .= '01';
        $bankCode = $bankDetails['bank_code'] ?? 'BSP';
        $line .= str_pad(substr($bankCode, 0, 3), 3, ' ');
        $line .= str_repeat(' ', 7);
        
        $userName = $bankDetails['account_name'] ?? $company->name ?? 'LARKIN ENTERPRISES LIMITED';
        $userName = strtoupper($userName);
        $userName = str_pad(substr($userName, 0, 26), 26, ' ', STR_PAD_RIGHT);
        $line .= $userName;
        
        $apcaId = $bankDetails['apca_user_id'] ?? $company->apca_user_id ?? '000001';
        $line .= str_pad(substr($apcaId, 0, 6), 6, '0', STR_PAD_LEFT);
        
        $description = $bankDetails['payment_type'] ?? 'SALARY';
        $description = strtoupper(substr($description, 0, 12));
        $line .= str_pad($description, 12, ' ', STR_PAD_RIGHT);
        
        $date = $bankDetails['payment_date'] ?? now()->format('Y-m-d');
        $dateObj = \Carbon\Carbon::parse($date);
        $line .= $dateObj->format('Ymd');
        
        $line .= str_repeat(' ', 40);
        
        return $line;
    }

    private function formatDetailRecord($bankAccount, $employee, $amount, $paymentType, $debitDescription, $tracerReference, $payroll)
    {
        $line = '';
        
        // Record Type
        $line .= '1';
        
        // BSB
        $bsb = $bankAccount->bsb_code ?? '';
        $bsb = preg_replace('/[^0-9]/', '', $bsb);
        if (strlen($bsb) > 6) {
            $bsb = substr($bsb, -6);
        }
        $bsb = str_pad($bsb, 6, '0', STR_PAD_LEFT);
        $bsbFormatted = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        $line .= str_pad($bsbFormatted, 7, '-', STR_PAD_RIGHT);
        
        // Account Number (15 digits)
        $accountNumber = $bankAccount->account_number ?? '';
        $accountNumber = preg_replace('/[^0-9]/', '', $accountNumber);
        $accountNumber = substr($accountNumber, 0, 15);
        $line .= str_pad($accountNumber, 15, '0', STR_PAD_LEFT);
        
        // Space
        $line .= ' ';
        
        // Transaction Code
        $line .= '53';
        
        // Amount
        $amountCents = round($amount * 100);
        $line .= str_pad($amountCents, 10, '0', STR_PAD_LEFT);
        
        // Employee Name (32 chars)
        $accountName = $bankAccount->account_name ?? $employee->full_name ?? '';
        $accountName = strtoupper(substr($accountName, 0, 32));
        $line .= str_pad($accountName, 32, ' ', STR_PAD_RIGHT);
        
        // Description (18 chars)
        $fortnightRef = 'FN' . $payroll->fortnight_number;
        $description = strtoupper(substr($fortnightRef, 0, 18));
        $line .= str_pad($description, 18, ' ', STR_PAD_RIGHT);
        
        // ✅ FIX: Use the FULL 46-char tracer reference
        $line .= $tracerReference;
        
        return $line;
    }

    private function formatTracerRecord($company, $bankDetails, $totalAmount, $tracerReference, $payroll)
    {
        $line = '';
        
        // Record Type
        $line .= '1';
        
        // Tracer BSB
        $bsb = $bankDetails['bsb_number'] ?? $company->bsb_code ?? '088950';
        $bsb = preg_replace('/[^0-9]/', '', $bsb);
        $bsb = str_pad($bsb, 6, '0', STR_PAD_LEFT);
        $bsbFormatted = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
        $line .= str_pad($bsbFormatted, 7, '-', STR_PAD_RIGHT);
        
        // Tracer Account
        $account = $bankDetails['account_number'] ?? $company->bank_account_number ?? '7009276416';
        $account = preg_replace('/[^0-9]/', '', $account);
        $account = substr($account, 0, 15);
        $line .= str_pad($account, 15, '0', STR_PAD_LEFT);
        
        // Space
        $line .= ' ';
        
        // Transaction Code (Contra)
        $line .= '13';
        
        // Total Amount
        $totalAmountCents = round($totalAmount * 100);
        $line .= str_pad($totalAmountCents, 10, '0', STR_PAD_LEFT);
        
        // Company Name
        $userName = $bankDetails['account_name'] ?? $company->name ?? 'LARKIN ENTERPRISES LIMITED';
        $userName = strtoupper($userName);
        $userName = str_pad(substr($userName, 0, 32), 32, ' ', STR_PAD_RIGHT);
        $line .= $userName;
        
        // Description
        $fortnightRef = 'FN' . $payroll->fortnight_number;
        $tracerRef = $bankDetails['tracer_reference'] ?? $fortnightRef;
        $tracerRef = strtoupper(substr($tracerRef, 0, 18));
        $line .= str_pad($tracerRef, 18, ' ', STR_PAD_RIGHT);
        
        // Tracer Reference
        $line .= $tracerReference;
        
        return $line;
    }

    private function formatTrailerRecord($transactionCount, $totalAmount)
    {
        $line = '';
        
        $line .= '7';
        $line .= '999-999';
        $line .= str_repeat(' ', 12);
        
        $totalAmountCents = round($totalAmount * 100);
        $line .= str_pad(0, 10, '0', STR_PAD_LEFT);
        $line .= str_pad($totalAmountCents, 10, '0', STR_PAD_LEFT);
        $line .= str_pad($totalAmountCents, 10, '0', STR_PAD_LEFT);
        $line .= str_repeat(' ', 24);
        $line .= str_pad($transactionCount, 6, '0', STR_PAD_LEFT);
        $line .= str_repeat(' ', 40);
        
        return $line;
    }
    
    private function padLine($line, $fileFormat = 'STANDARD')
    {
        // Remove any existing line breaks
        $line = preg_replace('/\r\n|\r|\n/', '', $line);
        
        // Determine target length based on format
        $targetLength = ($fileFormat === 'KUNDUPEI') ? 132 : 120;
        $length = strlen($line);
        
        // Only pad if shorter than target (don't truncate)
        if ($length < $targetLength) {
            $line = str_pad($line, $targetLength, ' ');
        }
        // If longer than target, keep as-is (ABA allows longer lines)
        
        return $line;
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
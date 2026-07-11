<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Payroll;
use App\Models\ABABatch;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\BankAccount;
use App\Services\ABAGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ABAGeneratorController extends Controller
{
    protected $abaService;

    public function __construct(ABAGeneratorService $abaService)
    {
        $this->abaService = $abaService;
    }

    /**
     * Show ABA generation form
     */
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->get();
        
        $companyId = $request->get('company_id');
        
        if (!$companyId) {
            $companyId = session('current_company_id');
        }
        
        if ($companyId && !Company::find($companyId)) {
            $companyId = null;
        }
        
        if (!$companyId && $companies->isNotEmpty()) {
            $companyId = $companies->first()->id;
        }
        
        if ($companyId) {
            session(['current_company_id' => $companyId]);
        }
        
        $payrolls = collect();
        $companyBankDetails = [];
        $currentFortnight = '';
        
        if ($companyId) {
            $payrolls = Payroll::where('company_id', $companyId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $company = Company::find($companyId);
            if ($company) {
                $companyBankDetails = [
                    'bank_name' => $company->bank_name ?? '',
                    'bsb_code' => $company->bsb_code ?? '',
                    'bank_account_number' => $company->bank_account_number ?? '',
                    'bank_account_name' => $company->bank_account_name ?? $company->name,
                ];
            }
            
            if ($payrolls->isNotEmpty()) {
                $latestPayroll = $payrolls->first();
                $currentFortnight = $latestPayroll->fortnight_number ?? '';
            }
        }
        
        return view('aba.index', compact('companies', 'payrolls', 'companyId', 'companyBankDetails', 'currentFortnight'));
    }

    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payroll_id' => 'required|exists:payrolls,id',
            'company_id' => 'required|exists:companies,id',
            'bank_name' => 'nullable|string|max:100',
            'bsb_number' => 'nullable|string|max:10',
            'bank_account_number' => 'nullable|string|max:20',
            'bank_account_name' => 'nullable|string|max:100',
            'payment_type' => 'nullable|string|max:50',
            'debit_description' => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $payroll = Payroll::findOrFail($request->payroll_id);
            $company = Company::findOrFail($request->company_id);
            
            if ($this->abaService->existsForPayroll($payroll->id)) {
                return redirect()->back()
                    ->with('warning', 'ABA file already generated for this payroll.');
            }
            
            $bankDetails = [
                'bank_name' => $request->bank_name ?? $company->bank_name ?? 'HRMS Bank',
                'bsb_number' => $request->bsb_number ?? $company->bsb_code ?? '0000000',
                'account_number' => $request->bank_account_number ?? $company->bank_account_number ?? '000000000',
                'account_name' => $request->bank_account_name ?? $company->bank_account_name ?? $company->name,
                'payment_type' => $request->payment_type ?? 'SALARY',
                'debit_description' => $request->debit_description ?? 'PAYROLL',
                'payment_date' => $request->payment_date ?? now()->format('Y-m-d'),
            ];
            
            $batch = $this->abaService->generate($payroll, $company, $bankDetails);
            
            return redirect()->route('aba.show', $batch->id)
                ->with('success', 'ABA file generated successfully! Batch #: ' . $batch->batch_number);
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to generate ABA file: ' . $e->getMessage());
        }
    }
    
    /**
     * Show ABA file details
     */
    public function show($id)
    {
        $batch = ABABatch::with(['company', 'payroll', 'generator'])->findOrFail($id);
        return view('aba.show', compact('batch'));
    }

    /**
     * Download ABA file
     */
    public function download($id)
    {
        try {
            return $this->abaService->download($id);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to download ABA file: ' . $e->getMessage());
        }
    }

    public function exportExcel($id)
    {
        try {
            $batch = ABABatch::findOrFail($id);
            
            $payrollItems = $batch->payroll->items()
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
                return redirect()->back()
                    ->with('error', 'No employees with valid bank details found for this payroll.');
            }

            $companyName = $batch->account_name ?? 'Company Name';
            
            $content = [];
            $content[] = ['Company Name:', $companyName];
            $content[] = ['Type of Payment:', $batch->metadata['payment_type'] ?? 'SALARY'];
            
            $date = isset($batch->metadata['payment_date']) 
                ? date('n/j/Y', strtotime($batch->metadata['payment_date'])) 
                : date('n/j/Y');
            $content[] = ['Date:', $date];
            
            $bsb = $batch->bsb_number ?? '';
            if ($bsb && strlen($bsb) >= 6) {
                $bsb = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
            }
            $content[] = ['Company BSB:', $bsb];
            $content[] = ['Company Account:', $batch->account_number ?? ''];
            $content[] = ['Total Amount:', number_format($batch->total_amount, 2)];
            $content[] = ['Debit Description:', $batch->metadata['debit_description'] ?? 'PAYROLL'];
            
            $content[] = [];
            $content[] = ['BSB', 'Account Number', 'Amount', 'Account Name', 'Description'];
            
            foreach ($payrollItems as $item) {
                $employee = $item->employee;
                $bankAccount = $employee->bankAccounts()->where('is_active', true)->first();
                
                $bsb = $bankAccount->bsb_code ?? '';
                $bsb = preg_replace('/[^0-9]/', '', $bsb);
                if (strlen($bsb) >= 6) {
                    $bsb = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
                }
                
                $content[] = [
                    $bsb,
                    $bankAccount->account_number ?? '',
                    number_format($item->net_pay, 2),
                    strtoupper($bankAccount->account_name ?? $employee->full_name),
                    $batch->metadata['debit_description'] ?? 'FN' . ($batch->payroll->fortnight_number ?? ''),
                ];
            }
            
            $content[] = [];
            $content[] = ['', 'TOTAL:', number_format($batch->total_amount, 2), '', ''];

            $filename = 'ABA_' . $batch->batch_number . '_' . date('Ymd') . '.csv';
            
            $handle = fopen('php://temp', 'w+');
            foreach ($content as $row) {
                fputcsv($handle, $row);
            }
            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ]);
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to export: ' . $e->getMessage());
        }
    }
    
    /**
     * Show ABA generation history
     */
    public function history(Request $request)
    {
        $companyId = $request->get('company_id', session('current_company_id'));
        $history = $this->abaService->getHistory($companyId);
        $companies = Company::where('is_active', true)->get();
        
        return view('aba.history', compact('history', 'companies', 'companyId'));
    }

    /**
     * Preview ABA file content
     */
    public function preview($id)
    {
        try {
            $batch = ABABatch::findOrFail($id);
            $content = $this->abaService->getContent($id);
            
            return response()->json([
                'success' => true,
                'content' => nl2br($content),
                'filename' => $batch->filename,
                'batch' => $batch
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Delete ABA batch
     */
    public function destroy($id)
    {
        try {
            $batch = ABABatch::findOrFail($id);
            
            if ($batch->file_path && \Storage::disk('public')->exists($batch->file_path)) {
                \Storage::disk('public')->delete($batch->file_path);
            }
            
            $batch->delete();
            
            return redirect()->route('aba.history')
                ->with('success', 'ABA batch deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete ABA batch: ' . $e->getMessage());
        }
    }

    /**
     * API: Get payrolls by company (for AJAX)
     */
    public function getPayrollsByCompany(Request $request)
    {
        $companyId = $request->get('company_id');
        
        if (!$companyId) {
            return response()->json([]);
        }
        
        session(['current_company_id' => $companyId]);
        
        $payrolls = Payroll::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($payroll) {
                $period = 'Payroll #' . $payroll->id;
                if ($payroll->fortnight_number) {
                    $period = 'FN' . $payroll->fortnight_number;
                } elseif ($payroll->period_start && $payroll->period_end) {
                    $period = $payroll->period_start->format('d/m/Y') . ' - ' . $payroll->period_end->format('d/m/Y');
                }
                
                return [
                    'id' => $payroll->id,
                    'pay_period' => $period,
                    'fortnight_number' => $payroll->fortnight_number,
                    'period_start' => $payroll->period_start ? $payroll->period_start->format('d/m/Y') : null,
                    'period_end' => $payroll->period_end ? $payroll->period_end->format('d/m/Y') : null,
                    'created_at' => $payroll->created_at->format('d/m/Y H:i'),
                    'status' => $payroll->status ?? 'unknown',
                    'total_net' => $payroll->total_net ?? 0,
                ];
            });
        
        return response()->json($payrolls);
    }

    /**
     * Save manual entries to payroll
     */
    public function saveManualEntries(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'payroll_id' => 'required|exists:payrolls,id',
                'entries' => 'required|array',
                'entries.*.bsb' => 'required|string',
                'entries.*.account_number' => 'required|string',
                'entries.*.amount' => 'required|numeric|min:0.01',
                'entries.*.account_name' => 'required|string',
                'entries.*.description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $payroll = Payroll::findOrFail($request->payroll_id);
            $entries = $request->entries;
            $user = auth()->user();
            $allowedTypes = $user->getAllowedEmployeeTypes();

            foreach ($entries as $entry) {
                $employee = Employee::where('company_id', $payroll->company_id)
                    ->where('full_name', 'LIKE', '%' . $entry['account_name'] . '%')
                    ->whereIn('employee_type', $allowedTypes)
                    ->first();

                if (!$employee) {
                    $employee = Employee::create([
                        'company_id' => $payroll->company_id,
                        'first_name' => $entry['account_name'],
                        'last_name' => 'MANUAL',
                        'full_name' => $entry['account_name'],
                        'employee_type' => 'National',
                        'status' => 'Active',
                        'employee_number' => 'MANUAL-' . time() . rand(100, 999),
                        'position' => 'Manual Payment',
                    ]);

                    $bankAccount = new \App\Models\BankAccount();
                    $bankAccount->employee_id = $employee->id;
                    $bankAccount->account_name = $entry['account_name'];
                    $bankAccount->account_number = $entry['account_number'];
                    $bankAccount->bsb_code = str_replace('-', '', $entry['bsb']);
                    $bankAccount->bank_name = 'Manual Entry';
                    $bankAccount->is_active = true;
                    $bankAccount->is_preferred = true;
                    $bankAccount->priority = 1;
                    $bankAccount->save();
                }

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'gross_wage' => $entry['amount'],
                    'net_pay' => $entry['amount'],
                    'regular_hours' => 0,
                    'overtime_hours' => 0,
                    'sunday_hours' => 0,
                    'holiday_hours' => 0,
                    'hours_worked' => 0,
                    'hourly_rate' => 0,
                    'overtime_rate' => 0,
                    'regular_pay' => 0,
                    'overtime_pay' => 0,
                    'sunday_pay' => 0,
                    'holiday_pay' => 0,
                    'allowance' => 0,
                    'tax' => 0,
                    'nasfund_ee' => 0,
                    'nasfund_er' => 0,
                    'loan_deduction' => 0,
                    'other_deductions' => 0,
                    'total_deductions' => 0,
                    'payment_method' => 'Bank Transfer',
                    'details' => json_encode([
                        'type' => 'manual_entry',
                        'description' => $entry['description'] ?? 'Manual Payment',
                    ])
                ]);
            }

            $payroll->calculateTotals();

            return response()->json([
                'success' => true,
                'message' => count($entries) . ' manual entries added successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a manual entry from payroll
     */
    public function deleteManualEntry($payrollItemId)
    {
        try {
            $payrollItem = PayrollItem::findOrFail($payrollItemId);
            
            $details = $payrollItem->details;
            if (!is_array($details) || !isset($details['type']) || $details['type'] !== 'manual_entry') {
                return response()->json([
                    'success' => false,
                    'message' => 'This is not a manual entry.'
                ], 400);
            }
            
            $payrollId = $payrollItem->payroll_id;
            $payrollItem->delete();
            
            $payroll = Payroll::find($payrollId);
            if ($payroll) {
                $payroll->calculateTotals();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Manual entry deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview payroll data in table format
     */
public function previewPayroll($payrollId, Request $request)
{
    try {
        $payroll = Payroll::with(['items.employee.bankAccounts' => function($query) {
            $query->where('is_active', true)
                ->orderBy('is_preferred', 'desc')
                ->orderBy('priority', 'asc');
        }])->findOrFail($payrollId);
        
        // ✅ GET DEBIT DESCRIPTION FROM REQUEST
        $debitDescription = $request->get('debit_description', '');
        
        $payrollItems = $payroll->items()
            ->where('net_pay', '>', 0)
            ->get()
            ->filter(function($item) {
                return $item->employee && 
                    $item->employee->bankAccounts && 
                    $item->employee->bankAccounts->isNotEmpty();
            });

        $data = [];
        foreach ($payrollItems as $item) {
            $employee = $item->employee;
            $bankAccount = $employee->bankAccounts()->where('is_active', true)->first();
            
            $bsb = $bankAccount->bsb_code ?? '';
            $bsb = preg_replace('/[^0-9]/', '', $bsb);
            if (strlen($bsb) >= 6) {
                $bsb = substr($bsb, 0, 3) . '-' . substr($bsb, 3, 3);
            }
            
            $details = $item->details;
            $isManualEntry = is_array($details) && isset($details['type']) && $details['type'] === 'manual_entry';
            
            // ✅ USE DEBIT DESCRIPTION FROM REQUEST
            $description = $debitDescription;
            if (!$description) {
                // Fallback to existing logic
                if ($isManualEntry && isset($details['description'])) {
                    $description = $details['description'];
                } elseif ($payroll->fortnight_number) {
                    $description = 'FN' . $payroll->fortnight_number;
                }
            }
            
            $data[] = [
                'id' => $item->id,
                'bsb' => $bsb,
                'account_number' => $bankAccount->account_number ?? '',
                'amount' => $item->net_pay,
                'account_name' => strtoupper($bankAccount->account_name ?? $employee->full_name),
                'description' => strtoupper($description),
                'is_manual_entry' => $isManualEntry,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $payroll->total_net ?? 0,
            'count' => count($data),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function saveAllEntries(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'payroll_id' => 'required|exists:payrolls,id',
            'entries' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $payroll = Payroll::findOrFail($request->payroll_id);
        $entries = $request->entries;
        $updatedCount = 0;
        $manualCount = 0;

        foreach ($entries as $entry) {
            if ($entry['type'] === 'payroll_item') {
                // ✅ Update existing payroll item
                $payrollItem = PayrollItem::find($entry['payroll_item_id']);
                if ($payrollItem) {
                    // Calculate the difference to adjust gross/net
                    $oldAmount = $payrollItem->net_pay;
                    $newAmount = $entry['amount'];
                    $difference = $newAmount - $oldAmount;
                    
                    // Update the payroll item
                    $payrollItem->net_pay = $newAmount;
                    $payrollItem->gross_wage = $payrollItem->gross_wage + $difference;
                    $payrollItem->save();
                    
                    $updatedCount++;
                }
            } else {
                // ✅ Create new manual entry
                $employee = Employee::where('company_id', $payroll->company_id)
                    ->where('full_name', 'LIKE', '%' . $entry['account_name'] . '%')
                    ->first();

                if (!$employee) {
                    $employee = Employee::create([
                        'company_id' => $payroll->company_id,
                        'first_name' => $entry['account_name'],
                        'last_name' => 'MANUAL',
                        'full_name' => $entry['account_name'],
                        'employee_type' => 'National',
                        'status' => 'Active',
                        'employee_number' => 'MANUAL-' . time() . rand(100, 999),
                        'position' => 'Manual Payment',
                    ]);

                    $bankAccount = new \App\Models\BankAccount();
                    $bankAccount->employee_id = $employee->id;
                    $bankAccount->account_name = $entry['account_name'];
                    $bankAccount->account_number = $entry['account_number'];
                    $bankAccount->bsb_code = str_replace('-', '', $entry['bsb']);
                    $bankAccount->bank_name = 'Manual Entry';
                    $bankAccount->is_active = true;
                    $bankAccount->is_preferred = true;
                    $bankAccount->priority = 1;
                    $bankAccount->save();
                }

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'gross_wage' => $entry['amount'],
                    'net_pay' => $entry['amount'],
                    'regular_hours' => 0,
                    'overtime_hours' => 0,
                    'sunday_hours' => 0,
                    'holiday_hours' => 0,
                    'hours_worked' => 0,
                    'hourly_rate' => 0,
                    'overtime_rate' => 0,
                    'regular_pay' => 0,
                    'overtime_pay' => 0,
                    'sunday_pay' => 0,
                    'holiday_pay' => 0,
                    'allowance' => 0,
                    'tax' => 0,
                    'nasfund_ee' => 0,
                    'nasfund_er' => 0,
                    'loan_deduction' => 0,
                    'other_deductions' => 0,
                    'total_deductions' => 0,
                    'payment_method' => 'Bank Transfer',
                    'details' => json_encode([
                        'type' => 'manual_entry',
                        'description' => $entry['description'] ?? 'Manual Payment',
                    ])
                ]);
                $manualCount++;
            }
        }

        // Recalculate payroll totals
        $payroll->calculateTotals();

        return response()->json([
            'success' => true,
            'message' => $updatedCount . ' payroll items updated and ' . $manualCount . ' manual entries added.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
}
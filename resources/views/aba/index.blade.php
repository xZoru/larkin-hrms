@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Generate ABA Bank File
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / ABA Bank File
        </div>
    </div>
@endsection

@section('content')
<style>
    .aba-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .aba-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .aba-header .header-subtitle {
        font-size: 14px;
        color: #a0aec0;
    }
    .stat-box {
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px 16px;
        text-align: center;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }
    .stat-box:hover {
        border-color: #6366f1;
        background: #f5f3ff;
    }
    .stat-box .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #1a1f36;
    }
    .stat-box .stat-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .stat-box .stat-value.blue { color: #2563eb; }
    .stat-box .stat-value.green { color: #16a34a; }
    .stat-box .stat-value.purple { color: #7c3aed; }
    .stat-box .stat-value.orange { color: #ea580c; }
    
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .form-card .card-header {
        background: #f8fafc;
        padding: 14px 20px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        color: #1a1f36;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-card .card-header i {
        color: #6366f1;
    }
    .form-card .card-body {
        padding: 20px;
    }
    .form-control-custom {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
        background: white;
    }
    .form-control-custom:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .form-control-custom:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
    }
    .form-label-custom {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }
    .form-label-custom .required {
        color: #ef4444;
        margin-left: 2px;
    }
    .btn-create {
        background: #4f46e5;
        color: white;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-create:hover {
        background: #4338ca;
    }
    .btn-preview {
        background: #0ea5e9;
        color: white;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-preview:hover {
        background: #0284c7;
    }
    .btn-secondary-custom {
        background: #e2e8f0;
        color: #475569;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-secondary-custom:hover {
        background: #cbd5e1;
    }
    .alert-custom {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .alert-custom.alert-info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }
    .alert-custom.alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .alert-custom.alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .alert-custom.alert-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }
    .alert-custom .icon {
        font-size: 20px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .alert-custom ul {
        margin: 0;
        padding-left: 18px;
    }
    .alert-custom ul li {
        margin-bottom: 2px;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
    }
    .grid-4 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 768px) {
        .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
        .stat-box .stat-value { font-size: 16px; }
        .aba-header { padding: 16px; }
        .aba-header .header-title { font-size: 16px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="aba-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-file-invoice text-indigo-300 mr-2"></i> Generate ABA Bank File
                    </div>
                    <div class="header-subtitle mt-1">
                        Create ABA payment files for bank processing
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('aba.history') }}" class="btn-secondary-custom" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-history"></i> History
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert-custom alert-success">
                <span class="icon">✅</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-custom alert-danger">
                <span class="icon">❌</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert-custom alert-warning">
                <span class="icon">⚠️</span>
                <div>{{ session('warning') }}</div>
            </div>
        @endif
        <!-- Generation Form -->
        <div class="form-card">
            <div class="card-header">
                Generate ABA File
            </div>
            <div class="card-body">
                <form action="{{ route('aba.generate') }}" method="POST" id="abaForm">
                    @csrf
                    
                    <div class="grid-2">
                        <div>
                            <label class="form-label-custom">
                                Company <span class="required">*</span>
                            </label>
                            <select name="company_id" id="company_id" class="form-control-custom @error('company_id') is-invalid @enderror" 
                                    onchange="window.location.href='/aba?company_id=' + this.value" required>
                                <option value="">Select Company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <span class="text-danger text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="form-label-custom">
                                Payroll Period <span class="required">*</span>
                            </label>
                            <select name="payroll_id" id="payroll_id" class="form-control-custom @error('payroll_id') is-invalid @enderror" required>
                                <option value="">Select Payroll</option>
                                @forelse($payrolls as $payroll)
                                    <option value="{{ $payroll->id }}" {{ old('payroll_id') == $payroll->id ? 'selected' : '' }}>
                                        @if($payroll->fortnight_number)
                                            FN{{ $payroll->fortnight_number }}
                                        @elseif($payroll->period_start && $payroll->period_end)
                                            {{ $payroll->period_start->format('d/m/Y') }} - {{ $payroll->period_end->format('d/m/Y') }}
                                        @else
                                            Payroll #{{ $payroll->id }}
                                        @endif
                                        - {{ $payroll->created_at->format('d/m/Y') }}
                                        [{{ $payroll->status }}]
                                        @if($payroll->total_net > 0)
                                            - K {{ number_format($payroll->total_net, 2) }}
                                        @endif
                                    </option>
                                @empty
                                    <option value="" disabled>No payrolls found</option>
                                @endforelse
                            </select>
                            @error('payroll_id')
                                <span class="text-danger text-sm">{{ $message }}</span>
                            @enderror
                            
                            <div id="payrollInfo" class="mt-2" style="display:none;">
                                <div class="alert-custom alert-info" style="padding: 8px 12px;">
                                    <span id="payrollDetails"></span>
                                </div>
                            </div>
                            
                            @if($payrolls->isEmpty())
                                <div class="text-danger text-sm mt-1">
                                    <i class="fas fa-exclamation-circle"></i> No payrolls found. Please process a payroll first.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Bank Details -->
                    <div class="form-card mt-4" style="border: 1px solid #e5e7eb; border-radius: 8px;">
                        <div class="card-header" style="background: #fafafa;">
                         Company Bank Details
                        </div>
                        <div class="card-body">
                            <div class="grid-4">
                                <div>
                                    <label class="form-label-custom">Bank Name</label>
                                    <input type="text" name="bank_name" id="bank_name" 
                                           class="form-control-custom" 
                                           placeholder="e.g., BSP Bank"
                                           value="{{ old('bank_name', $companyBankDetails['bank_name'] ?? '') }}">
                                    <div class="text-xs text-gray-400 mt-1">Name of the bank</div>
                                </div>
                                <div>
                                    <label class="form-label-custom">BSB Number</label>
                                    <input type="text" name="bsb_number" id="bsb_number" 
                                           class="form-control-custom" 
                                           placeholder="e.g., 088-950"
                                           value="{{ old('bsb_number', $companyBankDetails['bsb_code'] ?? '') }}">
                                    <div class="text-xs text-gray-400 mt-1">6-digit BSB code</div>
                                </div>
                                <div>
                                    <label class="form-label-custom">Account Number</label>
                                    <input type="text" name="bank_account_number" id="bank_account_number" 
                                           class="form-control-custom" 
                                           placeholder="e.g., 7009276416"
                                           value="{{ old('bank_account_number', $companyBankDetails['bank_account_number'] ?? '') }}">
                                    <div class="text-xs text-gray-400 mt-1">Bank account number</div>
                                </div>
                                <div>
                                    <label class="form-label-custom">Account Name</label>
                                    <input type="text" name="bank_account_name" id="bank_account_name" 
                                           class="form-control-custom" 
                                           placeholder="e.g., LARKIN ENTERPRISES LTD"
                                           value="{{ old('bank_account_name', $companyBankDetails['bank_account_name'] ?? '') }}">
                                    <div class="text-xs text-gray-400 mt-1">Name on the account</div>
                                </div>
                            </div>
                            <div class="grid-4 mt-3">
                                <div>
                                    <label class="form-label-custom">Type of Payment</label>
                                    <select name="payment_type" id="payment_type" class="form-control-custom">
                                        <option value="SALARY" {{ old('payment_type', 'SALARY') == 'SALARY' ? 'selected' : '' }}>SALARY</option>
                                        <option value="WAGES" {{ old('payment_type') == 'WAGES' ? 'selected' : '' }}>WAGES</option>
                                        <option value="COMMISSION" {{ old('payment_type') == 'COMMISSION' ? 'selected' : '' }}>COMMISSION</option>
                                        <option value="BONUS" {{ old('payment_type') == 'BONUS' ? 'selected' : '' }}>BONUS</option>
                                        <option value="SUPERANNUATION" {{ old('payment_type') == 'SUPERANNUATION' ? 'selected' : '' }}>SUPERANNUATION</option>
                                    </select>
                                    <div class="text-xs text-gray-400 mt-1">Payment description</div>
                                </div>
                                <div>
                                    <label class="form-label-custom">Debit Description</label>
                                    <input type="text" name="debit_description" id="debit_description" 
                                           class="form-control-custom" 
                                           placeholder="e.g., FN2613"
                                           value="{{ old('debit_description', 'FN' . ($currentFortnight ?? '')) }}">
                                    <div class="text-xs text-gray-400 mt-1">Reference for the payment</div>
                                </div>
                                <div>
                                    <label class="form-label-custom">Payment Date</label>
                                    <input type="date" name="payment_date" id="payment_date" 
                                           class="form-control-custom" 
                                           value="{{ old('payment_date', now()->format('Y-m-d')) }}">
                                    <div class="text-xs text-gray-400 mt-1">Date of payment</div>
                                </div>
                                <div>
                                    <label class="form-label-custom">Total Amount</label>
                                    <input type="text" id="total_amount_display" 
                                           class="form-control-custom" 
                                           value="K 0.00" readonly disabled>
                                    <div class="text-xs text-gray-400 mt-1">Calculated from payroll</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="alert-custom alert-info mt-4">
                        <span class="icon">📋</span>
                        <div>
                            <strong class="block mb-1">ABA File Details</strong>
                            <ul>
                                <li>Generated for employees with valid BSB and bank account numbers</li>
                                <li>Format: Australian Banking Association (ABA) standard</li>
                                <li>Includes: Employee name, BSB, Account number, Amount</li>
                                <li>File will be saved for future reference</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t border-gray-200">
                        <button type="button" class="btn-preview" onclick="showPreview()">
                            <i class="fas fa-eye"></i> Preview & Edit
                        </button>
                        <button type="submit" class="btn-create" id="generateBtn">
                            <i class="fas fa-file-alt"></i> Generate ABA File
                        </button>
                        <button type="reset" class="btn-secondary-custom">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Debug Information -->
        @if($payrolls->isEmpty())
        <div class="alert-custom alert-warning">
            <span class="icon">🔍</span>
            <div>
                <strong>Debug Information</strong>
                <ul class="mt-2">
                    <li><strong>Company ID:</strong> {{ $companyId ?? 'Not set' }}</li>
                    <li><strong>Payrolls Found:</strong> {{ $payrolls->count() }}</li>
                </ul>
                @php
                    $allPayrolls = \App\Models\Payroll::where('company_id', $companyId)->get();
                @endphp
                @if($allPayrolls->isNotEmpty())
                    <div class="mt-2 overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-3 py-2 text-left">ID</th>
                                    <th class="px-3 py-2 text-left">Fortnight</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-right">Total Net</th>
                                    <th class="px-3 py-2 text-left">Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allPayrolls as $p)
                                    <tr class="border-b border-gray-200">
                                        <td class="px-3 py-2">{{ $p->id }}</td>
                                        <td class="px-3 py-2">{{ $p->fortnight_number ?? 'N/A' }}</td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium 
                                                {{ $p->status == 'Approved' ? 'bg-green-100 text-green-800' : ($p->status == 'Draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600') }}">
                                                {{ $p->status }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right">K {{ number_format($p->total_net ?? 0, 2) }}</td>
                                        <td class="px-3 py-2">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog" style="max-width: 95%;">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%); color: white; border: none;">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> ABA Payment Preview
                </h5>
                <button type="button" class="close text-white" onclick="closePreview()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="previewLoader" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="text-gray-500 mt-2">Loading preview...</p>
                </div>
                <div id="previewContent" style="display:none;">
                    
                    <!-- Summary Stats -->
                    <div class="grid-3 mb-4">
                        <div class="stat-box">
                            <div class="stat-value blue" id="previewCount">0</div>
                            <div class="stat-label">Total Employees</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value green" id="previewTotal">K 0.00</div>
                            <div class="stat-label">Total Amount</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value orange" id="manualCount">0</div>
                            <div class="stat-label">Manual Entries</div>
                        </div>
                    </div>

                    <!-- Add Manual Entry Form -->
                    <div class="form-card">
                        <div class="card-header" style="background: #fafafa;">
                            <i class="fas fa-plus-circle text-success"></i> Add Manual Payment
                        </div>
                        <div class="card-body">
                            <div class="grid-4">
                                <div>
                                    <input type="text" id="manual_bsb" class="form-control-custom" placeholder="BSB (e.g., 088-294)">
                                </div>
                                <div>
                                    <input type="text" id="manual_account" class="form-control-custom" placeholder="Account Number">
                                </div>
                                <div>
                                    <input type="number" id="manual_amount" class="form-control-custom" placeholder="Amount" step="0.01">
                                </div>
                                <div>
                                    <input type="text" id="manual_name" class="form-control-custom" placeholder="Account Name">
                                </div>
                            </div>
                            <div class="flex items-center gap-3 mt-3">
                                <input type="text" id="manual_description" class="form-control-custom" placeholder="Description (e.g., FN2613, Bonus, Contractor Payment)" style="flex: 1;">
                                <button type="button" class="btn-create" onclick="addManualRow()" style="white-space: nowrap;">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="overflow-x-auto mt-4 border border-gray-200 rounded-lg">
                        <table class="w-full text-sm" id="previewTable">
                            <thead style="background: #f1f5f9;">
                                <tr>
                                    <th class="px-3 py-2 text-left" style="width: 30px;">#</th>
                                    <th class="px-3 py-2 text-left">BSB</th>
                                    <th class="px-3 py-2 text-left">Account Number</th>
                                    <th class="px-3 py-2 text-right" style="width: 120px;">Amount</th>
                                    <th class="px-3 py-2 text-left">Account Name</th>
                                    <th class="px-3 py-2 text-left">Description</th>
                                    <th class="px-3 py-2 text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody" class="divide-y divide-gray-200">
                            </tbody>
                            <tfoot style="background: #f1f5f9; font-weight: 600;">
                                <tr>
                                    <td colspan="3" class="px-3 py-2 text-right">TOTAL:</td>
                                    <td id="previewTotalFoot" class="px-3 py-2 text-right text-green-600">0.00</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        Total: <span id="previewCount2">0</span> entries
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-gray-200">
                <button type="button" class="btn-secondary-custom" onclick="closePreview()">Close</button>
                <button type="button" class="btn-create" onclick="saveManualEntries()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============ COMPANY SWITCHER ============
    var companySelector = document.getElementById('company_id');
    if (companySelector) {
        companySelector.addEventListener('change', function() {
            var companyId = this.value;
            if (companyId) {
                window.location.href = '/aba?company_id=' + companyId;
            }
        });
    }

    // ============ PAYROLL DETAILS ============
    var payrollSelector = document.getElementById('payroll_id');
    if (payrollSelector) {
        payrollSelector.addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            var payrollInfo = document.getElementById('payrollInfo');
            var payrollDetails = document.getElementById('payrollDetails');
            
            if (selected && selected.value) {
                if (payrollDetails) payrollDetails.innerHTML = 'Selected: ' + selected.text;
                if (payrollInfo) payrollInfo.style.display = 'block';
                
                // Update total amount
                var match = selected.text.match(/K\s+([\d,]+\.\d{2})/);
                var totalDisplay = document.getElementById('total_amount_display');
                if (totalDisplay && match) {
                    totalDisplay.value = 'K ' + match[1];
                }
            } else {
                if (payrollInfo) payrollInfo.style.display = 'none';
            }
        });
    }

    // ============ FORM SUBMIT ============
    var abaForm = document.getElementById('abaForm');
    var generateBtn = document.getElementById('generateBtn');
    
    if (abaForm) {
        abaForm.addEventListener('submit', function() {
            if (generateBtn) {
                generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
                generateBtn.disabled = true;
            }
        });
    }
});

// ============ GLOBAL FUNCTIONS FOR PREVIEW ============
var manualEntries = [];
var payrollData = [];

function showPreview() {
    var payrollId = document.getElementById('payroll_id').value;
    var debitDescription = document.getElementById('debit_description').value; // ✅ GET THE VALUE
    
    if (!payrollId) {
        alert('Please select a payroll first.');
        return;
    }
    
    // Show modal
    var modal = document.getElementById('previewModal');
    if (modal) {
        $('#previewModal').modal('show');
    }
    
    document.getElementById('previewLoader').style.display = 'block';
    document.getElementById('previewContent').style.display = 'none';
    document.getElementById('previewTableBody').innerHTML = '';
    manualEntries = [];
    
    // ✅ PASS DEBIT DESCRIPTION TO THE AJAX CALL
    fetch('/aba/preview-payroll/' + payrollId + '?debit_description=' + encodeURIComponent(debitDescription))
        .then(response => response.json())
        .then(response => {
            document.getElementById('previewLoader').style.display = 'none';
            
            if (response.success) {
                payrollData = response.data.map(function(item) {
                    if (item.is_manual_entry) {
                        item.is_saved_manual = true;
                        item.is_manual = false;
                    }
                    return item;
                });
                renderTable();
            } else {
                document.getElementById('previewContent').innerHTML = 
                    '<div class="alert-custom alert-warning">' + (response.message || 'No data found') + '</div>';
                document.getElementById('previewContent').style.display = 'block';
            }
        })
        .catch(function(error) {
            document.getElementById('previewLoader').style.display = 'none';
            document.getElementById('previewContent').innerHTML = 
                '<div class="alert-custom alert-danger">Failed to load preview</div>';
            document.getElementById('previewContent').style.display = 'block';
        });
}

function renderTable() {
    var html = '';
    var allData = payrollData.concat(manualEntries);
    
    if (allData.length === 0) {
        html += '<tr><td colspan="7" class="px-3 py-4 text-center text-gray-500">No entries found</td></tr>';
    } else {
        allData.forEach(function(item, index) {
            // ✅ Check if this is a manual entry (either from database or new)
            var isManual = item.is_manual || false;
            var isSavedManual = item.is_manual_entry || false;
            var isManualRow = isManual || isSavedManual;
            
            var rowClass = isManualRow ? 'bg-yellow-50' : '';
            var dataIndex = index;
            
            html += '<tr class="' + rowClass + '" data-index="' + dataIndex + '">';
            html += '<td class="px-3 py-2">' + (index + 1) + (isManualRow ? ' <span class="px-1.5 py-0.5 bg-yellow-200 text-yellow-800 rounded text-xs font-medium">Manual</span>' : '') + '</td>';
            html += '<td class="px-3 py-2">' + (item.bsb || '') + '</td>';
            html += '<td class="px-3 py-2">' + (item.account_number || '') + '</td>';
            
            html += '<td class="px-3 py-2 text-right">';
            html += '<input type="number" class="form-control form-control-sm amount-input" style="width: 120px; text-align: right; display: inline-block; padding: 4px 8px; border: 1px solid #d1d5db; border-radius: 4px;" ';
            html += 'value="' + parseFloat(item.amount || 0).toFixed(2) + '" ';
            html += 'step="0.01" min="0" ';
            html += 'data-index="' + dataIndex + '" ';
            html += 'onchange="updateAmount(' + dataIndex + ', this.value)">';
            html += '</td>';
            
            html += '<td class="px-3 py-2">' + (item.account_name || '') + '</td>';
            html += '<td class="px-3 py-2">' + (item.description || '') + '</td>';
            html += '<td class="px-3 py-2 text-center">';
            
            if (isManual) {
                // New manual entry (not saved yet)
                html += '<button type="button" class="btn btn-danger btn-sm" style="padding: 2px 8px; font-size: 12px; border: none; border-radius: 4px; background: #ef4444; color: white; cursor: pointer;" onclick="removeManualRow(' + index + ')">';
                html += '<i class="fas fa-times"></i>';
                html += '</button>';
            } else if (isSavedManual) {
                // Saved manual entry from database - can delete
                html += '<button type="button" class="btn btn-danger btn-sm" style="padding: 2px 8px; font-size: 12px; border: none; border-radius: 4px; background: #ef4444; color: white; cursor: pointer;" onclick="deleteSavedManual(' + item.id + ')">';
                html += '<i class="fas fa-trash"></i>';
                html += '</button>';
            } else {
                html += '<span class="text-gray-400 text-xs">-</span>';
            }
            
            html += '</td>';
            html += '</tr>';
        });
    }
    
    document.getElementById('previewTableBody').innerHTML = html;
    updateTotals();
    document.getElementById('previewContent').style.display = 'block';
}
function updateAmount(index, value) {
    var amount = parseFloat(value);
    if (isNaN(amount) || amount < 0) {
        amount = 0;
    }
    
    if (index < payrollData.length) {
        payrollData[index].amount = amount;
    } else {
        var manualIndex = index - payrollData.length;
        if (manualIndex < manualEntries.length) {
            manualEntries[manualIndex].amount = amount;
        }
    }
    
    updateTotals();
}

function addManualRow() {
    var bsb = document.getElementById('manual_bsb').value.trim();
    var account = document.getElementById('manual_account').value.trim();
    var amount = parseFloat(document.getElementById('manual_amount').value);
    var name = document.getElementById('manual_name').value.trim();
    var description = document.getElementById('manual_description').value.trim() || document.getElementById('debit_description').value || 'MANUAL';
    
    if (!bsb || !account || !amount || !name) {
        alert('Please fill in all fields (BSB, Account Number, Amount, Account Name)');
        return;
    }
    
    manualEntries.push({
        bsb: bsb,
        account_number: account,
        amount: amount,
        account_name: name.toUpperCase(),
        description: description,
        is_manual: true
    });
    
    document.getElementById('manual_bsb').value = '';
    document.getElementById('manual_account').value = '';
    document.getElementById('manual_amount').value = '';
    document.getElementById('manual_name').value = '';
    document.getElementById('manual_description').value = '';
    
    renderTable();
}

function removeManualRow(index) {
    if (confirm('Remove this manual entry?')) {
        manualEntries.splice(index, 1);
        renderTable();
    }
}

function deleteSavedManual(payrollItemId) {
    if (!confirm('Delete this manual entry? This action cannot be undone.')) {
        return;
    }
    
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch('/aba/delete-manual-entry/' + payrollItemId, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(response => {
        if (response.success) {
            alert('Manual entry deleted successfully!');
            showPreview();
        } else {
            alert('Failed to delete: ' + (response.message || 'Unknown error'));
        }
    })
    .catch(function(error) {
        alert('Failed to delete: Server error');
    });
}

function updateTotals() {
    var allData = payrollData.concat(manualEntries);
    var total = 0;
    allData.forEach(function(item) {
        total += parseFloat(item.amount || 0);
    });
    
    document.getElementById('previewTotal').textContent = 'K ' + total.toFixed(2);
    document.getElementById('previewTotalFoot').textContent = total.toFixed(2);
    document.getElementById('previewCount').textContent = allData.length;
    document.getElementById('previewCount2').textContent = allData.length;
    document.getElementById('manualCount').textContent = manualEntries.length;
}

function closePreview() {
    var modal = document.getElementById('previewModal');
    if (modal) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var myModal = bootstrap.Modal.getInstance(modal);
            if (myModal) {
                myModal.hide();
            }
        } else {
            // Fallback: hide manually
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            
            // Remove backdrop
            var backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(function(el) {
                el.remove();
            });
        }
    }
}
function saveManualEntries() {
    var allEntries = [];
    
    // ✅ 1. Get all edited payroll items (existing employees)
    var tableRows = document.querySelectorAll('#previewTableBody tr');
    tableRows.forEach(function(row, index) {
        var dataIndex = row.dataset.index;
        var amountInput = row.querySelector('.amount-input');
        var isManual = row.querySelector('.bg-yellow-50') !== null;
        
        // Check if this is a manual entry or payroll item
        if (dataIndex < payrollData.length) {
            // ✅ This is an existing payroll item - save it
            var item = payrollData[dataIndex];
            allEntries.push({
                id: item.id,
                type: 'payroll_item',
                payroll_item_id: item.id,
                amount: parseFloat(amountInput.value) || 0,
                account_name: item.account_name,
                account_number: item.account_number,
                bsb: item.bsb,
                description: item.description || 'UPDATE'
            });
        }
    });
    
    // ✅ 2. Get manual entries
    manualEntries.forEach(function(item, index) {
        var globalIndex = payrollData.length + index;
        var amountInput = document.querySelector('#previewTableBody tr[data-index="' + globalIndex + '"] .amount-input');
        var amount = amountInput ? parseFloat(amountInput.value) : item.amount;
        
        allEntries.push({
            type: 'manual_entry',
            bsb: item.bsb,
            account_number: item.account_number,
            amount: amount || 0,
            account_name: item.account_name,
            description: item.description || 'MANUAL'
        });
    });
    
    if (allEntries.length === 0) {
        alert('No entries to save.');
        return;
    }
    
    var payrollId = document.getElementById('payroll_id').value;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // ✅ Save ALL entries (both payroll items and manual)
    fetch('/aba/save-all-entries', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            payroll_id: payrollId,
            entries: allEntries
        })
    })
    .then(response => response.json())
    .then(response => {
        if (response.success) {
            alert('All changes saved successfully!');
            manualEntries = [];
            showPreview();
        } else {
            alert('Failed to save: ' + (response.message || 'Unknown error'));
        }
    })
    .catch(function(error) {
        alert('Failed to save: Server error');
    });
}
</script>
@endsection
@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Loan Requests
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / Loan Requests
        </div>
    </div>
@endsection

@section('content')
<style>
    .loan-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .loan-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .loan-header .header-subtitle {
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
    .stat-box .stat-value.red { color: #dc2626; }
    
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 24px;
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
    
    .table-loan-requests {
        font-size: 13px;
    }
    .table-loan-requests thead th {
        background: #f1f5f9;
        color: #475569;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        padding: 10px 12px;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .table-loan-requests tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-loan-requests tbody tr:hover {
        background: #f8fafc;
    }
    .table-loan-requests .employee-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 13px;
    }
    .table-loan-requests .employee-number {
        font-size: 11px;
        color: #94a3b8;
    }
    .table-loan-requests .amount {
        font-weight: 600;
    }
    .table-loan-requests .amount.positive { color: #16a34a; }
    .table-loan-requests .amount.negative { color: #dc2626; }
    
    .badge-status {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.pending {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-status.approved {
        background: #dbeafe;
        color: #1e40af;
    }
    .badge-status.released {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.completed {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.on-hold {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-status.rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    .badge-status.cancelled {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .btn-action {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        border: none;
        cursor: pointer;
    }
    .btn-action.approve {
        background: #dbeafe;
        color: #1e40af;
    }
    .btn-action.approve:hover {
        background: #bfdbfe;
    }
    .btn-action.release {
        background: #dcfce7;
        color: #166534;
    }
    .btn-action.release:hover {
        background: #bbf7d0;
    }
    .btn-action.reject {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.reject:hover {
        background: #fecaca;
    }
    .btn-action.view {
        background: #f1f5f9;
        color: #475569;
    }
    .btn-action.view:hover {
        background: #e2e8f0;
    }
    .btn-action.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.delete:hover {
        background: #fecaca;
    }
    
    .btn-add-row {
        background: #4f46e5;
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-add-row:hover {
        background: #4338ca;
        color: white;
    }
    .btn-submit {
        background: #22c55e;
        color: white;
        padding: 8px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-submit:hover {
        background: #16a34a;
    }
    .btn-filter {
        padding: 4px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #e2e8f0;
        background: white;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-filter:hover {
        background: #f1f5f9;
    }
    .btn-filter.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }
    .btn-filter.active:hover {
        background: #4338ca;
    }
    
    .filter-group {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
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
    .form-control-custom-sm {
        padding: 6px 10px;
        font-size: 13px;
        border-radius: 6px;
    }
    .form-control-custom:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
    }
    
    .selected-employee-display {
        font-size: 13px;
        color: #0f172a;
        padding: 6px 10px;
        background: #f8fafc;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        min-height: 38px;
        display: flex;
        align-items: center;
    }
    .selected-employee-display .text-muted {
        color: #94a3b8;
        font-size: 12px;
    }
    
    .deduction-badge {
        background: #dbeafe;
        color: #1e40af;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        display: inline-block;
    }
    
    .remove-row-btn {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        border: none;
        background: #fee2e2;
        color: #991b1b;
        cursor: pointer;
        transition: background 0.2s;
    }
    .remove-row-btn:hover {
        background: #fecaca;
    }
    
    .empty-state {
        text-align: center;
        padding: 48px 20px;
    }
    .empty-state .icon {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 16px;
    }
    .empty-state h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1a1f36;
        margin-bottom: 8px;
    }
    .empty-state p {
        color: #6b7280;
        margin-bottom: 16px;
    }
    
    .alert-custom {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
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
    .alert-custom .icon {
        font-size: 18px;
        flex-shrink: 0;
    }
    
    @media (max-width: 768px) {
        .loan-header { padding: 16px; }
        .loan-header .header-title { font-size: 16px; }
        .table-loan-requests { font-size: 11px; }
        .table-loan-requests thead th, .table-loan-requests tbody td { padding: 6px 8px; }
        .stat-box .stat-value { font-size: 16px; }
        .form-card .card-body { padding: 12px; }
        .filter-group { gap: 2px; }
        .btn-filter { font-size: 10px; padding: 3px 8px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="loan-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-hand-holding-usd text-indigo-300 mr-2"></i> Loan Requests
                    </div>
                    <div class="header-subtitle mt-1">
                        Manage employee loan requests, approvals, and releases
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('dashboard') }}" class="btn-secondary" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

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



        <!-- ============ NEW LOAN REQUEST FORM ============ -->
        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i> New Loan Request
            </div>
            <div class="card-body">
                <form action="{{ route('loan-requests.store') }}" method="POST">
                    @csrf
                    
                    <div class="overflow-x-auto">
                        <table class="table-loan-requests w-full" id="loanRequestTable">
                            <thead>
                                <tr>
                                    <th style="width: 18%;">Employee</th>
                                    <th style="width: 18%;">Selected Employee</th>
                                    <th style="width: 10%;">Amount</th>
                                    <th style="width: 14%;">Loan Type</th>
                                    <th style="width: 12%;">Deduction / Fortnight</th>
                                    <th style="width: 18%;">Reason</th>
                                    <th style="width: 10%;">Est. Fortnights</th>
                                    <th style="width: 6%;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="loanRows">
                                <tr class="loan-row">
                                    <td>
                                        <select name="loans[0][employee_id]" class="form-control-custom form-control-custom-sm searchable-select" required>
                                            <option value="">Search employee...</option>
                                            @foreach($employees ?? [] as $employee)
                                                <option value="{{ $employee->id }}" data-code="{{ $employee->employee_number }}">
                                                    {{ $employee->employee_number }} | {{ $employee->full_name ?? ($employee->first_name . ' ' . $employee->last_name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="selected-employee" data-code="" data-name="">
                                        <span class="text-muted" style="font-size: 12px;">Select an employee</span>
                                    </td>
                                    <td>
                                        <input type="number" name="loans[0][amount]" class="form-control-custom form-control-custom-sm amount-input" placeholder="Amount" step="0.01" min="1">
                                    </td>
                                    <td>
                                        <select name="loans[0][loan_type]" class="form-control-custom form-control-custom-sm loan-type-select" required>
                                            <option value="">Select Type</option>
                                            <option value="Cash Advance">Cash Advance</option>
                                            <option value="Loan">Loan</option>
                                            <option value="Company Deductions">Company Deductions</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="loans[0][deduction_per_cutoff]" class="form-control-custom form-control-custom-sm deduction-input" placeholder="Amount per fortnight" step="0.01" min="0.01" required>
                                    </td>
                                    <td>
                                        <input type="text" name="loans[0][reason]" class="form-control-custom form-control-custom-sm" placeholder="Reason">
                                    </td>
                                    <td class="deduction-cell text-center">
                                        <span class="deduction-badge">-</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="remove-row-btn" style="display:none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <button type="button" class="btn-add-row" id="addRowBtn">
                            <i class="fas fa-plus"></i> Add Row
                        </button>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Submit All
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============ LOAN LIST ============ -->
        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-list"></i> Loan List
                <div class="ml-auto">
                    <div class="filter-group">
                        <button class="btn-filter active" data-filter="all">All</button>
                        <button class="btn-filter" data-filter="Pending">Pending</button>
                        <button class="btn-filter" data-filter="Approved">Approved</button>
                        <button class="btn-filter" data-filter="Released">Released</button>
                        <button class="btn-filter" data-filter="Completed">Completed</button>
                        <button class="btn-filter" data-filter="Rejected">Rejected</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="bulkLoanActionForm" method="POST" class="mb-3 flex flex-wrap items-center gap-2">
                    @csrf
                    <button type="submit" formaction="{{ route('loan-requests.bulk-approve') }}" class="px-3 py-2 rounded bg-green-600 text-white text-sm" onclick="return confirm('Approve all selected pending loan requests?');">Approve selected</button>
                    <button type="submit" formaction="{{ route('loan-requests.bulk-release') }}" class="px-3 py-2 rounded bg-blue-600 text-white text-sm" onclick="return confirm('Release all selected approved loan requests?');">Release selected</button>
                    <span class="text-sm text-gray-500">Select Pending loans to approve or Approved loans to release.</span>
                </form>
                <div class="overflow-x-auto">
                    <table class="table-loan-requests w-full" id="loanListTable">
                        <thead>
                            <tr>
                                <th class="text-center"><input type="checkbox" id="selectAllLoans" aria-label="Select all loans"></th>
                                <th class="text-left">Employee</th>
                                <th class="text-left">Loan Type</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Deduction / FN</th>
                                <th class="text-right">Remaining</th>
                                <th class="text-right">Total Paid</th>
                                <th class="text-left">Reason</th>
                                <th class="text-center">Status</th>
                                <th class="text-left">Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $loan)
                            <tr data-status="{{ $loan->status }}">
                                <td class="text-center"><input type="checkbox" class="loan-select" form="bulkLoanActionForm" name="loan_ids[]" value="{{ $loan->id }}" aria-label="Select loan for {{ $loan->employee->full_name }}"></td>
                                <td>
                                    <div class="employee-name">{{ $loan->employee->first_name ?? '' }} {{ $loan->employee->last_name ?? '' }}</div>
                                    <div class="employee-number">{{ $loan->employee->employee_number ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <span class="badge-status" style="background: #dbeafe; color: #1e40af;">{{ $loan->loan_type }}</span>
                                </td>
                                <td class="text-right amount">K {{ number_format($loan->amount, 2) }}</td>
                                <td class="text-right">K {{ number_format($loan->deduction_per_cutoff, 2) }}</td>
                                <td class="text-right">
                                    <span class="amount {{ $loan->remaining_balance > 0 ? 'negative' : 'positive' }}">
                                        K {{ number_format($loan->remaining_balance, 2) }}
                                    </span>
                                </td>
                                <td class="text-right amount positive">K {{ number_format($loan->total_paid, 2) }}</td>
                                <td class="text-left" style="font-size: 12px; color: #64748b;">{{ $loan->reason ?? '-' }}</td>
                                <td class="text-center">
                                    @php
                                        $statusClasses = [
                                            'Pending' => 'pending',
                                            'Approved' => 'approved',
                                            'Released' => 'released',
                                            'On-Hold' => 'on-hold',
                                            'Rejected' => 'rejected',
                                            'Completed' => 'completed',
                                            'Cancelled' => 'cancelled'
                                        ];
                                        $statusDisplay = $statusClasses[$loan->status] ?? 'pending';
                                    @endphp
                                    <span class="badge-status {{ $statusDisplay }}">{{ $loan->status }}</span>
                                </td>
                                <td style="font-size: 12px; color: #64748b;">{{ $loan->created_at->format('d/m/y') }}</td>
                                <td>
                                    <div class="flex items-center justify-center gap-1">
                                        @if($loan->canBeApproved())
                                            <button class="btn-action approve approve-btn" data-id="{{ $loan->id }}" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        @if($loan->canBeReleased())
                                            <button class="btn-action release release-btn" data-id="{{ $loan->id }}" title="Release">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                        @if($loan->canBeRejected())
                                            <button class="btn-action reject reject-btn" data-id="{{ $loan->id }}" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->can('correct-loan-balances'))
                                            <a href="{{ route('loan-requests.edit-balance', $loan) }}" class="btn-action" title="Edit loan balance">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('loan-requests.destroy', $loan) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this loan request? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11">
                                    <div class="empty-state">
                                        <div class="icon">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </div>
                                        <h3>No Loan Requests</h3>
                                        <p>No loan requests have been submitted yet.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Showing {{ $loans->firstItem() ?? 0 }} to {{ $loans->lastItem() ?? 0 }} of {{ $loans->total() }} entries
                    </div>
                    <div>
                        {{ $loans->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowCount = 1;

    // ============ HELPER FUNCTION: Estimate Fortnights From Deduction ============
    // Deduction per fortnight is now the actual input; this just shows the user
    // roughly how many fortnights it will take to pay off - purely informational,
    // not submitted with the form.
    function triggerDeductionCalculation(row) {
        var amount = parseFloat(row.querySelector('.amount-input').value) || 0;
        var deductionInput = row.querySelector('.deduction-input');
        var deduction = parseFloat(deductionInput.value) || 0;

        var badge = row.querySelector('.deduction-cell .deduction-badge');
        if (!badge) return;

        if (amount <= 0 || deduction <= 0) {
            badge.textContent = '-';
            return;
        }

        var fortnights = Math.ceil(amount / deduction);
        badge.textContent = fortnights + (fortnights === 1 ? ' cutoff' : ' cutoffs');
    }

    // ============ ADD ROW ============
    document.getElementById('addRowBtn').addEventListener('click', function() {
        var firstRow = document.querySelector('.loan-row');
        var newRow = firstRow.cloneNode(true);
        var newIndex = rowCount;
        
        // Update name attributes
        newRow.querySelectorAll('select, input').forEach(function(el) {
            var name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/\[0\]/, '[' + newIndex + ']'));
            }
            if (el.tagName === 'SELECT') {
                el.selectedIndex = 0;
            } else if (el.type === 'number' || el.type === 'text') {
                el.value = '';
            }
        });

        // Reset deduction estimate badge and re-enable the deduction input
        var badge = newRow.querySelector('.deduction-cell .deduction-badge');
        if (badge) badge.textContent = '-';

        var deductionInput = newRow.querySelector('.deduction-input');
        if (deductionInput) {
            deductionInput.value = '';
            deductionInput.readOnly = false;
        }

        // Show remove button
        var removeBtn = newRow.querySelector('.remove-row-btn');
        if (removeBtn) removeBtn.style.display = 'inline-block';
        
        // Reset selected employee display
        var selectedDisplay = newRow.querySelector('.selected-employee');
        if (selectedDisplay) {
            selectedDisplay.innerHTML = '<span class="text-muted" style="font-size: 12px;">Select an employee</span>';
        }

        document.getElementById('loanRows').appendChild(newRow);
        rowCount++;
    });

    // ============ REMOVE ROW ============
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row-btn')) {
            var rows = document.querySelectorAll('.loan-row');
            if (rows.length > 1) {
                e.target.closest('.loan-row').remove();
            } else {
                alert('You must have at least one row.');
            }
        }
    });

    // ============ EMPLOYEE SELECT - Update Display ============
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('employee-select')) {
            var selectedOption = e.target.options[e.target.selectedIndex];
            var code = selectedOption.getAttribute('data-code') || '';
            var name = selectedOption.getAttribute('data-name') || '';
            var display = e.target.closest('tr').querySelector('.selected-employee');
            
            if (code && name) {
                display.innerHTML = '<strong>' + code + '</strong> | ' + name;
            } else {
                display.innerHTML = '<span class="text-muted" style="font-size: 12px;">Select an employee</span>';
            }
        }
    });

    // ============ LOAN TYPE CHANGE - Cash Advance forces full-amount deduction ============
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('loan-type-select')) {
            var row = e.target.closest('tr');
            var deductionInput = row.querySelector('.deduction-input');
            var amountInput = row.querySelector('.amount-input');
            var selectedType = e.target.value;

            if (selectedType === 'Cash Advance') {
                // Cash Advance is always deducted in full next cutoff.
                deductionInput.value = amountInput.value || '';
                deductionInput.readOnly = true;
            } else {
                deductionInput.readOnly = false;
            }

            triggerDeductionCalculation(row);
        }
    });

    // ============ AMOUNT INPUT - Keep Cash Advance deduction synced, recalc estimate ============
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('amount-input')) {
            var row = e.target.closest('tr');
            var loanTypeSelect = row.querySelector('.loan-type-select');
            var deductionInput = row.querySelector('.deduction-input');

            if (loanTypeSelect && loanTypeSelect.value === 'Cash Advance') {
                deductionInput.value = e.target.value;
            }

            triggerDeductionCalculation(row);
        }
    });

    // ============ DEDUCTION INPUT CHANGE - Recalculate estimate ============
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('deduction-input')) {
            var row = e.target.closest('tr');
            triggerDeductionCalculation(row);
        }
    });

    // ============ FILTER BUTTONS ============
    var filterBtns = document.querySelectorAll('.btn-filter');
    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var filter = this.dataset.filter;
            
            filterBtns.forEach(function(b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            var rows = document.querySelectorAll('#loanListTable tbody tr');
            rows.forEach(function(row) {
                if (filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    var selectAllLoans = document.getElementById('selectAllLoans');
    if (selectAllLoans) {
        selectAllLoans.addEventListener('change', function () {
            document.querySelectorAll('.loan-select').forEach(function (checkbox) {
                checkbox.checked = selectAllLoans.checked;
            });
        });
    }

    // ============ APPROVE LOAN ============
    document.querySelectorAll('.approve-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            if (confirm('Approve this loan request?')) {
                fetch('/loan-requests/' + id + '/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.success || 'Loan approved!');
                    location.reload();
                })
                .catch(error => {
                    alert('Error approving loan');
                });
            }
        });
    });

    // ============ RELEASE LOAN ============
    document.querySelectorAll('.release-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            if (confirm('Release this loan?')) {
                fetch('/loan-requests/' + id + '/release', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.success || 'Loan released!');
                    location.reload();
                })
                .catch(error => {
                    alert('Error releasing loan');
                });
            }
        });
    });

    // ============ REJECT LOAN ============
    document.querySelectorAll('.reject-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.id;
            var reason = prompt('Enter reason for rejection:');
            if (reason !== null) {
                fetch('/loan-requests/' + id + '/reject', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ reason: reason })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.success || 'Loan rejected!');
                    location.reload();
                })
                .catch(error => {
                    alert('Error rejecting loan');
                });
            }
        });
    });

    // ============ SELECT2 INITIALIZATION ============
    if (typeof $.fn.select2 !== 'undefined') {
        $('.employee-select').select2({
            placeholder: 'Search employee by name or ID',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
@endsection

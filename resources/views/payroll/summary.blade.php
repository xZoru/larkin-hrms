@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Payroll Summary
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / Payroll Summary
        </div>
    </div>
@endsection

@section('content')
<style>
    /* ============================================
       HEADER STYLES
       ============================================ */
    .summary-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 12px;
        padding: 24px 28px;
        color: white;
        margin-bottom: 24px;
    }
    .summary-header .company-name {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: white;
    }
    .summary-header .fortnight-info {
        font-size: 14px;
        color: #a0aec0;
        margin-top: 4px;
    }
    .summary-header .fortnight-info .value {
        color: #e2e8f0;
        font-weight: 500;
    }
    .summary-header .fortnight-info .label {
        color: #94a3b8;
    }
    
    /* ============================================
       STATS ROW
       ============================================ */
    .stat-box {
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px 16px;
        text-align: center;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
        flex: 1;
        min-width: 120px;
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
    .stat-box .stat-value.green { color: #16a34a; }
    .stat-box .stat-value.red { color: #dc2626; }
    .stat-box .stat-value.blue { color: #2563eb; }
    .stat-box .stat-value.purple { color: #7c3aed; }
    .stat-box .stat-value.orange { color: #ea580c; }
    .stat-box .stat-value.indigo { color: #4f46e5; }
    
    /* ============================================
       TABLE STYLES
       ============================================ */
    .table-wrap {
        overflow: visible;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: white;
    }
    .table-payroll {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
        table-layout: fixed;
    }
    .table-payroll thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f1f5f9;
        color: #475569;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        padding: 10px 6px;
        border-bottom: 2px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        text-align: right;
        white-space: nowrap;
    }
    .table-payroll thead th:last-child {
        border-right: none;
    }
    .table-payroll thead th.text-left {
        text-align: left;
    }
    .table-payroll thead th.text-center {
        text-align: center;
    }
    .table-payroll tbody td {
        padding: 6px 6px;
        border-bottom: 1px solid #f1f5f9;
        border-right: 1px solid #f1f5f9;
        vertical-align: middle;
        background: white;
        transition: background 0.15s;
    }
    .table-payroll tbody td:last-child {
        border-right: none;
    }
    .table-payroll tbody tr:hover td {
        background: #fafbfc;
    }
    .table-payroll tbody tr:hover td.employee-cell {
        background: #fafbfc !important;
    }
    .table-payroll tbody tr:last-child td {
        border-bottom: none;
    }
    
    /* Update the employee-cell styles */
    .employee-cell {
        width: 200px;
        min-width: 200px;
        max-width: 200px;
        position: sticky;
        left: 0;
        background: white !important;
        border-right: 2px solid #e2e8f0 !important;
        box-shadow: 4px 0 12px rgba(0, 0, 0, 0.04);
        z-index: 5;
        padding: 6px 10px !important;
    }
    th.employee-cell {
        z-index: 15 !important;
        background: #f1f5f9 !important;
        box-shadow: 4px 0 12px rgba(0, 0, 0, 0.04);
        padding: 10px 10px !important;
        width: 200px;
        min-width: 200px;
        max-width: 200px;
    }
    .employee-cell .employee-number {
        font-weight: 600;
        color: #0f172a;
        font-size: 11px;
        line-height: 1.2;
    }
    .employee-cell .employee-name {
        font-weight: 400;
        color: #475569;
        font-size: 12px;
        line-height: 1.3;
        margin-top: 1px;
    }

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .employee-cell { width: 160px; min-width: 160px; max-width: 160px; }
        th.employee-cell { width: 160px; min-width: 160px; max-width: 160px; }
        .employee-cell .employee-number { font-size: 10px; }
        .employee-cell .employee-name { font-size: 11px; }
    }

    @media (max-width: 768px) {
        .employee-cell { width: 130px; min-width: 130px; max-width: 130px; padding: 4px 6px !important; }
        th.employee-cell { width: 130px; min-width: 130px; max-width: 130px; }
        .employee-cell .employee-number { font-size: 9px; }
        .employee-cell .employee-name { font-size: 10px; }
    }

    @media (max-width: 480px) {
        .employee-cell { width: 110px; min-width: 110px; max-width: 110px; }
        th.employee-cell { width: 110px; min-width: 110px; max-width: 110px; }
        .employee-cell .employee-number { font-size: 8px; }
        .employee-cell .employee-name { font-size: 9px; }
    }
    
    /* Number Columns */
    .num-cell {
        width: 90px;
        min-width: 90px;
        max-width: 90px;
        text-align: right;
        padding: 4px 6px !important;
    }
    .num-cell-sm {
        width: 75px;
        min-width: 75px;
        max-width: 75px;
        text-align: right;
        padding: 4px 6px !important;
    }
    .num-cell-xs {
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        text-align: right;
        padding: 4px 4px !important;
    }
    
    /* Editable Inputs */
    .editable-input {
        width: 100%;
        padding: 4px 6px;
        border: 1.5px solid #e2e8f0;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        text-align: right;
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-variant-numeric: tabular-nums;
        height: 30px;
    }

    .editable-input::-webkit-outer-spin-button,
    .editable-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .editable-input:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        z-index: 2;
        position: relative;
    }
    .editable-input:hover {
        border-color: #94a3b8;
    }
    .editable-input.edited {
        border-color: #22c55e;
        background: #f0fdf4;
    }
    .editable-input:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .editable-input::placeholder {
        color: #cbd5e1;
        font-weight: 400;
        font-size: 11px;
    }
    .editable-input.amount-positive {
        color: #16a34a;
    }
    .editable-input.amount-negative {
        color: #dc2626;
    }
    .editable-input.amount-gross {
        color: #1a1f36;
        font-weight: 700;
    }
    .editable-input.amount-net {
        color: #16a34a;
        font-weight: 700;
        font-size: 13px;
    }
    
    /* Footer */
    .table-payroll tfoot td {
        background: #f1f5f9;
        font-weight: 700;
        padding: 10px 6px;
        border-top: 2px solid #cbd5e1;
        font-size: 13px;
        border-right: 1px solid #e2e8f0;
    }
    .table-payroll tfoot td:last-child {
        border-right: none;
    }
    .table-payroll tfoot .total-label {
        text-transform: uppercase;
        font-size: 10px;
        color: #475569;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .table-payroll tfoot .total-value {
        font-weight: 700;
        color: #0f172a;
    }
    .table-payroll tfoot .total-value.green { color: #16a34a; }
    .table-payroll tfoot .total-value.red { color: #dc2626; }

    .table-payroll tfoot .total-value {
    text-align: right;
    font-weight: 700;
    color: #0f172a;
    font-size: 12px;
    background: #f1f5f9;
    padding: 10px 6px;
    }
    .table-payroll tfoot .total-value.gross-total {
        color: #1a1f36;
        font-size: 13px;
        border-top: 2px solid #4f46e5;
    }
    .table-payroll tfoot .total-value.net-total {
        color: #16a34a;
        font-size: 14px;
        border-top: 2px solid #16a34a;
    }
    .table-payroll tfoot .total-value.negative {
        color: #dc2626;
    }
    
    /* ============================================
       ACTION BAR
       ============================================ */
    .action-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 2px solid #f1f5f9;
    }
    .action-bar-left {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }
    .action-bar .fortnight-selector {
        padding: 8px 14px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        min-width: 200px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
    }
    .action-bar .fortnight-selector:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .btn-save-all {
        background: #22c55e;
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    .btn-save-all:hover {
        background: #16a34a;
    }
    .btn-save-all:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-view-payroll {
        background: #4f46e5;
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-view-payroll:hover {
        background: #4338ca;
        color: white;
    }
    .btn-reset {
        background: #e2e8f0;
        color: #475569;
        padding: 8px 20px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-reset:hover {
        background: #cbd5e1;
    }

    
    
    /* ============================================
       ALERTS / EMPTY STATE
       ============================================ */
    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        border-radius: 8px;
        padding: 14px 18px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }
    .empty-state .icon {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 16px;
        display: block;
    }
    .empty-state .title {
        font-size: 18px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .empty-state .subtitle {
        font-size: 14px;
        color: #94a3b8;
    }
    
    /* ============================================
       TOAST NOTIFICATION
       ============================================ */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .toast {
        padding: 14px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 280px;
    }
    .toast.success { background: #16a34a; }
    .toast.error { background: #dc2626; }
    .toast.info { background: #2563eb; }
    .toast .close {
        margin-left: auto;
        cursor: pointer;
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        opacity: 0.7;
    }
    .toast .close:hover { opacity: 1; }
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 1400px) {
        .num-cell { width: 75px; min-width: 75px; max-width: 75px; }
        .num-cell-sm { width: 65px; min-width: 65px; max-width: 65px; }
        .num-cell-xs { width: 50px; min-width: 50px; max-width: 50px; }
        .editable-input { font-size: 11px; height: 26px; padding: 3px 4px; }
    }
    
    @media (max-width: 1200px) {
        .table-payroll { font-size: 11px; }
        .employee-cell { width: 130px; min-width: 130px; max-width: 130px; }
        th.employee-cell { width: 130px; min-width: 130px; max-width: 130px; }
        .num-cell { width: 65px; min-width: 65px; max-width: 65px; }
        .num-cell-sm { width: 55px; min-width: 55px; max-width: 55px; }
        .num-cell-xs { width: 45px; min-width: 45px; max-width: 45px; }
        .editable-input { font-size: 10px; height: 24px; padding: 2px 3px; }
        .table-payroll thead th { font-size: 8px; padding: 8px 4px; }
    }
    
    @media (max-width: 768px) {
        .summary-header { padding: 16px 20px; }
        .summary-header .company-name { font-size: 18px; }
        .stat-box .stat-value { font-size: 16px; }
        .stat-box { padding: 8px 12px; min-width: 80px; }
        .action-bar { flex-direction: column; align-items: stretch; }
        .action-bar-left { justify-content: center; flex-wrap: wrap; }
        .action-bar .fortnight-selector { min-width: 100%; }
        .btn-save-all, .btn-view-payroll, .btn-reset { width: 100%; justify-content: center; }
        .table-wrap { overflow-x: auto; margin: 0 -16px; border-radius: 0; border-left: none; border-right: none; }
        .table-payroll { min-width: 1100px; font-size: 10px; }
        .employee-cell { width: 110px; min-width: 110px; max-width: 110px; padding: 4px 6px !important; }
        th.employee-cell { width: 110px; min-width: 110px; max-width: 110px; }
        .employee-cell .employee-name { font-size: 10px; }
        .num-cell { width: 55px; min-width: 55px; max-width: 55px; }
        .num-cell-sm { width: 48px; min-width: 48px; max-width: 48px; }
        .num-cell-xs { width: 40px; min-width: 40px; max-width: 40px; }
        .editable-input { font-size: 9px; height: 20px; padding: 2px 3px; }
        .table-payroll tbody td { padding: 4px 4px; }
        .table-payroll tfoot td { padding: 6px 4px; font-size: 10px; }
    }
    
    @media (max-width: 480px) {
        .table-payroll { min-width: 900px; font-size: 9px; }
        .employee-cell { width: 90px; min-width: 90px; max-width: 90px; }
        th.employee-cell { width: 90px; min-width: 90px; max-width: 90px; }
        .employee-cell .employee-name { font-size: 9px; }
        .num-cell { width: 48px; min-width: 48px; max-width: 48px; }
        .num-cell-sm { width: 42px; min-width: 42px; max-width: 42px; }
        .num-cell-xs { width: 35px; min-width: 35px; max-width: 35px; }
        .editable-input { font-size: 8px; height: 18px; padding: 1px 2px; border-width: 1px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Toast Container -->
        <div class="toast-container" id="toastContainer"></div>
        
        <!-- Summary Header -->
        <div class="summary-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="company-name">{{ auth()->user()->company->name ?? 'Company' }}</div>
                    <div class="fortnight-info">
                        <span class="label">Fortnight:</span> 
                        <span class="value">{{ $selectedFortnight ?? 'Not Selected' }}</span>
                        @if($period)
                            <span class="text-gray-500 mx-2">|</span>
                            <span class="value">{{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }}</span>
                            <span class="text-gray-500 mx-2">|</span>
                            <span class="value">{{ $payrollItems->count() ?? 0 }} employees</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($selectedFortnight && $payrollItems->count() > 0)
            @if(session('success'))
                <div class="alert-success">
                    <i class="fas fa-check-circle text-green-600"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Action Bar -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-5">
                <div class="action-bar">
                    <div class="action-bar-left">
                        <select id="fortnight_selector" class="fortnight-selector">
                            @foreach($fortnights as $fn)
                                @php
                                    $period = $fortnightPeriods[$fn] ?? null;
                                @endphp
                                <option value="{{ $fn }}" {{ $fn == $selectedFortnight ? 'selected' : '' }}>
                                    {{ $fn }}
                                    @if($period)
                                        ({{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="button" onclick="resetChanges()" class="btn-reset">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="button" onclick="saveAll()" class="btn-save-all" id="saveAllBtn">
                            <i class="fas fa-save"></i> Save All Changes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payroll Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="table-wrap">
                    <form id="payrollForm" method="POST" action="{{ route('payroll.summary.bulk-update') }}">
                        @csrf
                        <input type="hidden" name="fortnight" value="{{ $selectedFortnight }}">
                        
                        <table class="table-payroll">
                            <thead>
                                <tr>
                                    <th class="employee-cell text-left" style="width:200px; min-width:200px; max-width:200px;">EMPLOYEE</th>
                                    <th class="num-cell-xs text-right">FN Rate</th>
                                    <th class="num-cell-sm text-right">BasicPay</th>
                                    <th class="num-cell-sm text-right">Regular</th>
                                    <th class="num-cell-sm text-right">Over Time</th>
                                    <th class="num-cell-sm text-right">Sunday OT</th>
                                    <th class="num-cell-sm text-right">Holiday OT</th>
                                    <th class="num-cell-sm text-right">PLP/ALP/FP</th>
                                    <th class="num-cell-sm text-right">Other</th>
                                    <th class="num-cell-sm text-right">Gross Total</th>
                                    <th class="num-cell-sm text-right">FN Tax</th>
                                    <th class="num-cell-sm text-right">NPF (%)</th>
                                    <th class="num-cell-sm text-right">NCSL</th>
                                    <th class="num-cell-sm text-right">Cash Advance</th>
                                    <th class="num-cell-sm text-right">Others</th>
                                    <th class="num-cell-sm text-right">Net Pay</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payrollItems as $item)
                                <tr data-id="{{ $item->id }}" data-employee-type="{{ $item->employee->employee_type ?? 'National' }}">
                                    <td class="employee-cell" style="width:200px; min-width:200px; max-width:200px;">
                                        <div class="employee-number" style="font-weight:600; color:#0f172a; font-size:11px;">
                                            {{ $item->employee->employee_number ?? '' }}
                                        </div>
                                        <div class="employee-name" style="font-size:12px; margin-top:1px;">
                                            {{ $item->employee->full_name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <!-- FN RATE column - uses basic_pay (Original Basic Pay) -->
                                    <td class="num-cell-xs">
                                        <input type="number" step="0.01" 
                                            value="{{ number_format($item->basic_pay, 2, '.', '') }}"
                                            class="editable-input"
                                            disabled>
                                    </td>

                                    <!-- BASICPAY column - uses basic_pay (Original Basic Pay) -->
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][basic_pay]"
                                            value="{{ number_format($item->basic_pay, 2, '.', '') }}"
                                            class="editable-input amount-positive"
                                            data-original="{{ $item->basic_pay }}"
                                            disabled>
                                    </td>

                                    <!-- REGULAR column - uses regular_pay (grossed up for expats) -->
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][regular_pay]"
                                            value="{{ number_format($item->regular_pay, 2, '.', '') }}"
                                            class="editable-input amount-positive"
                                            data-original="{{ $item->regular_pay }}">
                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][overtime_pay]"
                                            value="{{ number_format($item->overtime_pay, 2, '.', '') }}"
                                            class="editable-input amount-positive"
                                            data-original="{{ $item->overtime_pay }}">
                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][sunday_pay]"
                                            value="{{ number_format($item->sunday_pay, 2, '.', '') }}"
                                            class="editable-input amount-positive"
                                            data-original="{{ $item->sunday_pay }}">

                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][holiday_pay]"
                                            value="{{ number_format($item->holiday_pay, 2, '.', '') }}"
                                            class="editable-input amount-positive"
                                            data-original="{{ $item->holiday_pay }}">

                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][leave_pay]"
                                            value="0.00"
                                            class="editable-input amount-positive"
                                            data-original="0">
                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][other_earnings]"
                                            value="{{ number_format($item->allowance, 2, '.', '') }}"
                                            class="editable-input amount-positive"
                                            data-original="{{ $item->allowance }}">

                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][gross_wage]"
                                            value="{{ number_format($item->gross_wage, 2, '.', '') }}"
                                            class="editable-input amount-gross"
                                            data-original="{{ $item->gross_wage }}"
                                            id="gross_{{ $item->id }}">
                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][tax]"
                                            value="{{ number_format($item->tax, 2, '.', '') }}"
                                            class="editable-input amount-negative"
                                            data-original="{{ $item->tax }}">
                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][nasfund_ee]"
                                            value="{{ number_format($item->nasfund_ee, 2, '.', '') }}"
                                            class="editable-input"
                                            data-original="{{ $item->nasfund_ee }}">
                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][ncsl]"
                                            value="0.00"
                                            class="editable-input amount-negative"
                                            data-original="0">
                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][loan_deduction]"
                                            value="{{ number_format($item->loan_deduction, 2, '.', '') }}"
                                            class="editable-input amount-negative"
                                            data-original="{{ $item->loan_deduction }}">
                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][other_deductions]"
                                            value="{{ number_format($item->other_deductions, 2, '.', '') }}"
                                            class="editable-input amount-negative"
                                            data-original="{{ $item->other_deductions }}">
                                    </td>
                                    <td class="num-cell-sm">
                                        <input type="number" step="0.01" min="0" 
                                            name="items[{{ $item->id }}][net_pay]"
                                            value="{{ number_format($item->net_pay, 2, '.', '') }}"
                                            class="editable-input amount-net"
                                            data-original="{{ $item->net_pay }}"
                                            id="net_{{ $item->id }}"
                                            readonly>
                                    </td>
                                </tr>
                                
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="total-label" style="text-align:left; padding-left:10px; font-weight:700;">TOTALS</td>
                                    <td class="total-value"></td>
                                    <td class="total-value" id="totalBasic"></td>
                                    <td class="total-value" id="totalRegular"></td>
                                    <td class="total-value" id="totalOvertime"></td>
                                    <td class="total-value" id="totalSunday"></td>
                                    <td class="total-value" id="totalHoliday"></td>
                                    <td class="total-value" id="totalLeave">K 0.00</td>
                                    <td class="total-value" id="totalOtherEarnings">K {{ number_format($totalAllowance ?? 0, 2) }}</td>
                                    <td class="total-value gross-total" id="totalGross">K {{ number_format($totalGross, 2) }}</td>
                                    <td class="total-value negative" id="totalTax">K {{ number_format($totalTax, 2) }}</td>
                                    <td class="total-value" id="totalNasfund">K {{ number_format($totalNasfund, 2) }}</td>
                                    <td class="total-value" id="totalNcsl">K 0.00</td>
                                    <td class="total-value negative" id="totalLoan">K {{ number_format($totalLoanDeductions, 2) }}</td>
                                    <td class="total-value negative" id="totalOtherDeductions">K {{ number_format($totalOtherDeductions ?? 0, 2) }}</td>
                                    <td class="total-value net-total" id="totalNet">K {{ number_format($totalNet, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </form>
                </div>
            </div>

        @elseif($selectedFortnight)
            <!-- No Data Found -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <span class="icon" style="font-size:48px; display:block; margin-bottom:16px;">📋</span>
                <h3 class="text-lg font-medium text-gray-700 mb-2">No Payroll Data</h3>
                <p class="text-gray-500 mb-4">No payroll items found for fortnight {{ $selectedFortnight }}.</p>
                <a href="{{ route('payroll.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium inline-block">
                    <i class="fas fa-plus mr-1"></i> Create Payroll
                </a>
            </div>

        @else
            <!-- No Fortnight Selected -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <span class="icon" style="font-size:48px; display:block; margin-bottom:16px;">📅</span>
                <h3 class="text-lg font-medium text-gray-700 mb-2">No Fortnights Found</h3>
                <p class="text-gray-500 mb-4">Please create a payroll first to see the summary.</p>
                <a href="{{ route('payroll.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium inline-block">
                    <i class="fas fa-plus mr-1"></i> Create Payroll
                </a>
            </div>
        @endif

    </div>
</div>

<script>
    // Tax tables from database - passed from Laravel
    const taxTables = @json($taxTables ?? []);
    
    // Tax calculation function using database tables
    function calculateTax(grossPay, employeeType) {
        if (grossPay <= 0) return 0;
        
        // Get the tax tables for this employee type, fallback to National
        const tables = taxTables[employeeType] || taxTables['National'] || [];
        
        if (tables.length === 0) return 0;
        
        // Find the applicable tax bracket
        for (const bracket of tables) {
            if (grossPay >= bracket.min) {
                if (bracket.max === null || grossPay <= bracket.max) {
                    // Tax = (Income × Rate%) - Offset (Fixed Tax)
                    const tax = (grossPay * bracket.rate / 100) - bracket.fixed;
                    return Math.max(0, Math.round(tax * 100) / 100);
                }
            }
        }
        
        // If no bracket found (income above all brackets), use the highest bracket
        const lastBracket = tables[tables.length - 1];
        const tax = (grossPay * lastBracket.rate / 100) - lastBracket.fixed;
        return Math.max(0, Math.round(tax * 100) / 100);
    }

    // Track edited fields with their original values
    let editedFields = new Map();
    let savedFields = new Map();

    function markEdited(input) {
        if (savedFields.has(input.name)) {
            const savedValue = savedFields.get(input.name);
            if (Math.abs(parseFloat(input.value) - savedValue) < 0.005) {
                return;
            }
            savedFields.delete(input.name);
        }

        if (!input.dataset.original) {
            const name = input.name;
            if (!editedFields.has(name)) {
                editedFields.set(name, {
                    original: parseFloat(input.value) || 0,
                    element: input
                });
            }
            input.dataset.original = input.value;
        }
        
        const original = parseFloat(input.dataset.original) || 0;
        const current = parseFloat(input.value) || 0;
        
        if (Math.abs(current - original) > 0.005) {
            input.classList.add('edited');
            if (editedFields.has(input.name)) {
                const entry = editedFields.get(input.name);
                entry.current = current;
            } else {
                editedFields.set(input.name, {
                    original: original,
                    current: current,
                    element: input
                });
            }
        } else {
            input.classList.remove('edited');
            editedFields.delete(input.name);
        }
        
        updateSaveButton();
    }

    function updateSaveButton() {
        const saveBtn = document.getElementById('saveAllBtn');
        const count = editedFields.size;
        if (count > 0) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save All Changes (' + count + ')';
            saveBtn.style.opacity = '1';
            saveBtn.style.background = '#22c55e';
        } else {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-check-circle"></i> All Saved';
            saveBtn.style.opacity = '0.6';
            saveBtn.style.background = '#22c55e';
        }
    }

    function recalculateRow(input) {
        const row = input.closest('tr');
        if (!row) return;
        
        // Get employee type from the row
        const employeeType = row.dataset.employeeType || 'National';
        
        // Get the name of the field that was edited
        const fieldName = input.name || '';
        
        // Check if this is a deduction field (should only affect Net Pay)
        const isDeduction = fieldName.includes('loan_deduction') || 
                            fieldName.includes('other_deductions') || 
                            fieldName.includes('ncsl') ||
                            fieldName.includes('nasfund_ee');
        
        // Get all earnings
        const basicPay = parseFloat(row.querySelector('[name$="[regular_pay]"]')?.value) || 0;
        const overtimePay = parseFloat(row.querySelector('[name$="[overtime_pay]"]')?.value) || 0;
        const sundayPay = parseFloat(row.querySelector('[name$="[sunday_pay]"]')?.value) || 0;
        const holidayPay = parseFloat(row.querySelector('[name$="[holiday_pay]"]')?.value) || 0;
        const leavePay = parseFloat(row.querySelector('[name$="[leave_pay]"]')?.value) || 0;
        const otherEarnings = parseFloat(row.querySelector('[name$="[other_earnings]"]')?.value) || 0;
        
        // Calculate Tax on BASIC PAY only
        let tax;
        if (employeeType === 'Expatriate') {
            // ✅ Expatriate: Tax on BASIC PAY only
            tax = calculateTax(basicPay, employeeType);
        } else {
            // ✅ National: Tax on GROSS PAY (all earnings combined)
            const gross = basicPay + overtimePay + sundayPay + holidayPay + leavePay + otherEarnings;
            tax = calculateTax(gross, employeeType);
        }
        
        // Determine REGULAR PAY based on employee type
        let regularPay;
        if (employeeType === 'Expatriate') {
            regularPay = basicPay + tax;
        } else {
            regularPay = basicPay;
        }
        
        // Calculate Gross Total
        const gross = regularPay + overtimePay + sundayPay + holidayPay + leavePay + otherEarnings;
        
        // ✅ ONLY update REGULAR, Gross, and Tax if NOT a deduction field
        if (!isDeduction) {
            // Update REGULAR field
            const regularInput = row.querySelector('[name$="[regular_pay]"]');
            if (regularInput) {
                regularInput.value = regularPay.toFixed(2);
                markEdited(regularInput);
            }
            
            // Update Gross field
            const grossInput = row.querySelector('[name$="[gross_wage]"]');
            if (grossInput) {
                grossInput.value = gross.toFixed(2);
                markEdited(grossInput);
            }
            
            // Update Tax field
            const taxField = row.querySelector('[name$="[tax]"]');
            if (taxField) {
                taxField.value = tax.toFixed(2);
                markEdited(taxField);
            }
            
            // Calculate NASFUND (6%) - ONLY if employee has NASFUND
            const nasfundField = row.querySelector('[name$="[nasfund_ee]"]');
            const originalNasfund = parseFloat(nasfundField?.dataset?.original) || 0;
            const nasfund = originalNasfund > 0 ? gross * 0.06 : 0;
            
            if (nasfundField) {
                nasfundField.value = nasfund.toFixed(2);
                markEdited(nasfundField);
            }
        }
        
        // ✅ ALWAYS recalculate Net Pay (regardless of what was edited)
        // Get deductions (these may have been changed)
        const ncsl = parseFloat(row.querySelector('[name$="[ncsl]"]')?.value) || 0;
        const loan = parseFloat(row.querySelector('[name$="[loan_deduction]"]')?.value) || 0;
        const otherDeductions = parseFloat(row.querySelector('[name$="[other_deductions]"]')?.value) || 0;
        
        // Get NASFUND (may have been changed if it was edited directly)
        const nasfundField = row.querySelector('[name$="[nasfund_ee]"]');
        const nasfund = parseFloat(nasfundField?.value) || 0;
        
        // Calculate Net Pay
        const net = gross - tax - nasfund - ncsl - loan - otherDeductions;
        
        // Update Net Pay field
        const netField = row.querySelector('[name$="[net_pay]"]');
        if (netField) {
            netField.value = net.toFixed(2);
        }
        
        updateTotals();
    }

    function updateTotals() {
        let totalBasic = 0, totalRegular = 0, totalOvertime = 0, totalSunday = 0;
        let totalHoliday = 0, totalLeave = 0, totalOtherEarnings = 0, totalGross = 0;
        let totalTax = 0, totalNasfund = 0, totalNcsl = 0, totalLoan = 0;
        let totalOtherDeductions = 0, totalNet = 0;
        
        document.querySelectorAll('#payrollForm tbody tr').forEach(row => {
            const basic = parseFloat(row.querySelector('[name*="regular_pay"]')?.value) || 0;
            const regular = parseFloat(row.querySelector('[name*="regular_pay"]')?.value) || 0;
            const overtime = parseFloat(row.querySelector('[name*="overtime_pay"]')?.value) || 0;
            const sunday = parseFloat(row.querySelector('[name*="sunday_pay"]')?.value) || 0;
            const holiday = parseFloat(row.querySelector('[name*="holiday_pay"]')?.value) || 0;
            const leave = parseFloat(row.querySelector('[name*="leave_pay"]')?.value) || 0;
            const otherEarnings = parseFloat(row.querySelector('[name*="other_earnings"]')?.value) || 0;
            const gross = parseFloat(row.querySelector('[name*="gross_wage"]')?.value) || 0;
            const tax = parseFloat(row.querySelector('[name*="tax"]')?.value) || 0;
            const nasfund = parseFloat(row.querySelector('[name*="nasfund_ee"]')?.value) || 0;
            const ncsl = parseFloat(row.querySelector('[name*="ncsl"]')?.value) || 0;
            const loan = parseFloat(row.querySelector('[name*="loan_deduction"]')?.value) || 0;
            const otherDed = parseFloat(row.querySelector('[name*="other_deductions"]')?.value) || 0;
            const net = parseFloat(row.querySelector('[name*="net_pay"]')?.value) || 0;
            
            totalBasic += basic;
            totalRegular += regular;
            totalOvertime += overtime;
            totalSunday += sunday;
            totalHoliday += holiday;
            totalLeave += leave;
            totalOtherEarnings += otherEarnings;
            totalGross += gross;
            totalTax += tax;
            totalNasfund += nasfund;
            totalNcsl += ncsl;
            totalLoan += loan;
            totalOtherDeductions += otherDed;
            totalNet += net;
        });
        
        document.getElementById('totalBasic').textContent = 'K ' + totalBasic.toFixed(2);
        document.getElementById('totalRegular').textContent = 'K ' + totalRegular.toFixed(2);
        document.getElementById('totalOvertime').textContent = 'K ' + totalOvertime.toFixed(2);
        document.getElementById('totalSunday').textContent = 'K ' + totalSunday.toFixed(2);
        document.getElementById('totalHoliday').textContent = 'K ' + totalHoliday.toFixed(2);
        document.getElementById('totalLeave').textContent = 'K ' + totalLeave.toFixed(2);
        document.getElementById('totalOtherEarnings').textContent = 'K ' + totalOtherEarnings.toFixed(2);
        document.getElementById('totalGross').textContent = 'K ' + totalGross.toFixed(2);
        document.getElementById('totalTax').textContent = 'K ' + totalTax.toFixed(2);
        document.getElementById('totalNasfund').textContent = 'K ' + totalNasfund.toFixed(2);
        document.getElementById('totalNcsl').textContent = 'K ' + totalNcsl.toFixed(2);
        document.getElementById('totalLoan').textContent = 'K ' + totalLoan.toFixed(2);
        document.getElementById('totalOtherDeductions').textContent = 'K ' + totalOtherDeductions.toFixed(2);
        document.getElementById('totalNet').textContent = 'K ' + totalNet.toFixed(2);
    }

    function resetChanges() {
        const count = editedFields.size;
        if (count === 0) {
            showToast('No unsaved changes to reset', 'info');
            return;
        }
        
        if (!confirm('Reset ' + count + ' unsaved change(s)? This will revert to the last saved state.')) return;
        
        editedFields.forEach((entry, name) => {
            const input = entry.element;
            if (input) {
                if (savedFields.has(name)) {
                    const savedValue = savedFields.get(name);
                    input.value = savedValue.toFixed(2);
                    input.dataset.original = savedValue.toString();
                } else {
                    input.value = entry.original.toFixed(2);
                    input.dataset.original = entry.original.toString();
                }
                input.classList.remove('edited');
            }
        });
        
        editedFields.clear();
        updateTotals();
        updateSaveButton();
        showToast('All unsaved changes have been reset', 'info');
    }

    function saveAll() {
        const count = editedFields.size;
        if (count === 0) {
            showToast('No changes to save', 'info');
            return;
        }
        
        const form = document.getElementById('payrollForm');
        const formData = new FormData(form);
        const editedNames = Array.from(editedFields.keys());
        formData.append('_edited_fields', JSON.stringify(editedNames));
        
        const saveBtn = document.getElementById('saveAllBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        fetch('{{ route("payroll.summary.bulk-update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                editedFields.forEach((entry, name) => {
                    const input = entry.element;
                    if (input) {
                        const currentValue = parseFloat(input.value);
                        savedFields.set(name, currentValue);
                        input.dataset.original = currentValue.toString();
                        input.classList.remove('edited');
                    }
                });
                editedFields.clear();
                updateSaveButton();
                showToast(data.message || 'All changes saved successfully!', 'success');
            } else {
                showToast(data.message || 'Error saving changes', 'error');
                saveBtn.disabled = false;
                updateSaveButton();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error saving changes. Please try again.', 'error');
            saveBtn.disabled = false;
            updateSaveButton();
        });
    }

    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        
        const icons = {
            success: '✅',
            error: '❌',
            info: 'ℹ️'
        };
        
        toast.innerHTML = `
            <span>${icons[type] || 'ℹ️'}</span>
            <span>${message}</span>
            <button class="close" onclick="this.parentElement.remove()">×</button>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.editable-input').forEach(input => {
            if (input.value && input.name) {
                input.dataset.original = input.value;
                savedFields.set(input.name, parseFloat(input.value));
            }
        });
        
        updateSaveButton();
        
        const selector = document.getElementById('fortnight_selector');
        if (selector) {
            selector.addEventListener('change', function() {
                if (editedFields.size > 0) {
                    if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                        return;
                    }
                }
                const url = new URL(window.location.href);
                url.searchParams.set('fortnight', this.value);
                window.location.href = url.toString();
            });
        }
        
        document.querySelectorAll('.editable-input:not([readonly])').forEach(input => {
            input.addEventListener('input', function() {
                recalculateRow(this);
                markEdited(this);
            });
            
            input.addEventListener('blur', function() {
                const val = parseFloat(this.value);
                if (isNaN(val) || val < 0) {
                    this.value = '0.00';
                    markEdited(this);
                }
            });
        });
    });
</script>
@endsection
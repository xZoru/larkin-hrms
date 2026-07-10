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
    .summary-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        padding: 20px;
        margin-bottom: 20px;
    }
    .summary-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .summary-header .company-name {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: white;
    }
    .summary-header .fortnight-info {
        font-size: 14px;
        color: #a0aec0;
    }
    .summary-header .fortnight-info .label {
        color: #94a3b8;
    }
    .summary-header .fortnight-info .value {
        color: #e2e8f0;
        font-weight: 500;
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
    .stat-box .stat-value.green { color: #16a34a; }
    .stat-box .stat-value.red { color: #dc2626; }
    .stat-box .stat-value.blue { color: #2563eb; }
    .stat-box .stat-value.purple { color: #7c3aed; }
    .stat-box .stat-value.orange { color: #ea580c; }
    
    .table-payroll {
        font-size: 13px;
    }
    .table-payroll thead th {
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
    .table-payroll tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-payroll tbody tr:hover {
        background: #f8fafc;
    }
    .table-payroll tbody tr:last-child td {
        border-bottom: none;
    }
    .table-payroll .employee-name {
        font-weight: 600;
        color: #0f172a;
    }
    .table-payroll .employee-number {
        font-size: 11px;
        color: #94a3b8;
    }
    .table-payroll .amount {
        font-weight: 500;
        font-variant-numeric: tabular-nums;
    }
    .table-payroll .amount.positive { color: #16a34a; }
    .table-payroll .amount.negative { color: #dc2626; }
    .table-payroll .amount.gross { color: #1a1f36; font-weight: 600; }
    .table-payroll .amount.net { color: #16a34a; font-weight: 700; font-size: 14px; }
    
    .table-payroll tfoot td {
        background: #f1f5f9;
        font-weight: 700;
        padding: 12px;
        border-top: 2px solid #cbd5e1;
        font-size: 13px;
    }
    .table-payroll tfoot .total-label {
        text-transform: uppercase;
        font-size: 11px;
        color: #475569;
        letter-spacing: 0.5px;
    }
    .badge-method {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-method.bank {
        background: #dbeafe;
        color: #1e40af;
    }
    .badge-method.cash {
        background: #fef3c7;
        color: #92400e;
    }
    .fortnight-selector {
        background: white !important;
        color: #1a1f36 !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 14px;
        min-width: 200px;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
    }
    .fortnight-selector:focus {
        outline: none;
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .fortnight-selector option {
        color: #1a1f36 !important;
        background: white !important;
        padding: 8px 12px;
    }
    .fortnight-selector option:checked {
        background: #6366f1 !important;
        color: white !important;
    }
    .fortnight-selector option:hover {
        background: #f1f5f9 !important;
    }
    .btn-full-payroll {
        background: rgba(255,255,255,0.15);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .btn-full-payroll:hover {
        background: rgba(255,255,255,0.25);
        color: white;
    }
    @media (max-width: 768px) {
        .stat-box .stat-value { font-size: 16px; }
        .table-payroll { font-size: 11px; }
        .table-payroll thead th, .table-payroll tbody td { padding: 6px 8px; }
        .summary-header { padding: 16px; }
        .summary-header .company-name { font-size: 16px; }
        .fortnight-selector { font-size: 12px; padding: 6px 10px; min-width: 100px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Summary Header -->
        <div class="summary-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="company-name">{{ auth()->user()->company->name ?? 'Company' }}</div>
                    <div class="fortnight-info mt-1">
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
                <div class="flex items-center gap-3 mt-2 sm:mt-0">
                    <select id="fortnight_selector" class="fortnight-selector" style="color:#1a1f36 !important; background:white !important;">
                        @foreach($fortnights as $fn)
                            @php
                                $period = $fortnightPeriods[$fn] ?? null;
                            @endphp
                            <option value="{{ $fn }}" {{ $fn == $selectedFortnight ? 'selected' : '' }} style="color:#1a1f36 !important; background:white !important;">
                                {{ $fn }}
                                @if($period)
                                    ({{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @if($payroll)
                        <a href="{{ route('payroll.show', $payroll) }}" class="btn-full-payroll">
                            View Full Payroll →
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if($selectedFortnight && $payrollItems->count() > 0)
        

        <!-- Employee Breakdown Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-payroll w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Employee</th>
                            <th class="text-right">Rate</th>
                            <th class="text-right">Reg</th>
                            <th class="text-right">Basic</th>
                            <th class="text-right">OT</th>
                            <th class="text-right">OT Pay</th>
                            <th class="text-right">Sun</th>
                            <th class="text-right">Sun Pay</th>
                            <th class="text-right">Hol</th>
                            <th class="text-right">Hol Pay</th>
                            <th class="text-right">Gross</th>
                            <th class="text-right">Tax</th>
                            <th class="text-right">NASFUND</th>
                            <th class="text-right">Loan</th>
                            <th class="text-right">Net</th>
                            <th class="text-center">Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrollItems as $item)
                        <tr>
                            <td>
                                <div class="employee-name">{{ $item->employee->full_name ?? 'N/A' }}</div>
                                <div class="employee-number">{{ $item->employee->employee_number ?? '' }}</div>
                            </td>
                            <td class="text-right">{{ number_format($item->hourly_rate, 2) }}</td>
                            <td class="text-right">{{ number_format($item->regular_hours, 1) }}</td>
                            <td class="text-right amount">K {{ number_format($item->regular_pay, 2) }}</td>
                            <td class="text-right">{{ number_format($item->overtime_hours ?? 0, 1) }}</td>
                            <td class="text-right amount">K {{ number_format($item->overtime_pay, 2) }}</td>
                            <td class="text-right">{{ number_format($item->sunday_hours ?? 0, 1) }}</td>
                            <td class="text-right amount">K {{ number_format($item->sunday_pay, 2) }}</td>
                            <td class="text-right">{{ number_format($item->holiday_hours ?? 0, 1) }}</td>
                            <td class="text-right amount">K {{ number_format($item->holiday_pay, 2) }}</td>
                            <td class="text-right amount gross">K {{ number_format($item->gross_wage, 2) }}</td>
                            <td class="text-right amount negative">K {{ number_format($item->tax, 2) }}</td>
                            <td class="text-right amount">K {{ number_format($item->nasfund_ee, 2) }}</td>
                            <td class="text-right amount negative">K {{ number_format($item->loan_deduction, 2) }}</td>
                            <td class="text-right amount net">K {{ number_format($item->net_pay, 2) }}</td>
                            <td class="text-center">
                                <span class="badge-method {{ $item->payment_method == 'Bank Transfer' ? 'bank' : 'cash' }}">
                                    {{ $item->payment_method }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="total-label" colspan="4">TOTALS</td>
                            <td class="text-right">{{ number_format($totalOvertimeHours ?? 0, 1) }}</td>
                            <td class="text-right amount">K {{ number_format($totalOvertimePay ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($totalSundayHours ?? 0, 1) }}</td>
                            <td class="text-right amount">K {{ number_format($totalSundayPay ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format($totalHolidayHours ?? 0, 1) }}</td>
                            <td class="text-right amount">K {{ number_format($totalHolidayPay ?? 0, 2) }}</td>
                            <td class="text-right amount gross">K {{ number_format($totalGross, 2) }}</td>
                            <td class="text-right amount negative">K {{ number_format($totalTax, 2) }}</td>
                            <td class="text-right amount">K {{ number_format($totalNasfund, 2) }}</td>
                            <td class="text-right amount negative">K {{ number_format($totalLoanDeductions ?? 0, 2) }}</td>
                            <td class="text-right amount net">K {{ number_format($totalNet, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @elseif($selectedFortnight)
        <!-- No Data Found -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-700 mb-2">No Payroll Data</h3>
            <p class="text-gray-500 mb-4">No payroll items found for fortnight {{ $selectedFortnight }}.</p>
            <a href="{{ route('payroll.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium inline-block">
                + Create Payroll
            </a>
        </div>

        @else
        <!-- No Fortnight Selected -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-700 mb-2">No Fortnights Found</h3>
            <p class="text-gray-500 mb-4">Please create a payroll first to see the summary.</p>
            <a href="{{ route('payroll.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium inline-block">
                + Create Payroll
            </a>
        </div>
        @endif

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selector = document.getElementById('fortnight_selector');
        if (selector) {
            selector.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('fortnight', this.value);
                window.location.href = url.toString();
            });
        }
    });
</script>
@endsection
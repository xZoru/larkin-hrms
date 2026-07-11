@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Summary of Earnings
        </h2>
        <div class="text-sm text-gray-500">
            Reports / Summary of Earnings
        </div>
    </div>
@endsection

@section('content')
<style>
    .report-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .report-header .company-name {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .report-header .report-info {
        font-size: 14px;
        color: #a0aec0;
    }
    .report-header .report-info .value {
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
    .stat-box .stat-value.blue { color: #2563eb; }
    .stat-box .stat-value.green { color: #16a34a; }
    .stat-box .stat-value.purple { color: #7c3aed; }
    .stat-box .stat-value.orange { color: #ea580c; }
    .stat-box .stat-value.red { color: #dc2626; }
    
    .table-report {
        font-size: 13px;
        width: 100%;
    }
    .table-report thead th {
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
    .table-report tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-report tbody tr:hover {
        background: #f8fafc;
    }
    .table-report .employee-number {
        font-weight: 600;
        color: #2563eb;
        font-size: 13px;
    }
    .filter-select {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
        background: white;
        width: 100%;
        transition: border-color 0.2s;
    }
    .filter-select:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .btn-filter {
        background: #f1f5f9;
        color: #475569;
        padding: 8px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
    }
    .btn-filter:hover {
        background: #e2e8f0;
    }
    .btn-export {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-export.pdf {
        background: #dcfce7;
        color: #166534;
    }
    .btn-export.pdf:hover {
        background: #bbf7d0;
    }
    .btn-export.excel {
        background: #dbeafe;
        color: #1e40af;
    }
    .btn-export.excel:hover {
        background: #bfdbfe;
    }
    .btn-export.csv {
        background: #fef3c7;
        color: #92400e;
    }
    .btn-export.csv:hover {
        background: #fde68a;
    }
    .total-row td {
        background: #f1f5f9 !important;
        font-weight: 700;
        border-top: 2px solid #e2e8f0;
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    .empty-state .icon {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 12px;
    }
    .empty-state h3 {
        font-size: 18px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }
    .empty-state p {
        color: #6b7280;
        font-size: 14px;
    }
    .fortnight-section {
        margin-top: 40px;
        border-top: 2px solid #e5e7eb;
        padding-top: 24px;
    }
    .fortnight-section h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1a1f36;
        margin-bottom: 16px;
    }
    @media (max-width: 768px) {
        .table-report { font-size: 11px; }
        .table-report thead th, .table-report tbody td { padding: 6px 8px; }
        .stat-box .stat-value { font-size: 16px; }
        .report-header { padding: 16px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Report Header -->
        <div class="report-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="company-name">{{ $company->name ?? 'Company' }}</div>
                    <div class="report-info mt-1">
                        <span class="value">{{ count($reportData) }} employees</span>
                        @if($selectedYear)
                            <span class="text-gray-500 mx-2">|</span>
                            <span class="value">{{ $selectedYear }}</span>
                        @endif
                        <span class="text-gray-500 mx-2">|</span>
                        <span class="value">{{ count($fortnightData) }} fortnights</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
            <form action="{{ route('reports.earnings.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Year</label>
                    <select name="year" class="filter-select">
                        <option value="">Select Year</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" @selected($selectedYear == $year)>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-filter">Generate Report</button>
                </div>
            </form>
        </div>

        @if($selectedYear && count($reportData) > 0)
            <!-- Summary Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-box">
                    <div class="stat-value purple">{{ $summary->total_employees }}</div>
                    <div class="stat-label">Total Employees</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value blue">K {{ number_format($summary->total_gross, 2) }}</div>
                    <div class="stat-label">Total Gross Wage</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value orange">K {{ number_format($summary->total_tax, 2) }}</div>
                    <div class="stat-label">Total Tax</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value green">K {{ number_format($summary->total_net, 2) }}</div>
                    <div class="stat-label">Total Net Pay</div>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex flex-wrap gap-3 mb-6">
                <form action="{{ route('reports.earnings.export') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="hidden" name="format" value="pdf">
                    <button type="submit" class="btn-export pdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </form>
                <form action="{{ route('reports.earnings.export') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="hidden" name="format" value="excel">
                    <button type="submit" class="btn-export excel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </form>
                <form action="{{ route('reports.earnings.export') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn-export csv">
                        <i class="fas fa-file-csv"></i> CSV
                    </button>
                </form>
            </div>

            <!-- Employee Summary Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-report w-full">
                        <thead>
                            <tr>
                                <th class="text-left">#</th>
                                <th class="text-left">Employee #</th>
                                <th class="text-left">Employee Name</th>
                                <th class="text-right">Gross Wage</th>
                                <th class="text-right">Tax</th>
                                <th class="text-right">Net Pay</th>
                                <th class="text-right">NASFUND EE</th>
                                <th class="text-center">Payrolls</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $index => $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="employee-number">{{ $item->employee_number }}</div>
                                </td>
                                <td>{{ $item->full_name }}</td>
                                <td class="text-right">K {{ number_format($item->total_gross, 2) }}</td>
                                <td class="text-right text-orange-600">K {{ number_format($item->total_tax, 2) }}</td>
                                <td class="text-right text-green-600">K {{ number_format($item->total_net, 2) }}</td>
                                <td class="text-right">K {{ number_format($item->total_nasfund_ee, 2) }}</td>
                                <td class="text-center">
                                    <span class="inline-block bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs font-semibold">
                                        {{ $item->payroll_count }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="3" class="text-right">TOTAL</td>
                                <td class="text-right">K {{ number_format($summary->total_gross, 2) }}</td>
                                <td class="text-right">K {{ number_format($summary->total_tax, 2) }}</td>
                                <td class="text-right">K {{ number_format($summary->total_net, 2) }}</td>
                                <td class="text-right">K {{ number_format($summary->total_nasfund_ee, 2) }}</td>
                                <td class="text-center">{{ $summary->total_payrolls }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Fortnightly Summary -->
            @if(count($fortnightData) > 0)
            <div class="fortnight-section">
                <h3>Fortnight-by-Fortnight Summary - {{ $selectedYear }}</h3>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table-report w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">Fortnight</th>
                                    <th class="text-left">Period</th>
                                    <th class="text-center">Employees</th>
                                    <th class="text-right">Gross Wage</th>
                                    <th class="text-right">Tax</th>
                                    <th class="text-right">Net Pay</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fortnightData as $fn)
                                <tr>
                                    <td class="font-medium">{{ $fn->fortnight_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($fn->period_start)->format('d M') }} - {{ \Carbon\Carbon::parse($fn->period_end)->format('d M, Y') }}</td>
                                    <td class="text-center">
                                        <span class="inline-block bg-blue-50 text-blue-700 px-2 py-1 rounded text-xs font-semibold">
                                            {{ $fn->total_employees }}
                                        </span>
                                    </td>
                                    <td class="text-right">K {{ number_format($fn->total_gross, 2) }}</td>
                                    <td class="text-right text-orange-600">K {{ number_format($fn->total_tax, 2) }}</td>
                                    <td class="text-right text-green-600">K {{ number_format($fn->total_net, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge-status {{ strtolower($fn->status) }}">
                                            {{ $fn->status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="total-row">
                                    <td colspan="2" class="text-right">TOTAL</td>
                                    <td class="text-center">{{ $fortnightData->sum('total_employees') }}</td>
                                    <td class="text-right">K {{ number_format($fortnightData->sum('total_gross'), 2) }}</td>
                                    <td class="text-right">K {{ number_format($fortnightData->sum('total_tax'), 2) }}</td>
                                    <td class="text-right">K {{ number_format($fortnightData->sum('total_net'), 2) }}</td>
                                    <td class="text-center"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Footer Info -->
            <div class="mt-4 text-sm text-gray-500 flex justify-between">
                <span>
                    Showing {{ count($reportData) }} employees for {{ $selectedYear }}
                </span>
                <span>
                    Generated: {{ now()->format('d M Y H:i:s') }} | By: {{ auth()->user()->name }}
                </span>
            </div>
        @elseif($selectedYear)
            <!-- Empty State - No Data -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="empty-state">
                    <div class="icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h3>No Earnings Data Found</h3>
                    <p>No payroll records found for {{ $selectedYear }}.</p>
                    <p class="text-sm text-gray-400 mt-2">Ensure payroll has been processed for this year.</p>
                </div>
            </div>
        @else
            <!-- Empty State - No Selection -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="empty-state">
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Select a Year</h3>
                    <p>Choose a year from the filter above to generate the earnings summary report.</p>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
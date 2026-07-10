@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Payroll
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / Payroll
        </div>
    </div>
@endsection

@section('content')
<style>
    .payroll-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .payroll-header .company-name {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .payroll-header .payroll-info {
        font-size: 14px;
        color: #a0aec0;
    }
    .payroll-header .payroll-info .value {
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
    .table-payroll .payroll-code {
        font-weight: 600;
        color: #2563eb;
        font-size: 13px;
    }
    .table-payroll .fortnight {
        font-weight: 500;
        color: #0f172a;
    }
    .table-payroll .date-created {
        font-size: 12px;
        color: #94a3b8;
    }
    .table-payroll .created-by {
        font-size: 13px;
        color: #334155;
    }
    .table-payroll .count-badge {
        display: inline-block;
        background: #dbeafe;
        color: #1e40af;
        font-weight: 700;
        font-size: 13px;
        padding: 2px 12px;
        border-radius: 12px;
        min-width: 30px;
        text-align: center;
    }
    .badge-status {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.draft {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-status.approved {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.processing {
        background: #dbeafe;
        color: #1e40af;
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
    .btn-action.view {
        background: #dbeafe;
        color: #1e40af;
    }
    .btn-action.view:hover {
        background: #bfdbfe;
    }
    .btn-action.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.delete:hover {
        background: #fecaca;
    }
    .btn-action.summary {
        background: #e0e7ff;
        color: #3730a3;
    }
    .btn-action.summary:hover {
        background: #c7d2fe;
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
    .filter-input {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
        width: 100%;
        transition: border-color 0.2s;
    }
    .filter-input:focus {
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
    .action-buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    @media (max-width: 768px) {
        .table-payroll { font-size: 11px; }
        .table-payroll thead th, .table-payroll tbody td { padding: 6px 8px; }
        .stat-box .stat-value { font-size: 16px; }
        .payroll-header { padding: 16px; }
        .payroll-header .company-name { font-size: 16px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Payroll Header -->
        <div class="payroll-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="company-name">{{ auth()->user()->company->name ?? 'Company' }}</div>
                    <div class="payroll-info mt-1">
                        <span class="value">{{ $payrolls->total() }} total payrolls</span>
                        <span class="text-gray-500 mx-2">|</span>
                        <span class="value">{{ $payrolls->where('status', 'Draft')->count() }} Draft</span>
                        <span class="text-gray-500 mx-2">|</span>
                        <span class="value">{{ $payrolls->where('status', 'Approved')->count() }} Approved</span>
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('payroll.create') }}" class="btn-create">
                        + Create Payroll
                    </a>
                </div>
            </div>
        </div>


        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Fortnight</label>
                    <select id="fortnight_filter" class="filter-select">
                        <option value="">All Fortnights</option>
                        @foreach($fortnights ?? [] as $fn)
                            @php
                                $period = $fortnightPeriods[$fn] ?? null;
                            @endphp
                            <option value="{{ $fn }}">
                                {{ $fn }}
                                @if($period)
                                    ({{ \Carbon\Carbon::parse($period['start'])->format('d M') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select id="status_filter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Draft">Draft</option>
                        <option value="Approved">Approved</option>
                        <option value="Processing">Processing</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input type="text" id="search_input" placeholder="Search payroll code..." class="filter-input">
                </div>
                <div class="flex items-end">
                    <button id="apply_filters" class="btn-filter">Apply Filters</button>
                </div>
            </div>
        </div>

        <!-- Payroll List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-payroll w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Payroll Code</th>
                            <th class="text-left">Fortnight</th>
                            <th class="text-left">Period</th>
                            <th class="text-left">Created By</th>
                            <th class="text-center">Employees</th>
                            <th class="text-right">Total Gross</th>
                            <th class="text-right">Total Net</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                        <tr>
                            <td>
                                <div class="payroll-code">P-{{ $payroll->company->code ?? 'LKP' }}-{{ str_pad($payroll->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="date-created">{{ $payroll->created_at->format('M d, Y H:i') }}</div>
                            </td>
                            <td class="fortnight">{{ $payroll->fortnight_number }}</td>
                            <td>
                                <div style="font-size: 12px; color: #64748b;">
                                    {{ $payroll->period_start->format('M d') }} - {{ $payroll->period_end->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="created-by">{{ $payroll->createdBy->name ?? 'Super Admin' }}</td>
                            <td class="text-center">
                                <span class="count-badge">{{ $payroll->total_employees }}</span>
                            </td>
                            <td class="text-right font-medium">K {{ number_format($payroll->total_gross, 2) }}</td>
                            <td class="text-right font-medium text-green-600">K {{ number_format($payroll->total_net, 2) }}</td>
                            <td class="text-center">
                                <span class="badge-status {{ strtolower($payroll->status) }}">
                                    {{ $payroll->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('payroll.show', $payroll) }}" class="btn-action view" title="View Payroll">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('payroll.summary', ['fortnight' => $payroll->fortnight_number]) }}" class="btn-action summary" title="View Summary">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <form method="POST" action="{{ route('payroll.destroy', $payroll) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action delete" title="Delete Payroll" onclick="return confirm('Delete this payroll?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                                <svg class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-700 mb-1">No Payroll Records</h3>
                                <p class="text-gray-500 mb-4">Create your first payroll to get started.</p>
                                <a href="{{ route('payroll.create') }}" class="btn-create">
                                    + Create Payroll
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing {{ $payrolls->firstItem() ?? 0 }} to {{ $payrolls->lastItem() ?? 0 }} of {{ $payrolls->total() }} results
            </div>
            <div>
                {{ $payrolls->links() }}
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Apply filters
        document.getElementById('apply_filters')?.addEventListener('click', function() {
            const url = new URL(window.location.href);
            const fortnight = document.getElementById('fortnight_filter').value;
            const status = document.getElementById('status_filter').value;
            const search = document.getElementById('search_input').value;
            
            if (fortnight) url.searchParams.set('fortnight', fortnight);
            else url.searchParams.delete('fortnight');
            
            if (status) url.searchParams.set('status', status);
            else url.searchParams.delete('status');
            
            if (search) url.searchParams.set('search', search);
            else url.searchParams.delete('search');
            
            window.location.href = url.toString();
        });

        // Enter key for search
        document.getElementById('search_input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('apply_filters').click();
            }
        });
    });
</script>
@endsection
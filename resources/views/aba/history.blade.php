@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ABA Generation History
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / ABA History
        </div>
    </div>
@endsection

@section('content')
<style>
    .history-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .history-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .history-header .header-subtitle {
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
        color: white;
        text-decoration: none;
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
        text-decoration: none;
        display: inline-block;
    }
    .btn-filter:hover {
        background: #e2e8f0;
        text-decoration: none;
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
    .btn-action.download {
        background: #dcfce7;
        color: #166534;
    }
    .btn-action.download:hover {
        background: #bbf7d0;
    }
    .btn-action.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.delete:hover {
        background: #fecaca;
    }
    .btn-action.delete:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .badge-status {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.completed {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.draft {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-status.submitted {
        background: #dbeafe;
        color: #1e40af;
    }
    .badge-status.generated {
        background: #e0e7ff;
        color: #3730a3;
    }
    .badge-status.uploaded {
        background: #fef3c7;
        color: #92400e;
    }
    .filter-select {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
        background: white;
        width: 100%;
        min-width: 200px;
        transition: border-color 0.2s;
    }
    .filter-select:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .table-history {
        font-size: 13px;
    }
    .table-history thead th {
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
    .table-history tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-history tbody tr:hover {
        background: #f8fafc;
    }
    .table-history .batch-number {
        font-weight: 600;
        color: #2563eb;
        font-size: 13px;
    }
    .table-history .amount {
        font-weight: 600;
        color: #0f172a;
    }
    .action-buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex-wrap: wrap;
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
        .table-history { font-size: 11px; }
        .table-history thead th, .table-history tbody td { padding: 6px 8px; }
        .stat-box .stat-value { font-size: 16px; }
        .history-header { padding: 16px; }
        .history-header .header-title { font-size: 16px; }
        .action-buttons { gap: 2px; }
        .btn-action { padding: 3px 7px; font-size: 10px; }
        .filter-select { min-width: 100%; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="history-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-history text-indigo-300 mr-2"></i> ABA Generation History
                    </div>
                    <div class="header-subtitle mt-1">
                        View all ABA file generations
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('aba.index') }}" class="btn-create">
                        <i class="fas fa-plus"></i> New Generation
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

        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
            <form method="GET" action="{{ route('aba.history') }}" class="flex flex-wrap items-end gap-4">
                <div style="flex: 1; min-width: 200px;">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Company</label>
                    <select name="company_id" class="filter-select">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ $companyId == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    @if($companyId)
                        <a href="{{ route('aba.history') }}" class="btn-filter" style="margin-left: 4px;">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- History Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-history w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Batch #</th>
                            <th class="text-left">Company</th>
                            <th class="text-left">Payroll Period</th>
                            <th class="text-left">Processing Date</th>
                            <th class="text-right">Amount</th>
                            <th class="text-center">Transactions</th>
                            <th class="text-center">Status</th>
                            <th class="text-left">Generated By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $batch)
                            <tr>
                                <td>
                                    <div class="batch-number">{{ $batch->batch_number }}</div>
                                </td>
                                <td>{{ $batch->company->name ?? 'N/A' }}</td>
                                <td>{{ $batch->payroll->pay_period ?? 'N/A' }}</td>
                                <td>{{ $batch->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-right amount">K {{ number_format($batch->total_amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                        {{ $batch->total_transactions }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge-status {{ strtolower($batch->status) }}">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </td>
                                <td>{{ $batch->generator->name ?? 'System' }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('aba.show', $batch->id) }}" class="btn-action view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('aba.download', $batch->id) }}" class="btn-action download" title="Download ABA File">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($batch->status != 'submitted')
                                            <form action="{{ route('aba.destroy', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this batch? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action delete" title="Delete Batch">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn-action delete" disabled title="Cannot delete submitted batch">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="icon">
                                            <i class="fas fa-file-invoice"></i>
                                        </div>
                                        <h3>No ABA Files Generated</h3>
                                        <p>No ABA files have been generated yet. Create your first ABA file now.</p>
                                        <a href="{{ route('aba.index') }}" class="btn-create">
                                            <i class="fas fa-plus"></i> Generate ABA File
                                        </a>
                                    </div>
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
                Showing {{ $history->firstItem() ?? 0 }} to {{ $history->lastItem() ?? 0 }} of {{ $history->total() }} results
            </div>
            <div>
                {{ $history->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
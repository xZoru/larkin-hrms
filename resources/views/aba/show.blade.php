@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ABA File Details
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / ABA History / {{ $batch->batch_number }}
        </div>
    </div>
@endsection

@section('content')
<style>
    .show-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .show-header .batch-number {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .show-header .batch-status {
        font-size: 14px;
        color: #a0aec0;
    }
    .badge-status {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 14px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.generated {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.downloaded {
        background: #dbeafe;
        color: #1e40af;
    }
    .badge-status.submitted {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-status.completed {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.draft {
        background: #fef3c7;
        color: #92400e;
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
    .stat-box .stat-value.blue { color: #2563eb; }
    .stat-box .stat-value.purple { color: #7c3aed; }
    
    .detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .detail-card .card-header {
        background: #f8fafc;
        padding: 14px 20px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        color: #1a1f36;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .detail-card .card-header i {
        color: #6366f1;
    }
    .detail-card .card-body {
        padding: 20px;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .detail-item {
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .detail-item .label {
        font-size: 11px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: 600;
    }
    .detail-item .value {
        font-size: 14px;
        font-weight: 500;
        color: #0f172a;
        margin-top: 2px;
    }
    .detail-item .value .highlight {
        color: #2563eb;
        font-weight: 600;
    }
    
    .btn-action {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        width: 100%;
    }
    .btn-action.btn-download {
        background: #22c55e;
        color: white;
    }
    .btn-action.btn-download:hover {
        background: #16a34a;
        color: white;
    }
    .btn-action.btn-excel {
        background: #0ea5e9;
        color: white;
    }
    .btn-action.btn-excel:hover {
        background: #0284c7;
        color: white;
    }
    .btn-action.btn-preview {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .btn-action.btn-preview:hover {
        background: #e2e8f0;
    }
    .btn-action.btn-outline {
        background: transparent;
        color: #475569;
        border: 1px solid #d1d5db;
    }
    .btn-action.btn-outline:hover {
        background: #f1f5f9;
    }
    .btn-action.btn-danger-outline {
        background: transparent;
        color: #ef4444;
        border: 1px solid #fecaca;
    }
    .btn-action.btn-danger-outline:hover {
        background: #fef2f2;
    }
    
    .action-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .action-divider {
        border-top: 1px solid #e5e7eb;
        margin: 12px 0;
    }
    
    .preview-container {
        background: #1e293b;
        border-radius: 8px;
        padding: 16px;
        max-height: 400px;
        overflow: auto;
    }
    .preview-container code {
        color: #e2e8f0;
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 12px;
        white-space: pre;
        word-wrap: normal;
    }
    .preview-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        margin-right: 4px;
    }
    .preview-badge.header {
        background: #3b82f6;
        color: white;
    }
    .preview-badge.transaction {
        background: #22c55e;
        color: white;
    }
    .preview-badge.trailer {
        background: #f59e0b;
        color: white;
    }
    
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
    }
    
    @media (max-width: 768px) {
        .grid-2, .grid-3 {
            grid-template-columns: 1fr;
        }
        .detail-grid {
            grid-template-columns: 1fr;
        }
        .show-header .batch-number { font-size: 16px; }
        .stat-box .stat-value { font-size: 16px; }
        .preview-container { max-height: 300px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="show-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="batch-number">
                        <i class="fas fa-file-invoice text-indigo-300 mr-2"></i> {{ $batch->batch_number }}
                    </div>
                    <div class="batch-status mt-1">
                        <span class="badge-status {{ strtolower($batch->status) }}">
                            {{ ucfirst($batch->status) }}
                        </span>
                        @if($batch->status == 'generated')
                            <span class="ml-2">— File is ready for download</span>
                        @elseif($batch->status == 'downloaded')
                            <span class="ml-2">— File has been downloaded</span>
                        @elseif($batch->status == 'submitted')
                            <span class="ml-2">— File has been submitted to bank</span>
                        @endif
                    </div>
                </div>
                <div class="mt-2 sm:mt-0 text-right text-sm text-gray-400">
                    Generated {{ $batch->created_at->diffForHumans() }}
                    @if($batch->generator)
                        <br>by {{ $batch->generator->name }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid-3 mb-6">
            <div class="stat-box">
                <div class="stat-value purple">{{ $batch->total_transactions }}</div>
                <div class="stat-label">Total Transactions</div>
            </div>
            <div class="stat-box">
                <div class="stat-value green">{{ $batch->formatted_amount }}</div>
                <div class="stat-label">Total Amount</div>
            </div>
            <div class="stat-box">
                <div class="stat-value blue">{{ $batch->company->name ?? 'N/A' }}</div>
                <div class="stat-label">Company</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid-2">
            <!-- Left: Details -->
            <div>
                <div class="detail-card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i> File Details
                    </div>
                    <div class="card-body">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <div class="label">Batch Number</div>
                                <div class="value highlight">{{ $batch->batch_number }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="label">Status</div>
                                <div class="value">
                                    <span class="badge-status {{ strtolower($batch->status) }}">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="label">Payroll Period</div>
                                <div class="value">
                                    @if($batch->payroll->period_start && $batch->payroll->period_end)
                                        {{ $batch->payroll->period_start->format('d M Y') }} - {{ $batch->payroll->period_end->format('d M Y') }}
                                    @elseif($batch->payroll->fortnight_number)
                                        FN{{ $batch->payroll->fortnight_number }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="label">Payment Type</div>
                                <div class="value">{{ $batch->metadata['payment_type'] ?? 'SALARY' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="label">Debit Description</div>
                                <div class="value">{{ $batch->metadata['debit_description'] ?? 'PAYROLL' }}</div>
                            </div>
                            <div class="detail-item">
                                <div class="label">Payment Date</div>
                                <div class="value">{{ isset($batch->metadata['payment_date']) ? date('d/m/Y', strtotime($batch->metadata['payment_date'])) : $batch->processing_date->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="card-header">
                        <i class="fas fa-university"></i> Bank Account Details
                    </div>
                    <div class="card-body">
                        <div class="grid-2" style="gap: 12px;">
                            <div>
                                <div class="label">Bank</div>
                                <div class="value">{{ $batch->bank_name }}</div>
                            </div>
                            <div>
                                <div class="label">BSB</div>
                                <div class="value">{{ $batch->bsb_number }}</div>
                            </div>
                            <div>
                                <div class="label">Account Number</div>
                                <div class="value">{{ $batch->account_number }}</div>
                            </div>
                            <div>
                                <div class="label">Account Name</div>
                                <div class="value">{{ $batch->account_name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Actions -->
            <div>
                <div class="detail-card">
                    <div class="card-header">
                        <i class="fas fa-cog"></i> Actions
                    </div>
                    <div class="card-body">
                        <div class="action-group">
                            <a href="{{ route('aba.download', $batch->id) }}" class="btn-action btn-download">
                                <i class="fas fa-download"></i> Download ABA File
                            </a>
                            <a href="{{ route('aba.export.excel', $batch->id) }}" class="btn-action btn-excel">
                                <i class="fas fa-file-excel"></i> Export as Excel
                            </a>
                            <button type="button" class="btn-action btn-preview" onclick="previewABA({{ $batch->id }})">
                                <i class="fas fa-eye"></i> Preview File
                            </button>
                        </div>

                        <div class="action-divider"></div>

                        <div class="action-group">
                            <a href="{{ route('aba.index') }}" class="btn-action btn-outline">
                                <i class="fas fa-plus"></i> Generate New
                            </a>
                            <a href="{{ route('aba.history') }}" class="btn-action btn-outline">
                                <i class="fas fa-history"></i> View History
                            </a>
                        </div>

                        <div class="action-divider"></div>

                        <form action="{{ route('aba.destroy', $batch->id) }}" method="POST" onsubmit="return confirm('Delete this ABA batch? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-danger-outline">
                                <i class="fas fa-trash"></i> Delete Batch
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="row mt-4" id="previewContainer" style="display:none;">
            <div class="col-12">
                <div class="detail-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-file-code"></i> File Preview</span>
                        <button type="button" class="btn-action btn-outline" style="width: auto; padding: 4px 16px; font-size: 12px;" onclick="document.getElementById('previewContainer').style.display='none';">
                            <i class="fas fa-times"></i> Close
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="preview-badge header">Header</span>
                            <span class="preview-badge transaction">Transaction</span>
                            <span class="preview-badge trailer">Trailer</span>
                        </div>
                        <div class="preview-container">
                            <code id="abaContent" style="color: #e2e8f0; font-family: 'Consolas', 'Courier New', monospace; font-size: 12px; white-space: pre; word-wrap: normal;">Loading...</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function previewABA(id) {
    document.getElementById('previewContainer').style.display = 'block';
    document.getElementById('abaContent').textContent = 'Loading...';
    
    fetch('/aba/preview/' + id)
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                document.getElementById('abaContent').textContent = response.content.replace(/<br\s*\/?>/gi, '\n');
            } else {
                document.getElementById('abaContent').textContent = 'Error: ' + response.message;
            }
        })
        .catch(function(error) {
            document.getElementById('abaContent').textContent = 'Failed to load preview.';
        });
}
</script>
@endsection
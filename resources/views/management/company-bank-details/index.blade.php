@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Company Bank Details
        </h2>
        <div class="text-sm text-gray-500">
            Management / Company Bank Details
        </div>
    </div>
@endsection

@section('content')
<style>
    .bank-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .bank-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .bank-header .header-subtitle {
        font-size: 14px;
        color: #a0aec0;
    }
    .table-bank {
        font-size: 13px;
    }
    .table-bank thead th {
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
    .table-bank tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-bank tbody tr:hover {
        background: #f8fafc;
    }
    .badge-status {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.configured {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.missing {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action {
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-action.edit {
        background: #dbeafe;
        color: #1e40af;
    }
    .btn-action.edit:hover {
        background: #bfdbfe;
    }
    .btn-action.view {
        background: #f1f5f9;
        color: #475569;
    }
    .btn-action.view:hover {
        background: #e2e8f0;
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
    .alert-custom .icon {
        font-size: 18px;
        flex-shrink: 0;
    }
    @media (max-width: 768px) {
        .bank-header { padding: 16px; }
        .table-bank { font-size: 11px; }
        .table-bank thead th, .table-bank tbody td { padding: 6px 8px; }
    }
</style>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="bank-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-university text-indigo-300 mr-2"></i> Company Bank Details
                    </div>
                    <div class="header-subtitle mt-1">
                        Manage bank account details for ABA file generation
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-custom alert-success">
                <span class="icon">✅</span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <!-- Bank Details Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-bank w-full">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Bank Code</th>
                            <th>APCA ID</th>
                            <th>BSB Code</th>
                            <th>Account Number</th>
                            <th>Account Name</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                        <tr>
                            <td class="font-medium">{{ $company->name }}</td>
                            <td>{{ $company->bank_code ?? '-' }}</td>
                            <td>{{ $company->apca_user_id ?? '-' }}</td>
                            <td>{{ $company->bsb_code ?? '-' }}</td>
                            <td>{{ $company->bank_account_number ?? '-' }}</td>
                            <td>{{ $company->bank_account_name ?? '-' }}</td>
                            <td class="text-center">
                                @php
                                    $isConfigured = $company->bank_name && $company->bsb_code && $company->bank_account_number;
                                @endphp
                                <span class="badge-status {{ $isConfigured ? 'configured' : 'missing' }}">
                                    {{ $isConfigured ? '✅ Configured' : '⚠️ Missing' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('company-bank-details.edit', $company) }}" class="btn-action edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                No companies found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
            <div class="flex items-start gap-3">
                <div class="text-blue-500 text-xl">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h4 class="font-medium text-blue-800">About Bank Details</h4>
                    <p class="text-sm text-blue-700 mt-1">
                        These bank details are used when generating ABA files in the <strong>Payroll → ABA Bank File</strong> module.
                        If you leave fields blank, you can manually enter them during ABA generation.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
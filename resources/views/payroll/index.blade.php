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
    
    /* ============================================
       DROPDOWN ACTIONS - FIXED FOR TABLE
       ============================================ */
    .dropdown-container {
        position: relative;
        display: inline-block;
    }
    .dropdown-toggle {
        background: #f1f5f9;
        color: #475569;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }
    .dropdown-toggle:hover {
        background: #e2e8f0;
    }
    .dropdown-toggle i {
        font-size: 10px;
        transition: transform 0.2s;
    }
    .dropdown-toggle.open i {
        transform: rotate(180deg);
    }
    .dropdown-menu {
        position: fixed;
        min-width: 200px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        z-index: 99999;
        display: none;
        padding: 6px 0;
    }
    .dropdown-menu.show {
        display: block;
    }
    .dropdown-menu .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        color: #334155;
        font-size: 13px;
        text-decoration: none;
        transition: background 0.15s;
        border: none;
        background: none;
        width: 100%;
        cursor: pointer;
        text-align: left;
        font-weight: 400;
        font-family: inherit;
        line-height: 1.5;
    }
    .dropdown-menu .dropdown-item:hover {
        background: #f1f5f9;
    }
    .dropdown-menu .dropdown-item i {
        width: 18px;
        font-size: 14px;
        color: #64748b;
        text-align: center;
        flex-shrink: 0;
    }
    .dropdown-menu .dropdown-item.text-danger {
        color: #dc2626;
    }
    .dropdown-menu .dropdown-item.text-danger i {
        color: #dc2626;
    }
    .dropdown-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 4px 12px;
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
    
    @media (max-width: 768px) {
        .table-payroll { font-size: 11px; }
        .table-payroll thead th, .table-payroll tbody td { padding: 6px 8px; }
        .stat-box .stat-value { font-size: 16px; }
        .payroll-header { padding: 16px; }
        .payroll-header .company-name { font-size: 16px; }
        .dropdown-menu {
            min-width: 180px;
        }
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
                <div class="mt-2 sm:mt-0 flex items-center gap-2">
                    <a href="{{ route('payroll.export-all-excel', ['fortnight' => request('fortnight')]) }}" class="btn-create" style="background: #15803d;">
                        <i class="fas fa-download"></i> Download Filtered Payruns
                    </a>
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
                            <option value="{{ $fn }}" @selected(request('fortnight') == $fn)>
                                {{ $fn }}
                                @if($period)
                                    ({{ \Carbon\Carbon::parse($period['start'])->format('d/m/y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d/m/y') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select id="status_filter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Draft" @selected(request('status') === 'Draft')>Draft</option>
                        <option value="Approved" @selected(request('status') === 'Approved')>Approved</option>
                        <option value="Processing" @selected(request('status') === 'Processing')>Processing</option>
                        <option value="Paid" @selected(request('status') === 'Paid')>Paid</option>
                        <option value="Locked" @selected(request('status') === 'Locked')>Locked</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input type="text" id="search_input" value="{{ request('search') }}" placeholder="Search payroll code..." class="filter-input">
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
                                <div class="payroll-code">{{ $payroll->display_code }}</div>
                                <div class="date-created">{{ $payroll->created_at->format('d/m/y H:i') }}</div>
                            </td>
                            <td class="fortnight">{{ $payroll->fortnight_number }}</td>
                            <td>
                                <div style="font-size: 12px; color: #64748b;">
                                    {{ $payroll->period_start->format('d/m/y') }} - {{ $payroll->period_end->format('d/m/y') }}
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
                            <td class="text-center">
                                <div class="dropdown-container">
                                    <button type="button" class="dropdown-toggle" onclick="toggleDropdown(event, this)">
                                        <span>Actions</span>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('payroll.summary', ['payroll_id' => $payroll->id]) }}" class="dropdown-item">
                                            <i class="fas fa-chart-bar"></i> View Summary
                                        </a>
                                        
                                        <button type="button" class="dropdown-item" onclick="printPayslips('{{ $payroll->id }}', 'national')">
                                            <i class="fas fa-file-invoice"></i> Print National Payslips
                                        </button>

                                        <button type="button" class="dropdown-item" onclick="printPayslips('{{ $payroll->id }}', 'expatriate')">
                                            <i class="fas fa-file-invoice"></i> Print Expatriate Payslips
                                        </button>
                                        
                                        <button type="button" class="dropdown-item" onclick="printSigning('{{ $payroll->id }}')">
                                            <i class="fas fa-signature"></i> Print Signing
                                        </button>

                                        <div class="dropdown-divider"></div>

                                        <div class="dropdown-item" style="padding: 8px 16px; cursor: default;">
                                            <form method="POST" action="{{ route('payroll.update-status', $payroll) }}" class="w-full d-flex align-items-center gap-2" style="width: 100%;">
                                                @csrf
                                                <i class="fas fa-toggle-on"></i>
                                                <select name="status" onchange="this.form.submit()" 
                                                        style="border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px; padding: 4px 6px; flex: 1;"
                                                        onclick="event.stopPropagation()">
                                                    <option value="Draft" {{ $payroll->status == 'Draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="Approved" {{ $payroll->status == 'Approved' ? 'selected' : '' }}>Approved</option>
                                                    <option value="Paid" {{ $payroll->status == 'Paid' ? 'selected' : '' }}>Paid</option>
                                                    <option value="Locked" {{ $payroll->status == 'Locked' ? 'selected' : '' }}>Locked</option>
                                                </select>
                                            </form>
                                        </div>
                                        
                                        <div class="dropdown-divider"></div>
                                        
                                        <a href="{{ route('payroll.export-excel', $payroll) }}" class="dropdown-item">
                                            <i class="fas fa-file-excel"></i> Export Excel
                                        </a>
                                        
                                        <div class="dropdown-divider"></div>
                                        
                                        <form method="POST" action="{{ route('payroll.destroy', $payroll) }}" 
                                            class="inline w-full" 
                                            onsubmit="return confirm('Are you sure you want to delete this payroll? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash"></i> Delete Payroll
                                            </button>
                                        </form>
                                    </div>
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

    // ============================================
    // DROPDOWN TOGGLE - FIXED POSITION
    // ============================================
    function toggleDropdown(event, button) {
        event.preventDefault();
        event.stopPropagation();
        
        const container = button.closest('.dropdown-container');
        const menu = container.querySelector('.dropdown-menu');
        const isOpen = menu.classList.contains('show');
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu.show').forEach(function(el) {
            el.classList.remove('show');
            const toggle = el.closest('.dropdown-container').querySelector('.dropdown-toggle');
            if (toggle) toggle.classList.remove('open');
        });
        
        if (isOpen) {
            menu.classList.remove('show');
            button.classList.remove('open');
        } else {
            // Position the menu using fixed positioning
            const rect = button.getBoundingClientRect();
            menu.style.top = (rect.bottom + 5) + 'px';
            menu.style.left = (rect.left - 50) + 'px';
            
            // Make sure it stays within viewport
            const menuWidth = parseInt(menu.style.minWidth) || 200;
            const rightEdge = rect.left + menuWidth - 50;
            const viewportWidth = window.innerWidth;
            
            if (rightEdge > viewportWidth - 10) {
                menu.style.left = (viewportWidth - menuWidth - 10) + 'px';
            }
            
            menu.classList.add('show');
            button.classList.add('open');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        document.querySelectorAll('.dropdown-container').forEach(function(container) {
            const menu = container.querySelector('.dropdown-menu');
            const toggle = container.querySelector('.dropdown-toggle');
            if (menu && menu.classList.contains('show') && !container.contains(event.target)) {
                menu.classList.remove('show');
                if (toggle) toggle.classList.remove('open');
            }
        });
    });

    // Close on Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(el) {
                el.classList.remove('show');
                const toggle = el.closest('.dropdown-container').querySelector('.dropdown-toggle');
                if (toggle) toggle.classList.remove('open');
            });
        }
    });

    // Close on scroll
    document.addEventListener('scroll', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(el) {
            el.classList.remove('show');
            const toggle = el.closest('.dropdown-container').querySelector('.dropdown-toggle');
            if (toggle) toggle.classList.remove('open');
        });
    });

    // ============================================
    // PRINT FUNCTIONS
    // ============================================
    function printPayslips(payrollId, type) {
        const url = '/payroll/' + payrollId + '/print-payslips/' + type;
        window.open(url, '_blank');
        closeAllDropdowns();
    }

    function printSigning(payrollId) {
        const url = '/payroll/' + payrollId + '/print-signing';
        window.open(url, '_blank');
        closeAllDropdowns();
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(el) {
            el.classList.remove('show');
            const toggle = el.closest('.dropdown-container').querySelector('.dropdown-toggle');
            if (toggle) toggle.classList.remove('open');
        });
    }
</script>
@endsection

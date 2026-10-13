@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
        <div class="text-sm text-gray-500">
            {{ session('current_company_name') ?? auth()->user()->company->name ?? 'Company' }}
        </div>
    </div>
@endsection

@section('content')
<style>
    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 14px 18px;
        border: 1px solid #e5e7eb;
        text-align: center;
        transition: all 0.2s;
    }
    .stat-card:hover {
        border-color: #6366f1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .stat-card .stat-value {
        font-size: 22px;
        font-weight: 700;
        color: #1a1f36;
    }
    .stat-card .stat-label {
        font-size: 10px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-top: 2px;
    }
    .stat-card .stat-icon {
        font-size: 20px;
        opacity: 0.6;
        margin-bottom: 4px;
        display: block;
    }
    .widget-box {
        background: white;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
    }
    .widget-box .widget-header {
        padding: 10px 16px;
        border-bottom: 1px solid #e5e7eb;
        background: #fafafa;
        font-weight: 600;
        font-size: 13px;
        color: #374151;
    }
    .widget-box .widget-body {
        padding: 10px 16px;
        max-height: 200px;
        overflow-y: auto;
    }
    .widget-box .widget-body .empty-state {
        text-align: center;
        padding: 16px 0;
        color: #9ca3af;
        font-size: 13px;
    }
    .list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
    }
    .list-item:last-child {
        border-bottom: none;
    }
    .list-item .name {
        color: #1f2937;
        font-weight: 500;
    }
    .list-item .meta {
        color: #9ca3af;
        font-size: 12px;
    }
    .badge-urgent {
        background: #fee2e2;
        color: #991b1b;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 8px;
        border-radius: 10px;
    }
    .badge-warning {
        background: #fef3c7;
        color: #92400e;
        font-size: 10px;
        font-weight: 600;
        padding: 1px 8px;
        border-radius: 10px;
    }
    .grid-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .grid-4col {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 16px;
    }
    .grid-3col {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
    @media (max-width: 768px) {
        .grid-4col { grid-template-columns: repeat(2, 1fr); }
        .grid-3col { grid-template-columns: 1fr; }
        .grid-2col { grid-template-columns: 1fr; }
    }

    .expiry-layout{display:grid;grid-template-columns:minmax(0,2fr) minmax(280px,0.95fr);gap:16px;align-items:start}.expiry-tracker,.birthday-panel{min-width:0}.expiry-header{display:flex;align-items:center;justify-content:space-between;gap:12px}.expiry-tools{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;border-bottom:1px solid #e5e7eb}.expiry-tabs{display:flex;gap:5px}.expiry-tab{border:0;background:transparent;color:#6b7280;padding:7px 10px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer}.expiry-tab.active{background:#eef2ff;color:#4f46e5}.expiry-search{border:1px solid #d1d5db;border-radius:6px;padding:7px 10px;font-size:12px;width:min(240px,45%)}.expiry-table-wrap{overflow-x:auto}.expiry-table{width:100%;border-collapse:collapse;font-size:12px}.expiry-table th{text-align:left;color:#6b7280;font-size:10px;text-transform:uppercase;letter-spacing:.04em;background:#fafafa;padding:9px 12px;white-space:nowrap}.expiry-table td{padding:10px 12px;border-top:1px solid #f3f4f6;color:#4b5563}.employee-cell{display:flex;align-items:center;gap:9px;min-width:145px}.employee-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover}.avatar-fallback{display:grid;place-items:center;background:#e0e7ff;color:#4338ca;font-weight:700}.employee-name{font-weight:600;color:#1f2937;text-decoration:none}.employee-name:hover{color:#4f46e5}.employee-number{color:#9ca3af;font-size:10px;margin-top:2px}.doc-pill{background:#f3f4f6;color:#4b5563;border-radius:999px;padding:3px 8px;font-size:10px}.days-badge{font-size:10px;font-weight:700;padding:4px 8px;border-radius:999px;white-space:nowrap}.badge-urgent{background:#fee2e2;color:#991b1b}.badge-warning{background:#fef3c7;color:#92400e}.action-cell{display:flex;gap:10px;white-space:nowrap}.action-link{font-size:10px;font-weight:600;color:#4f46e5;text-decoration:none}.action-link:hover{text-decoration:underline}.action-link[aria-disabled=true]{color:#9ca3af;pointer-events:none}.dashboard-button{border:1px solid #d1d5db;background:#fff;color:#4b5563;padding:6px 9px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap}.dashboard-button:hover{background:#f9fafb}.birthday-today,.birthday-upcoming{padding:12px 16px}.birthday-today{background:#fff7fb;border-bottom:1px solid #f3f4f6}.birthday-upcoming{max-height:275px;overflow:auto}.birthday-subhead{display:flex;justify-content:space-between;align-items:center;font-size:11px;font-weight:700;color:#374151;margin-bottom:8px}.celebration-badge{background:#fce7f3;color:#be185d;border-radius:999px;padding:3px 7px;font-size:9px}.birthday-item{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 0;border-bottom:1px solid #f3f4f6}.birthday-item:last-child{border-bottom:0}.today-item{padding-top:3px}.birthday-date{font-size:11px;font-weight:700;color:#6366f1;white-space:nowrap}.birthday-group-date{font-size:10px;font-weight:700;color:#6b7280;padding:7px 0 3px;border-bottom:1px solid #f3f4f6}.country-code{font-size:9px;font-weight:700;color:#6b7280;background:#f3f4f6;padding:3px 6px;border-radius:4px}.birthday-empty{font-size:11px;color:#9ca3af;padding:8px 0}.wish-button{color:#be185d;border-color:#fbcfe8}.empty-state{text-align:center;padding:18px;color:#9ca3af;font-size:12px}.hidden{display:none!important}
    @media(max-width:900px){.expiry-layout{grid-template-columns:1fr}.expiry-tools{align-items:flex-start;flex-direction:column}.expiry-search{width:100%}}@media(max-width:640px){.expiry-header{align-items:flex-start}.expiry-table{min-width:690px}.expiry-tabs{width:100%;justify-content:space-between}.expiry-tabs button{padding:7px 6px}}

</style>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- ===== EXPIRY WIDGET ===== -->
        @include('partials.expiry-widget')

        <!-- ===== 3-COLUMN WIDGETS ===== -->
        <div class="grid-3col">
            <!-- Recent Employees -->
            <div class="widget-box">
                <div class="widget-header">
                    <i class="fas fa-user-plus text-indigo-500 mr-2"></i> Recent Employees
                </div>
                <div class="widget-body">
                    @forelse($recentEmployees ?? [] as $employee)
                        <div class="list-item">
                            <span class="name">{{ $employee->full_name }}</span>
                            <span class="meta">{{ $employee->employee_number }}</span>
                        </div>
                    @empty
                        <div class="empty-state">No employees yet</div>
                    @endforelse
                </div>
            </div>

            <!-- Pending Leave -->
            <div class="widget-box">
                <div class="widget-header">
                    <i class="fas fa-clock text-yellow-500 mr-2"></i> Pending Leave
                </div>
                <div class="widget-body">
                    @forelse($recentLeaveRequests ?? [] as $leave)
                        <div class="list-item">
                            <span class="name">{{ $leave->employee->full_name ?? 'N/A' }}</span>
                            <span class="meta">{{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/y') }}</span>
                        </div>
                    @empty
                        <div class="empty-state">No pending requests</div>
                    @endforelse
                </div>
            </div>

            <!-- Upcoming Payroll -->
            <div class="widget-box">
                <div class="widget-header">
                    <i class="fas fa-money-bill-wave text-green-500 mr-2"></i> Upcoming Payroll
                </div>
                <div class="widget-body">
                    @php
                        $nextPayroll = \App\Models\Payroll::where('company_id', auth()->user()->company_id)
                            ->where('status', 'Draft')
                            ->orderBy('period_end')
                            ->first();
                    @endphp
                    @if($nextPayroll)
                        <div class="list-item">
                            <span class="name">Fortnight {{ $nextPayroll->fortnight_number }}</span>
                            <span class="meta">{{ \Carbon\Carbon::parse($nextPayroll->period_end)->format('d/m/y') }}</span>
                        </div>
                        <div class="list-item">
                            <span class="name">Employees</span>
                            <span class="meta">{{ $nextPayroll->total_employees ?? 0 }}</span>
                        </div>
                        <div class="list-item">
                            <span class="name">Status</span>
                            <span class="badge-warning">{{ $nextPayroll->status }}</span>
                        </div>
                    @else
                        <div class="empty-state">No upcoming payroll</div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

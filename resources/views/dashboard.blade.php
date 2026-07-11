@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
        <div class="text-sm text-gray-500">
            {{ auth()->user()->company->name ?? 'Company' }}
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
</style>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- ===== STATS ROW ===== -->
        <div class="grid-4col">
            <div class="stat-card">
                <i class="fas fa-users stat-icon" style="color: #6366f1;"></i>
                <div class="stat-value">{{ $totalEmployees ?? 0 }}</div>
                <div class="stat-label">Employees</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-alt stat-icon" style="color: #d97706;"></i>
                <div class="stat-value">{{ $pendingLeaves ?? 0 }}</div>
                <div class="stat-label">Pending Leave</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-wallet stat-icon" style="color: #16a34a;"></i>
                <div class="stat-value">K {{ number_format($totalPayroll ?? 0, 2) }}</div>
                <div class="stat-label">This Fortnight</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-hand-holding-usd stat-icon" style="color: #7c3aed;"></i>
                <div class="stat-value">{{ $activeLoans ?? 0 }}</div>
                <div class="stat-label">Active Loans</div>
            </div>
        </div>

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
                            <span class="meta">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M') }}</span>
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
                            <span class="meta">{{ \Carbon\Carbon::parse($nextPayroll->period_end)->format('d M') }}</span>
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
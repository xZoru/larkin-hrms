@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Timesheet Entry
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / Timesheet Entry
        </div>
    </div>
@endsection

@section('content')
<style>
    .timesheet-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .timesheet-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .timesheet-header .header-subtitle {
        font-size: 14px;
        color: #a0aec0;
    }
    .stat-box {
        background: rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 12px 16px;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.05);
    }
    .stat-box .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: white;
    }
    .stat-box .stat-label {
        font-size: 11px;
        color: #a0aec0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    .timesheet-container {
        max-width: 100%;
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        padding: 24px;
        border: 1px solid #e5e7eb;
    }
    .pay-period-header {
        background: #f8fafc;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        border: 1px solid #e2e8f0;
    }
    .pay-period-label {
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .pay-period-value {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
    }
    .pay-period-range {
        font-size: 14px;
        color: #64748b;
    }
    .status-badge {
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    .status-draft {
        background: #fef3c7;
        color: #92400e;
    }
    .status-final {
        background: #dcfce7;
        color: #166534;
    }
    .status-locked {
        background: #fee2e2;
        color: #991b1b;
    }
    .timesheet-table th {
        background: #f8fafc;
        font-size: 10px;
        text-transform: uppercase;
        color: #475569;
        font-weight: 600;
        padding: 10px 12px;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
        letter-spacing: 0.3px;
    }
    .timesheet-table td {
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .timesheet-table tr:hover td {
        background: #fafbfc;
    }
    .timesheet-table .date-cell {
        font-weight: 500;
        color: #0f172a;
        font-size: 13px;
    }
    .timesheet-table .day-cell {
        font-size: 11px;
        color: #94a3b8;
    }
    .timesheet-table .hours-input {
        width: 70px;
        padding: 6px 8px;
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        text-align: center;
        background: white;
        transition: border-color 0.2s;
    }
    .timesheet-table .hours-input:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .timesheet-table .hours-input:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .timesheet-table .type-select {
        padding: 6px 10px;
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        background: white;
        min-width: 140px;
        transition: border-color 0.2s;
    }
    .timesheet-table .type-select:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .timesheet-table .type-select:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
    }
    .timesheet-table .notes-input {
        width: 100%;
        padding: 6px 10px;
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        background: white;
        transition: border-color 0.2s;
    }
    .timesheet-table .notes-input:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .timesheet-table .notes-input:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
    }
    .btn-save {
        background: #4f46e5;
        color: white;
        padding: 10px 32px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-save:hover {
        background: #4338ca;
    }
    .btn-finalize {
        background: #22c55e;
        color: white;
        padding: 10px 32px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-finalize:hover {
        background: #16a34a;
    }
    .btn-lock {
        background: #ef4444;
        color: white;
        padding: 10px 32px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-lock:hover {
        background: #dc2626;
    }
    .btn-secondary {
        background: #e2e8f0;
        color: #334155;
        padding: 10px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-secondary:hover {
        background: #cbd5e1;
    }
    .toolbar-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .employee-selector {
        padding: 8px 14px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        min-width: 200px;
        transition: border-color 0.2s;
    }
    .employee-selector:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .employee-picker {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .employee-search {
        min-width: 220px;
    }
    .status-text {
        font-size: 13px;
        color: #64748b;
        padding: 6px 12px;
        background: #f1f5f9;
        border-radius: 6px;
        display: inline-block;
    }
    .weekend-row {
        background-color: #fafbfc;
    }
    .weekend-row .date-cell {
        color: #94a3b8;
    }
    .disabled-row td {
        opacity: 0.6;
    }
    .week-header {
        background: #f1f5f9;
        font-weight: 600;
        font-size: 13px;
        color: #334155;
        text-align: center;
        padding: 8px;
        border-bottom: 2px solid #e2e8f0;
    }
    .timesheet-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .week-column {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    .week-column .week-header {
        background: #f1f5f9;
        padding: 10px 12px;
        font-weight: 600;
        font-size: 13px;
        color: #334155;
        text-align: center;
        border-bottom: 2px solid #e2e8f0;
    }
    .week-column table {
        width: 100%;
        border-collapse: collapse;
    }
    .week-column table td {
        padding: 6px 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .week-column table tr:last-child td {
        border-bottom: none;
    }
    .week-column .hours-input {
        width: 60px;
        padding: 4px 6px;
    }
    .week-column .type-select {
        min-width: 120px;
        padding: 4px 8px;
        font-size: 12px;
    }
    .week-column .notes-input {
        padding: 4px 8px;
        font-size: 12px;
    }
    .week-column .date-cell {
        font-size: 13px;
    }
    .week-column .day-cell {
        font-size: 11px;
    }
    .week-total {
        background: #f8fafc;
        font-weight: 600;
        border-top: 2px solid #e2e8f0;
    }
    .week-total td {
        padding: 8px 12px;
    }
    .week-total .total-label {
        text-align: right;
        color: #64748b;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .week-total .total-value {
        text-align: center;
        font-size: 16px;
        color: #0f172a;
    }
    .fortnight-total {
        background: #eef2ff;
        border-top: 3px solid #6366f1 !important;
    }
    .fortnight-total .total-value {
        font-size: 18px;
        color: #4f46e5;
        font-weight: 700;
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

    /* ============ HOURS CALCULATOR (National employees) ============ */
    .hours-cell {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .calc-trigger {
        width: 26px;
        height: 26px;
        flex-shrink: 0;
        border: 1.5px solid #d1d5db;
        background: #f8fafc;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s, border-color 0.15s;
    }
    .calc-trigger:hover {
        background: #eef2ff;
        border-color: #6366f1;
    }
    .calc-trigger:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    .calc-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .calc-overlay.open {
        display: flex;
    }
    .calc-popover {
        background: white;
        border-radius: 12px;
        width: 320px;
        max-width: 90vw;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        overflow: hidden;
    }
    .calc-popover-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        color: white;
        padding: 14px 18px;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .calc-popover-header .calc-close {
        background: none;
        border: none;
        color: #cbd5e1;
        font-size: 18px;
        cursor: pointer;
        line-height: 1;
    }
    .calc-popover-header .calc-close:hover {
        color: white;
    }
    .calc-popover-body {
        padding: 16px 18px;
    }
    .calc-popover-hint {
        font-size: 11px;
        color: #64748b;
        font-style: italic;
        margin-bottom: 14px;
    }
    .calc-field {
        margin-bottom: 12px;
    }
    .calc-field label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #475569;
        margin-bottom: 4px;
    }
    .calc-field input {
        width: 100%;
        padding: 8px 10px;
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        text-align: center;
        box-sizing: border-box;
    }
    .calc-field input:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .calc-actions {
        display: flex;
        gap: 8px;
        margin-top: 4px;
    }
    .calc-actions button {
        flex: 1;
        padding: 9px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        border: none;
        cursor: pointer;
    }
    .calc-btn-calc {
        background: #1a1f36;
        color: white;
    }
    .calc-btn-calc:hover {
        background: #2d3555;
    }
    .calc-btn-insert {
        background: #22c55e;
        color: white;
    }
    .calc-btn-insert:hover {
        background: #16a34a;
    }
    .calc-btn-insert:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
    }
    .calc-result-box {
        margin-top: 14px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .calc-result-box .calc-result-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #166534;
        font-weight: 700;
    }
    .calc-result-box .calc-result-value {
        font-size: 26px;
        font-weight: 700;
        color: #15803d;
        margin-top: 2px;
    }
    .calc-error {
        margin-top: 10px;
        font-size: 12px;
        color: #b91c1c;
        text-align: center;
    }

    @media (max-width: 1024px) {
        .timesheet-wrapper {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 768px) {
        .timesheet-header { padding: 16px; }
        .timesheet-header .header-title { font-size: 16px; }
        .pay-period-header { padding: 12px 16px; }
        .pay-period-value { font-size: 16px; }
        .timesheet-container { padding: 16px; }
        .employee-selector { min-width: 140px; font-size: 12px; padding: 6px 10px; }
        .timesheet-table th, .timesheet-table td { padding: 4px 6px; font-size: 11px; }
        .timesheet-table .hours-input { width: 50px; font-size: 12px; padding: 4px; }
        .timesheet-table .type-select { min-width: 80px; font-size: 11px; padding: 3px 6px; }
        .timesheet-table .notes-input { font-size: 11px; padding: 3px 6px; }
        .btn-save, .btn-finalize, .btn-lock, .btn-secondary { padding: 8px 16px; font-size: 12px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="timesheet-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-clipboard-list text-indigo-300 mr-2"></i> Timesheet Entry
                    </div>
                    <div class="header-subtitle mt-1">
                        Enter and manage hourly/daily timesheet data for a pay period
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('dashboard') }}" class="btn-secondary" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="timesheet-container">

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

            <!-- ============ TIMESHEET HEADER ============ -->
            <div class="pay-period-header">
                <div class="flex flex-wrap items-center justify-between">
                    <div>
                        <div class="pay-period-label">Current Pay Period</div>
                        <div class="pay-period-value">{{ $fortnight }}</div>
                        <div class="pay-period-range">{{ $period['start']->format('d/m/y') }} → {{ $period['end']->format('d/m/y') }}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 mt-2 sm:mt-0">
                        <!-- Fortnight Selector -->
                        <select id="fortnight_selector" class="employee-selector" style="min-width:150px;">
                            @foreach($fortnights as $fn)
                                @php $p = $fortnightPeriods[$fn] ?? null; @endphp
                                @if($p)
                                    <option value="{{ $fn }}" {{ $fn == $fortnight ? 'selected' : '' }}>
                                        {{ $fn }} ({{ $p['start']->format('d/m/y') }} - {{ $p['end']->format('d/m/y') }})
                                    </option>
                                @endif
                            @endforeach
                        </select>

                        <select id="branch_selector" class="employee-selector" style="min-width:150px;">
                            <option value="">All Branches / Sites</option>
                            @foreach($branches as $branch)<option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>@endforeach
                        </select>

                        <!-- Employee Selector -->
                        <div class="employee-picker">
                            <input
                                id="employee_search"
                                type="search"
                                class="employee-selector employee-search"
                                placeholder="Search employee number or name"
                                aria-label="Search employees">
                            <select id="employee_selector" class="employee-selector" aria-label="Select employee">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->employee_number }} - {{ $emp->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Status Badge -->
                        @php
                            $statusColors = ['Draft' => 'status-draft', 'Final' => 'status-final', 'Locked' => 'status-locked'];
                            $statusLabels = ['Draft' => '📝 Draft', 'Final' => '✅ Final', 'Locked' => '🔒 Locked'];
                        @endphp
                        <span class="status-badge {{ $statusColors[$timesheetStatus] ?? 'status-draft' }}">
                            {{ $statusLabels[$timesheetStatus] ?? 'Draft' }}
                        </span>
                        
                        <!-- Entered By -->
                        <span class="status-text">Entered by: {{ Auth::user()->name }}</span>
                    </div>
                </div>
            </div>

            <!-- ============ EMPLOYEE SELECTED INFO ============ -->
            @if(request('employee_id'))
                <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex flex-wrap items-center justify-between">
                        <div>
                            <span class="font-medium text-gray-700">Editing record for:</span>
                            <span class="font-bold text-gray-900">{{ $selectedEmployee->full_name ?? '' }}</span>
                            <span class="text-sm text-gray-500 ml-2">({{ $selectedEmployee->employee_number ?? '' }})</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Status: </span>
                            <span class="status-badge status-draft">{{ $timesheetStatus ?? 'Draft' }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @if(($timesheetStatus ?? 'Draft') === 'Locked' && (auth()->user()->isSuperAdmin() || auth()->user()->can('unlock-attendance')))
                            <form method="POST" action="{{ route('attendance.summary.bulk-update') }}">
                                @csrf
                                <input type="hidden" name="fortnight" value="{{ $fortnight }}">
                                <input type="hidden" name="target_employee_id" value="{{ $selectedEmployee->id }}">
                                <input type="hidden" name="redirect_to" value="timesheet">
                                <button type="submit" name="action" value="unlock" class="btn-secondary"
                                    onclick="return confirm('Unlock this employee\'s timesheet for the selected fortnight?');">
                                    <i class="fas fa-unlock"></i> Unlock Timesheet
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('attendance.summary.bulk-update') }}">
                            @csrf
                            <input type="hidden" name="fortnight" value="{{ $fortnight }}">
                            <input type="hidden" name="target_employee_id" value="{{ $selectedEmployee->id }}">
                            <input type="hidden" name="redirect_to" value="timesheet">
                            <button type="submit" name="action" value="reset" class="btn-secondary" style="color: #b91c1c;"
                                onclick="return confirm('Reset all attendance hours for this employee in the selected fortnight? This cannot be undone.');">
                                <i class="fas fa-undo"></i> Reset Attendance
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ============ TIMESHEET TABLE - 2 WEEKS SPLIT ============ -->
                <form method="POST" action="{{ route('attendance.bulk.update') }}" id="timesheetForm">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                    <input type="hidden" name="fortnight" value="{{ $fortnight }}">

                    @if($selectedEmployee && $selectedEmployee->isExpatriate() && $timesheetStatus === 'Draft')
                        @php
                            $companyName = strtolower((string) optional($selectedEmployee->company)->name);
                            $isYellowjacket = str_contains($companyName, 'yellowjacket')
                                && (str_contains($companyName, 'port moresby') || str_contains($companyName, 'lae'));
                        @endphp
                        <div class="mb-5 rounded-lg border border-indigo-200 bg-indigo-50 p-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold text-indigo-900">Expatriate schedule generator</div>
                                <div class="text-sm text-indigo-700">84 hours: Mon–Fri 8 hrs, Sat 2 hrs. Sundays are set to 0.</div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="submit" name="action" value="generate_expatriate_schedule" class="btn-save" onclick="this.form.expatriate_schedule_hours.value = '84'; return confirm('Replace this fortnight with the 84-hour expatriate schedule?');">
                                    Generate 84 hrs
                                </button>
                                @if($isYellowjacket)
                                    <button type="submit" name="action" value="generate_expatriate_schedule" class="btn-save" onclick="this.form.expatriate_schedule_hours.value = '144'; return confirm('Replace this fortnight with the 144-hour Yellowjacket expatriate schedule?');">
                                        Generate 144 hrs
                                    </button>
                                @endif
                            </div>
                        </div>
                        <input type="hidden" name="expatriate_schedule_hours" value="84">
                    @endif

                    <div class="timesheet-wrapper">
                        @php
                            $nonPayTypes = ['Leave Without Pay', 'Absent'];
                            $isLocked = $timesheetStatus == 'Locked';
                        @endphp

                        <!-- Week 1: Days 0-6 -->
                        <div class="week-column">
                            <div class="week-header">Week 1</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width:30%;">DATE</th>
                                        <th style="width:20%;">HOURS</th>
                                        <th style="width:30%;">TYPE</th>
                                        <th style="width:20%;">NOTES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $week1Total = 0;
                                    @endphp
                                    @for($i = 0; $i < 7; $i++)
                                        @php
                                            $date = $period['start']->copy()->addDays($i);
                                            $dateKey = $date->format('Y-m-d');
                                            $isWeekend = $date->isWeekend();
                                            $log = $selectedDayLogs->get($dateKey);
                                            $hours = $log ? $log->hours_worked : '';
                                            $type = $log ? $log->attendance_type : 'Work';
                                            $notes = $log ? $log->notes : '';
                                            
                                            $isDisabledType = in_array($type, $nonPayTypes);
                                            $rowDisabled = $isLocked || $isDisabledType;
                                            
                                            if (!in_array($type, $nonPayTypes)) {
                                                $week1Total += $log ? $log->hours_worked : 0;
                                            }
                                        @endphp
                                        <tr class="{{ $isWeekend ? 'weekend-row' : '' }} {{ $rowDisabled ? 'disabled-row' : '' }}">
                                            <td>
                                                <div class="date-cell">{{ $date->format('d/m/y') }}</div>
                                                <div class="day-cell">
                                                    {{ $date->format('l') }}
                                                    @if(isset($holidayDates[$dateKey]) && $holidayDates[$dateKey])
                                                        <span class="badge bg-danger text-white" style="font-size: 9px; padding: 1px 8px; border-radius: 4px; margin-left: 4px;">Holiday</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="hours-cell">
                                                    <input type="number" 
                                                        name="attendance[{{ $dateKey }}][hours]" 
                                                        id="hours-input-{{ $dateKey }}"
                                                        value="{{ $hours }}"
                                                        class="hours-input"
                                                        step="any"
                                                        min="0"
                                                        max="24"
                                                        {{ $rowDisabled ? 'disabled' : '' }}>
                                                    @unless($selectedEmployee->isExpatriate())
                                                        <button type="button" class="calc-trigger" title="Calculate hours from clock in/out"
                                                            onclick="openHoursCalculator('{{ $dateKey }}')"
                                                            {{ $rowDisabled ? 'disabled' : '' }}>🧮</button>
                                                    @endunless
                                                </div>
                                                <input type="hidden" name="attendance[{{ $dateKey }}][date]" value="{{ $dateKey }}">
                                            </td>
                                            <td>
                                                <select name="attendance[{{ $dateKey }}][type]" 
                                                        class="type-select"
                                                        onchange="toggleHours(this, '{{ $dateKey }}')"
                                                        {{ $rowDisabled ? 'disabled' : '' }}>
                                                    <option value="Work" {{ $type == 'Work' ? 'selected' : '' }}>Work</option>
                                                    <option value="Sick Leave" {{ $type == 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                                                    <option value="Leave Without Pay" {{ $type == 'Leave Without Pay' ? 'selected' : '' }}>Leave Without Pay</option>
                                                    <option value="Absent" {{ $type == 'Absent' ? 'selected' : '' }}>Absent</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                    name="attendance[{{ $dateKey }}][notes]" 
                                                    value="{{ $notes }}"
                                                    class="notes-input"
                                                    placeholder="Notes..."
                                                    {{ $rowDisabled ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                    @endfor
                                    <tr class="week-total">
                                        <td class="total-label" colspan="1">WEEK 1 TOTAL</td>
                                        <td class="total-value">{{ number_format($week1Total, 1) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Week 2: Days 7-13 -->
                        <div class="week-column">
                            <div class="week-header">Week 2</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width:30%;">DATE</th>
                                        <th style="width:20%;">HOURS</th>
                                        <th style="width:30%;">TYPE</th>
                                        <th style="width:20%;">NOTES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($i = 7; $i < 14; $i++)
                                        @php
                                            $date = $period['start']->copy()->addDays($i);
                                            $dateKey = $date->format('Y-m-d');
                                            $isWeekend = $date->isWeekend();
                                            $log = $selectedDayLogs->get($dateKey);
                                            $hours = $log ? $log->hours_worked : '';
                                            $type = $log ? $log->attendance_type : 'Work';
                                            $notes = $log ? $log->notes : '';
                                            $isDisabledType = in_array($type, $nonPayTypes);
                                            $rowDisabled = $isLocked|| $isDisabledType;
                                        @endphp
                                        <tr class="{{ $isWeekend ? 'weekend-row' : '' }} {{ $rowDisabled ? 'disabled-row' : '' }}">
                                            <td>
                                                <div class="date-cell">{{ $date->format('d/m/y') }}</div>
                                                <div class="day-cell">
                                                    {{ $date->format('l') }}
                                                    @if(isset($holidayDates[$dateKey]) && $holidayDates[$dateKey])
                                                        <span class="badge bg-danger text-white" style="font-size: 9px; padding: 1px 8px; border-radius: 4px; margin-left: 4px;">Holiday</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="hours-cell">
                                                    <input type="number" 
                                                           name="attendance[{{ $dateKey }}][hours]" 
                                                           id="hours-input-{{ $dateKey }}"
                                                           value="{{ $hours }}"
                                                           class="hours-input"
                                                           step="any"
                                                           min="0"
                                                           max="24"
                                                           {{ $rowDisabled ? 'disabled' : '' }}>
                                                    @unless($selectedEmployee->isExpatriate())
                                                        <button type="button" class="calc-trigger" title="Calculate hours from clock in/out"
                                                            onclick="openHoursCalculator('{{ $dateKey }}')"
                                                            {{ $rowDisabled ? 'disabled' : '' }}>🧮</button>
                                                    @endunless
                                                </div>
                                                <input type="hidden" name="attendance[{{ $dateKey }}][date]" value="{{ $dateKey }}">
                                            </td>
                                            <td>
                                                <select name="attendance[{{ $dateKey }}][type]" 
                                                        class="type-select"
                                                        onchange="toggleHours(this, '{{ $dateKey }}')"
                                                        {{ $rowDisabled ? 'disabled' : '' }}>
                                                    <option value="Work" {{ $type == 'Work' ? 'selected' : '' }}>Work</option>
                                                    <option value="Sick Leave" {{ $type == 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                                                    <option value="Leave Without Pay" {{ $type == 'Leave Without Pay' ? 'selected' : '' }}>Leave Without Pay</option>
                                                    <option value="Absent" {{ $type == 'Absent' ? 'selected' : '' }}>Absent</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       name="attendance[{{ $dateKey }}][notes]" 
                                                       value="{{ $notes }}"
                                                       class="notes-input"
                                                       placeholder="Notes..."
                                                       {{ $rowDisabled ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                    @endfor
                                    <tr class="week-total">
                                        <td class="total-label" colspan="1">WEEK 2 TOTAL</td>
                                        <td class="total-value">
                                            @php
                                                $week2Total = 0;
                                                for($i = 7; $i < 14; $i++) {
                                                    $date = $period['start']->copy()->addDays($i);
                                                    $dateKey = $date->format('Y-m-d');
                                                    $log = $selectedDayLogs->get($dateKey);
                                                    $type = $log ? $log->attendance_type : 'Work';
                                                    if (!in_array($type, $nonPayTypes)) {
                                                        $week2Total += $log ? $log->hours_worked : 0;
                                                    }
                                                }
                                            @endphp
                                            {{ number_format($week2Total, 1) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                    <tr class="week-total fortnight-total">
                                        <td class="total-label" colspan="1">FORTNIGHT TOTAL</td>
                                        <td class="total-value">
                                            {{ number_format($week1Total + $week2Total, 1) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @unless($selectedEmployee->isExpatriate())
                        <!-- ============ HOURS CALCULATOR (National employees) ============ -->
                        <div class="calc-overlay" id="hoursCalcOverlay">
                            <div class="calc-popover">
                                <div class="calc-popover-header">
                                    <span>🧮 Hours Calculator</span>
                                    <button type="button" class="calc-close" onclick="closeHoursCalculator()">&times;</button>
                                </div>
                                <div class="calc-popover-body">
                                    <div class="calc-popover-hint">Enter time using a dot: 8.36 = 8:36 AM, 17.30 = 5:30 PM</div>

                                    <div class="calc-field">
                                        <label for="calcClockIn">Clock In</label>
                                        <input type="text" id="calcClockIn" inputmode="decimal" placeholder="8.00">
                                    </div>
                                    <div class="calc-field">
                                        <label for="calcClockOut">Clock Out</label>
                                        <input type="text" id="calcClockOut" inputmode="decimal" placeholder="17.00">
                                    </div>
                                    <div class="calc-field">
                                        <label for="calcBreak">Break (hours)</label>
                                        <input type="text" id="calcBreak" inputmode="decimal" placeholder="1">
                                    </div>

                                    <div class="calc-actions">
                                        <button type="button" class="calc-btn-calc" onclick="calculateHoursWorked()">Calculate</button>
                                        <button type="button" class="calc-btn-insert" id="calcInsertBtn" disabled onclick="insertCalculatedHours()">Insert</button>
                                    </div>

                                    <div id="calcErrorBox" class="calc-error" style="display:none;"></div>

                                    <div id="calcResultBox" class="calc-result-box" style="display:none;">
                                        <div class="calc-result-label">Decimal Hours Worked</div>
                                        <div class="calc-result-value" id="calcResultValue">0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endunless

                    <!-- ============ ACTION BUTTONS ============ -->
                    <div class="mt-6 flex flex-wrap items-center justify-between border-t border-gray-200 pt-6">
                        <div class="toolbar-actions">
                            <button type="button" class="btn-secondary" onclick="window.location.href='{{ route('attendance.index') }}'">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                        <div class="toolbar-actions">
                            @if($timesheetStatus == 'Locked')
                                <span class="text-sm text-red-600 font-bold">🔒 LOCKED - No edits allowed</span>
                            @elseif($timesheetStatus == 'Final')
                                <span class="text-sm text-amber-600 font-bold">⚠️ FINALIZED - You can edit hours</span>
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->can('save-attendance'))
                                <button type="submit" name="action" value="save" class="btn-save">
                                    💾 Save
                                </button>
                                @endif
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->can('lock-attendance'))
                                <button type="submit" name="action" value="lock" class="btn-lock"
                                    onclick="return confirm('🔒 Lock this timesheet?\n\nNo further edits or changes will be allowed.')">
                                    🔒 Lock
                                </button>
                                @endif
                            @else
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->can('save-attendance'))
                                <button type="submit" name="action" value="save" class="btn-save">
                                    💾 Save
                                </button>
                                @endif
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->can('lock-attendance'))
                                <button type="submit" name="action" value="lock" class="btn-lock"
                                    onclick="return confirm('🔒 Lock this timesheet?\n\nNo further edits or changes will be allowed.')">
                                    🔒 Lock
                                </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </form>
            @else
                <!-- ============ NO EMPLOYEE SELECTED ============ -->
                <div class="text-center py-12">
                    <div class="text-gray-300 text-5xl mb-4">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Select an Employee</h3>
                    <p class="text-gray-500 mt-2">Please select an employee from the dropdown above to view and edit their timesheet.</p>
                </div>
            @endif

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Employee selector - redirect when changed
        const employeeSelector = document.getElementById('employee_selector');
        if (employeeSelector) {
            employeeSelector.addEventListener('change', function() {
                const url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('employee_id', this.value);
                } else {
                    url.searchParams.delete('employee_id');
                }
                window.location.href = url.toString();
            });
        }

        // Filter the employee list by employee number or name without changing
        // the selected employee until the user chooses an option.
        const employeeSearch = document.getElementById('employee_search');
        if (employeeSearch && employeeSelector) {
            employeeSearch.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();

                Array.from(employeeSelector.options).forEach(function(option, index) {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = query !== '' && !option.text.toLowerCase().includes(query);
                });
            });

            employeeSearch.addEventListener('keydown', function(event) {
                if (event.key !== 'Enter') {
                    return;
                }

                const firstMatch = Array.from(employeeSelector.options)
                    .find(function(option, index) {
                        return index > 0 && !option.hidden;
                    });

                if (firstMatch) {
                    event.preventDefault();
                    employeeSelector.value = firstMatch.value;
                    employeeSelector.dispatchEvent(new Event('change'));
                }
            });
        }

        // Fortnight selector - redirect when changed
        const fortnightSelector = document.getElementById('fortnight_selector');
        if (fortnightSelector) {
            fortnightSelector.addEventListener('change', function() {
                const url = new URL(window.location.href);
                url.searchParams.set('fortnight', this.value);
                const employeeId = document.getElementById('employee_selector').value;
                if (employeeId) {
                    url.searchParams.set('employee_id', employeeId);
                }
                window.location.href = url.toString();
            });
        }

        const branchSelector = document.getElementById('branch_selector');
        if (branchSelector) {
            branchSelector.addEventListener('change', function() {
                const url = new URL(window.location.href);
                if (this.value) url.searchParams.set('branch_id', this.value);
                else url.searchParams.delete('branch_id');
                url.searchParams.delete('employee_id');
                window.location.href = url.toString();
            });
        }
    });

    // ============ HOURS CALCULATOR (National employees) ============
    let calcTargetDateKey = null;

    function openHoursCalculator(dateKey) {
        calcTargetDateKey = dateKey;
        document.getElementById('calcClockIn').value = '';
        document.getElementById('calcClockOut').value = '';
        document.getElementById('calcBreak').value = '1';
        document.getElementById('calcResultBox').style.display = 'none';
        document.getElementById('calcErrorBox').style.display = 'none';
        document.getElementById('calcInsertBtn').disabled = true;
        document.getElementById('hoursCalcOverlay').classList.add('open');
        document.getElementById('calcClockIn').focus();
    }

    function closeHoursCalculator() {
        document.getElementById('hoursCalcOverlay').classList.remove('open');
        calcTargetDateKey = null;
    }

    // Parses "dot" time notation, e.g. 8.36 -> 8:36 (8h 36m), 17.3 -> 17:30 (17h 30m)
    // Returns total minutes since midnight, or null if invalid.
    function parseDotTime(raw) {
        if (raw === null || raw === undefined) return null;
        const value = String(raw).trim();
        if (value === '') return null;
        if (!/^\d{1,2}(\.\d{1,2})?$/.test(value)) return null;

        const [hourStr, minuteStr = ''] = value.split('.');
        const hours = parseInt(hourStr, 10);
        // Pad a single digit (e.g. ".3") to represent 30 minutes, not 3.
        const minutes = parseInt(minuteStr.padEnd(2, '0'), 10);

        if (hours < 0 || hours > 24 || minutes > 59) return null;
        return (hours * 60) + minutes;
    }

    function calculateHoursWorked() {
        const errorBox = document.getElementById('calcErrorBox');
        const resultBox = document.getElementById('calcResultBox');
        const insertBtn = document.getElementById('calcInsertBtn');

        errorBox.style.display = 'none';
        resultBox.style.display = 'none';
        insertBtn.disabled = true;

        const inMinutes = parseDotTime(document.getElementById('calcClockIn').value);
        const outMinutes = parseDotTime(document.getElementById('calcClockOut').value);
        const breakHoursRaw = document.getElementById('calcBreak').value.trim();
        const breakHours = breakHoursRaw === '' ? 0 : parseFloat(breakHoursRaw);

        if (inMinutes === null || outMinutes === null) {
            errorBox.textContent = 'Enter valid clock in/out times, e.g. 8.36';
            errorBox.style.display = 'block';
            return;
        }
        if (isNaN(breakHours) || breakHours < 0) {
            errorBox.textContent = 'Enter a valid break value, e.g. 1 or 0.5';
            errorBox.style.display = 'block';
            return;
        }

        let workedMinutes = outMinutes - inMinutes;
        if (workedMinutes <= 0) {
            errorBox.textContent = 'Clock out must be after clock in';
            errorBox.style.display = 'block';
            return;
        }

        const netMinutes = workedMinutes - (breakHours * 60);
        if (netMinutes < 0) {
            errorBox.textContent = 'Break is longer than time worked';
            errorBox.style.display = 'block';
            return;
        }

        const decimalHours = Math.round((netMinutes / 60) * 100) / 100;
        document.getElementById('calcResultValue').textContent = decimalHours.toFixed(2);
        resultBox.style.display = 'block';
        insertBtn.disabled = false;
        insertBtn.dataset.value = decimalHours;
    }

    function insertCalculatedHours() {
        if (!calcTargetDateKey) return;
        const value = document.getElementById('calcInsertBtn').dataset.value;
        const hoursInput = document.getElementById('hours-input-' + calcTargetDateKey);
        if (hoursInput) {
            hoursInput.value = value;
            hoursInput.dispatchEvent(new Event('change'));
        }
        closeHoursCalculator();
    }

    // Close popover on background click or Escape key
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('hoursCalcOverlay');
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) closeHoursCalculator();
            });
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeHoursCalculator();
        });
    });

    // Toggle hours input based on type selection
    function toggleHours(selectElement, dateKey) {
        const row = selectElement.closest('tr');
        const hoursInput = row.querySelector('.hours-input');
        const selectedValue = selectElement.value;
        
        const nonPayTypes = ['Leave Without Pay', 'Absent'];
        
        if (nonPayTypes.includes(selectedValue)) {
            hoursInput.disabled = true;
            hoursInput.value = '';
            hoursInput.style.background = '#f1f5f9';
            hoursInput.style.color = '#94a3b8';
        } else {
            hoursInput.disabled = false;
            hoursInput.style.background = 'white';
            hoursInput.style.color = '#0f172a';
        }
    }
</script>
@endsection

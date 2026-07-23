@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Generate Hours | Timesheets
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / Generate Hours | Timesheets
        </div>
    </div>
@endsection

@section('content')
<style>
    /* ============================================
       HEADER STYLES
       ============================================ */
    .summary-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 12px;
        padding: 24px 28px;
        color: white;
        margin-bottom: 24px;
    }
    .summary-header .header-title {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .summary-header .header-subtitle {
        font-size: 14px;
        color: #a0aec0;
        margin-top: 4px;
    }
    .summary-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        padding: 24px;
        overflow: hidden;
    }
    
    /* ============================================
       FILTER ROW
       ============================================ */
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 16px;
    }
    .filter-row .filter-group {
        flex: 1;
        min-width: 200px;
    }
    .filter-row .filter-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .filter-row .filter-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        transition: border-color 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
    }
    .filter-row .filter-group select:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .filter-row .filter-group input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        transition: border-color 0.2s;
    }
    .filter-row .filter-group input:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .btn-primary {
        background: #4f46e5;
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-primary:hover {
        background: #4338ca;
    }
    .btn-secondary {
        background: #e2e8f0;
        color: #334155;
        padding: 10px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s;
    }
    .btn-secondary:hover {
        background: #cbd5e1;
    }
    .btn-save {
        background: #22c55e;
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-save:hover {
        background: #16a34a;
    }
    .btn-export {
        background: #0ea5e9;
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-export:hover {
        background: #0284c7;
    }
    
    /* ============================================
       TABLE WRAPPER
       ============================================ */
    .summary-table-wrap {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: white;
        position: relative;
    }
    
    /* ============================================
       TABLE - FULL LAYOUT
       ============================================ */
    .summary-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
        table-layout: fixed;
        min-width: 1400px;
    }
    .summary-table th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f1f5f9;
        color: #475569;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        padding: 8px 4px;
        border-bottom: 2px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        text-align: center;
        white-space: nowrap;
    }
    .summary-table th:last-child {
        border-right: none;
    }
    .summary-table td {
        border-bottom: 1px solid #f1f5f9;
        border-right: 1px solid #f1f5f9;
        padding: 4px 4px;
        vertical-align: middle;
        background: white;
        text-align: center;
        transition: background 0.15s;
    }
    .summary-table td:last-child {
        border-right: none;
    }
    .summary-table tbody tr:hover td {
        background: #fafbfc;
    }
    .summary-table tbody tr:hover td.employee-cell {
        background: #fafbfc !important;
    }
    .summary-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    /* Employee Cells */
    .employee-cell {
        padding: 6px 8px !important;
        background: white !important;
        border-right: 2px solid #e2e8f0 !important;
        z-index: 5;
        font-size: 11px;
        font-weight: 600;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .employee-cell.name {
        text-align: left;
        font-weight: 400;
        color: #334155;
    }
    th.employee-cell {
        background: #f1f5f9 !important;
        font-size: 9px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        z-index: 15 !important;
    }
    
    /* Summary Columns (REG, OT, Sun, Hol) */
    .summary-col {
        width: 75px;
        min-width: 75px;
        max-width: 75px;
        font-weight: 700;
        font-size: 12px;
        color: #0f172a;
        padding: 4px 4px !important;
        background: #fafbfc !important;
    }
    .summary-col.reg {
        color: #2563eb;
    }
    .summary-col.ot {
        color: #ea580c;
    }
    .summary-col.sun {
        color: #7c3aed;
    }
    .summary-col.hol {
        color: #dc2626;
    }
    th.summary-col {
        background: #f1f5f9 !important;
        font-size: 8px;
        font-weight: 700;
        color: #475569;
    }
    
    /* Day Cells */
    .day-cell {
        width: 55px;
        min-width: 55px;
        max-width: 55px;
        padding: 2px 2px !important;
        vertical-align: middle;
        text-align: center;
    }
    .day-cell .hours-input {
        width: 100%;
        padding: 4px 2px;
        border: 1.5px solid #e2e8f0;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        text-align: center;
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-variant-numeric: tabular-nums;
        height: 28px;
        display: block;
    }
    .day-cell .hours-input:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        z-index: 2;
        position: relative;
    }
    .day-cell .hours-input:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .day-cell .hours-input::placeholder {
        color: #cbd5e1;
        font-weight: 400;
        font-size: 9px;
    }
    
    /* ============================================
       SUNDAY STYLES - RED
       ============================================ */
    /* Sunday column - light red background */
    .day-cell.weekend-cell {
        background: #fef2f2 !important;
        border-color: #fecaca !important;
    }
    .day-cell.weekend-cell .hours-input {
        background: #fef2f2 !important;
        border-color: #fecaca !important;
    }
    .day-cell.weekend-cell .hours-input:focus {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
        background: #fff5f5 !important;
    }
    
    /* Sunday header - red text and background */
    th.weekend-header {
        background: #fef2f2 !important;
        border-bottom: 2px solid #fecaca !important;
    }
    th.weekend-header .day-number {
        color: #dc2626 !important;
        font-weight: 700;
    }
    th.weekend-header .day-name {
        color: #dc2626 !important;
        font-weight: 600;
    }
    
    /* Date Header */
    .date-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1px;
        line-height: 1.2;
    }
    .date-header .day-number {
        font-size: 10px;
        font-weight: 700;
        color: #0f172a;
    }
    .date-header .day-name {
        font-size: 7px;
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .date-header .holiday-label {
        display: inline-block;
        margin-top: 1px;
        padding: 1px 4px;
        border-radius: 3px;
        background: #fee2e2;
        color: #991b1b;
        font-size: 5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    /* Status Badge */
    .status-badge {
        display: inline-block;
        margin-left: 4px;
        padding: 1px 6px;
        border-radius: 999px;
        font-size: 7px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .status-badge.status-draft {
        background: #fef3c7;
        color: #92400e;
    }
    .status-badge.status-final {
        background: #dcfce7;
        color: #166534;
    }
    .status-badge.status-locked {
        background: #fee2e2;
        color: #991b1b;
    }
    
    /* ============================================
       TABLE ACTIONS BAR
       ============================================ */
    .table-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 2px solid #f1f5f9;
    }
    .table-actions-left {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }
    .table-actions-left .fortnight-label {
        font-weight: 700;
        color: #0f172a;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .table-actions-left .fortnight-range {
        font-size: 13px;
        color: #64748b;
    }
    .table-actions-left .employee-count {
        font-size: 12px;
        color: #94a3b8;
        background: #f1f5f9;
        padding: 3px 12px;
        border-radius: 999px;
    }
    .table-actions .btn-save {
        flex-shrink: 0;
    }
    .table-actions .btn-export {
        flex-shrink: 0;
    }
    
    /* ============================================
       ALERTS / EMPTY STATE
       ============================================ */
    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        border-radius: 8px;
        padding: 14px 18px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }
    .empty-state .icon {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 16px;
        display: block;
    }
    .empty-state .title {
        font-size: 18px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .empty-state .subtitle {
        font-size: 14px;
        color: #94a3b8;
    }
    
    /* Info Box */
    .info-box {
        margin-top: 12px;
        padding: 12px 16px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 13px;
        color: #64748b;
    }
    .info-box i {
        color: #4f46e5;
        font-size: 16px;
    }
    
    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 1400px) {
        .day-cell { width: 48px; min-width: 48px; max-width: 48px; }
        .day-cell .hours-input { font-size: 10px; height: 24px; padding: 2px 2px; }
        .summary-col { width: 65px; min-width: 65px; max-width: 65px; font-size: 11px; }
        .summary-table { min-width: 1200px; }
    }
    
    @media (max-width: 1200px) {
        .day-cell { width: 42px; min-width: 42px; max-width: 42px; }
        .day-cell .hours-input { font-size: 9px; height: 22px; padding: 2px 1px; }
        .summary-col { width: 55px; min-width: 55px; max-width: 55px; font-size: 10px; }
        .employee-cell { font-size: 10px; padding: 4px 6px !important; }
        .summary-table { min-width: 1100px; }
    }
    
    @media (max-width: 768px) {
        .summary-header { padding: 16px 20px; }
        .summary-header .header-title { font-size: 18px; }
        .summary-header .header-subtitle { font-size: 13px; }
        .summary-card { padding: 16px; border-radius: 10px; }
        .filter-row .filter-group { min-width: 100%; flex: 1 1 100%; }
        .filter-row .btn-primary { width: 100%; justify-content: center; }
        .table-actions { flex-direction: column; align-items: stretch; }
        .table-actions-left { justify-content: center; flex-wrap: wrap; }
        .table-actions .btn-save { width: 100%; justify-content: center; }
        .table-actions .btn-export { width: 100%; justify-content: center; }
        .summary-table-wrap { margin: 0 -16px; border-radius: 0; border-left: none; border-right: none; }
        .summary-table { min-width: 900px; font-size: 10px; }
        .day-cell { width: 35px; min-width: 35px; max-width: 35px; }
        .day-cell .hours-input { font-size: 8px; height: 18px; padding: 1px 1px; border-width: 1px; }
        .summary-col { width: 45px; min-width: 45px; max-width: 45px; font-size: 9px; }
        .employee-cell { font-size: 9px; padding: 3px 4px !important; }
        .date-header .day-number { font-size: 8px; }
        .date-header .day-name { font-size: 6px; }
        .table-actions-left .fortnight-label { font-size: 14px; }
        .table-actions-left .fortnight-range { font-size: 12px; }
        .info-box { font-size: 11px; padding: 10px 14px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full px-4 sm:px-6 lg:px-8">
        <!-- HEADER -->
        <div class="summary-header">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="header-title">
                        <i class="fas fa-table text-indigo-300 mr-2"></i> Generate Hours | Timesheets
                    </div>
                    <div class="header-subtitle">
                        View and edit attendance hours for all employees
                    </div>
                </div>
                <a href="{{ route('attendance.index') }}" class="btn-secondary" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.1);">
                    <i class="fas fa-clipboard-list mr-1"></i> Timesheet Entry
                </a>
            </div>
        </div>

        <!-- ALERTS -->
        @if(session('success'))
            <div class="alert-success">
                <i class="fas fa-check-circle text-green-600"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- FILTERS -->
        <div class="summary-card mb-5">
            <form method="GET" action="{{ route('attendance.summary') }}" class="filter-row">
                <div class="filter-group">
                    <label for="fortnight">
                        <i class="fas fa-calendar-alt mr-1"></i> Select Fortnight
                    </label>
                    <select name="fortnight" id="fortnight">
                        @foreach($fortnights as $fn)
                            @php $p = $fortnightPeriods[$fn] ?? null; @endphp
                            <option value="{{ $fn }}" {{ $fn == $fortnight ? 'selected' : '' }}>
                                {{ $fn }} — 
                                @if($p)
                                    {{ $p['start']->format('M d, Y') }} to {{ $p['end']->format('M d, Y') }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="search"><i class="fas fa-search mr-1"></i> Search</label>
                    <input type="text" name="search" id="search" 
                           placeholder="Search employees..." 
                           value="{{ request('search') }}">
                </div>
                <input type="hidden" name="generated" value="1">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-sync-alt mr-1"></i> Generate
                </button>
            </form>
        </div>

        <!-- MAIN CONTENT -->
        @if($generated)
            <form method="POST" action="{{ route('attendance.summary.bulk-update') }}">
                @csrf
                <input type="hidden" name="fortnight" value="{{ $fortnight }}">

                <div class="summary-card">
                    <!-- Actions Bar -->
                    <div class="table-actions">
                        <div class="table-actions-left">
                            <span class="fortnight-label">
                                <i class="fas fa-calendar-check text-indigo-600"></i>
                                {{ $fortnight }}
                            </span>
                            <span class="fortnight-range">
                                {{ $period['start']->format('M d, Y') }} → {{ $period['end']->format('M d, Y') }}
                            </span>
                            <span class="employee-count">
                                <i class="fas fa-users mr-1"></i> {{ $employees->count() }} employee(s)
                            </span>
                        </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <button type="button" class="btn-export" onclick="exportTable()">
                                <i class="fas fa-file-excel"></i> Export
                            </button>
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    @if($employees->count() > 0)
                        <!-- TABLE -->
                        <div class="summary-table-wrap">
                            <table class="summary-table" id="attendanceTable">
                                <thead>
                                    <tr>
                                        <th class="employee-cell" style="width:80px;">EMP. NO.</th>
                                        <th class="employee-cell name" style="width:140px; text-align:left;">EMPLOYEE NAME</th>
                                        <th class="summary-col reg">REG</th>
                                        <th class="summary-col ot">OT Hrs.(1.5)</th>
                                        <th class="summary-col sun">Sun OT.(2.0)</th>
                                        <th class="summary-col hol">HOL</th>
                                        @for($i = 0; $i < 14; $i++)
                                            @php
                                                $date = $period['start']->copy()->addDays($i);
                                                $dateKey = $date->format('Y-m-d');
                                                $isWeekend = $date->isWeekend();
                                            @endphp
                                            <th class="{{ $isWeekend ? 'weekend-header' : '' }}" style="width:55px; min-width:55px; max-width:55px;">
                                                <div class="date-header">
                                                    <span class="day-number">{{ $date->format('d') }}</span>
                                                    <span class="day-name">{{ $date->format('D') }}</span>
                                                    @if(isset($holidayDates[$dateKey]))
                                                        <span class="holiday-label">🎉</span>
                                                    @endif
                                                </div>
                                            </th>
                                        @endfor
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $employee)
                                        @php
                                            $employeeLogs = $attendanceLogs->get($employee->id, collect());
                                            $summary = $summaries->get($employee->id);
                                            $status = $timesheetStatuses->get($employee->id, 'Draft');
                                            $isLocked = $status === 'Locked';
                                            $statusClass = [
                                                'Draft' => 'status-draft',
                                                'Final' => 'status-final',
                                                'Locked' => 'status-locked',
                                            ][$status] ?? 'status-draft';
                                        @endphp
                                        <tr>
                                            <!-- Employee Number -->
                                            <td class="employee-cell">
                                                {{ $employee->employee_number }}
                                                <span class="status-badge {{ $statusClass }}">
                                                    @if($status === 'Locked') 🔒 @endif
                                                    {{ $status }}
                                                </span>
                                            </td>
                                            
                                            <!-- Employee Name -->
                                            <td class="employee-cell name" title="{{ $employee->full_name }}">
                                                {{ $employee->full_name }}
                                            </td>

                                            <!-- Summary Columns (REG, OT, Sun, Hol) -->
                                            <td class="summary-col reg">{{ number_format($summary->regular_hours ?? 0, 2) }}</td>
                                            <td class="summary-col ot">{{ number_format($summary->overtime_hours ?? 0, 2) }}</td>
                                            <td class="summary-col sun">{{ number_format($summary->sunday_hours ?? 0, 2) }}</td>
                                            <td class="summary-col hol">{{ number_format($summary->holiday_hours ?? 0, 2) }}</td>

                                            <!-- Day Cells -->
                                            @for($i = 0; $i < 14; $i++)
                                                @php
                                                    $date = $period['start']->copy()->addDays($i);
                                                    $dateKey = $date->format('Y-m-d');
                                                    $log = $employeeLogs->get($dateKey);
                                                    $hours = $log ? $log->hours_worked : '';
                                                    $isWeekend = $date->isWeekend();
                                                @endphp
                                                <td class="day-cell {{ $isWeekend ? 'weekend-cell' : '' }}">
                                                    <input type="number"
                                                        name="attendance[{{ $employee->id }}][{{ $dateKey }}][hours]"
                                                        value="{{ $hours }}"
                                                        class="hours-input"
                                                        step="any"
                                                        min="0"
                                                        max="24"
                                                        placeholder=""
                                                        title="Enter hours for {{ $date->format('M d, Y') }}"
                                                        {{ $isLocked ? 'disabled' : '' }}>
                                                    <input type="hidden" 
                                                        name="attendance[{{ $employee->id }}][{{ $dateKey }}][type]" 
                                                        value="{{ $log ? $log->attendance_type : 'Work' }}">
                                                </td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Info Box -->
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <span>Edit hours directly in the grid. Locked timesheets are read-only.</span>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="empty-state">
                            <span class="icon">👥</span>
                            <div class="title">No Active Employees</div>
                            <div class="subtitle">No active employees found for the selected company and employee type.</div>
                        </div>
                    @endif
                </div>
            </form>
        @else
            <!-- Generate Prompt -->
            <div class="summary-card">
                <div class="empty-state">
                    <span class="icon">📊</span>
                    <div class="title">Ready to Generate</div>
                    <div class="subtitle">Select a fortnight above and click <strong>"Generate"</strong> to load the attendance summary grid.</div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function exportTable() {
        var table = document.getElementById('attendanceTable');
        var rows = table.querySelectorAll('tr');
        var csv = [];
        
        // Get headers
        var headers = [];
        var headerRow = rows[0];
        var headerCells = headerRow.querySelectorAll('th');
        headerCells.forEach(function(cell) {
            var text = cell.textContent.trim();
            // Clean up header text
            text = text.replace(/[🎉]/g, '').trim();
            headers.push(text);
        });
        csv.push(headers.join(','));
        
        // Get data rows
        for (var i = 1; i < rows.length; i++) {
            var row = rows[i];
            var cells = row.querySelectorAll('td');
            var rowData = [];
            cells.forEach(function(cell) {
                var input = cell.querySelector('input.hours-input');
                if (input) {
                    rowData.push(input.value || '0');
                } else {
                    var text = cell.textContent.trim();
                    rowData.push(text);
                }
            });
            csv.push(rowData.join(','));
        }
        
        // Download
        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'attendance_summary_{{ $fortnight }}.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Auto-submit on fortnight change
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('fortnight')?.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
</script>
@endsection
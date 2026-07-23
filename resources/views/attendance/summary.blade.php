@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Attendance Summary
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / Attendance Summary
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
    
    /* ============================================
       TABLE WRAPPER
       ============================================ */
    .summary-table-wrap {
        overflow: visible;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: white;
        position: relative;
    }
    
    /* ============================================
       TABLE - COMPACT LAYOUT
       ============================================ */
    .summary-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
        table-layout: fixed;
    }
    .summary-table th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8fafc;
        color: #475569;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        padding: 10px 4px;
        border-bottom: 2px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        text-align: center;
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
    
    /* Employee Cell - Sticky Left */
    .employee-cell {
        width: 160px;
        min-width: 160px;
        max-width: 160px;
        position: sticky;
        left: 0;
        background: white !important;
        border-right: 2px solid #e2e8f0 !important;
        box-shadow: 4px 0 12px rgba(0, 0, 0, 0.04);
        z-index: 5;
        padding: 8px 10px !important;
    }
    th.employee-cell {
        z-index: 15 !important;
        background: #f8fafc !important;
        box-shadow: 4px 0 12px rgba(0, 0, 0, 0.04);
        padding: 10px 10px !important;
        width: 160px;
        min-width: 160px;
        max-width: 160px;
    }
    .employee-cell .employee-name {
        font-weight: 700;
        color: #0f172a;
        font-size: 12px;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .employee-cell .employee-number {
        color: #94a3b8;
        font-size: 10px;
        margin-top: 1px;
        white-space: nowrap;
    }
    .employee-cell .status-badge {
        display: inline-block;
        margin-top: 3px;
        padding: 1px 8px;
        border-radius: 999px;
        font-size: 8px;
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
    
    /* Date Header Cells - Compact */
    .date-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1px;
        line-height: 1.2;
    }
    .date-header .day-number {
        font-size: 11px;
        font-weight: 700;
        color: #0f172a;
    }
    .date-header .day-name {
        font-size: 8px;
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
        font-size: 6px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    th.weekend-header .day-number {
        color: #94a3b8 !important;
    }
    th.weekend-header .day-name {
        color: #cbd5e1 !important;
    }
    
    /* Day Cells - Hours + Dropdown stacked vertically */
    .day-cell {
        width: 70px;
        min-width: 70px;
        max-width: 70px;
        padding: 3px 4px !important;
        vertical-align: middle;
        text-align: center;
    }
    .day-cell.weekend-cell {
        background: #fafbfc !important;
    }
    
    /* Hours Input - Top */
    .day-cell .hours-input {
        width: 100%;
        padding: 4px 2px;
        border: 1.5px solid #e2e8f0;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-variant-numeric: tabular-nums;
        height: 28px;
        display: block;
        margin-bottom: 2px;
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
        font-size: 10px;
    }
    
    /* Type Dropdown - Bottom with proper z-index */
    .day-cell .type-select-wrap {
        position: relative;
        display: block;
        width: 100%;
    }
    .day-cell .type-select {
        width: 100%;
        padding: 3px 16px 3px 4px;
        border: 1.5px solid #e2e8f0;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 500;
        background: white;
        transition: border-color 0.2s, box-shadow 0.2s;
        height: 22px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3E%3Cpath fill='%2394a3b8' d='M4 6L1 3h6z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 3px center;
        cursor: pointer;
        display: block;
    }
    .day-cell .type-select:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        z-index: 999;
        position: relative;
    }
    .day-cell .type-select:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
        opacity: 0.7;
    }
    .day-cell .type-select option {
        font-size: 11px;
        padding: 4px 8px;
    }
    
    /* Total Columns - Compact */
    .total-cell {
        width: 42px;
        min-width: 42px;
        max-width: 42px;
        text-align: center;
        font-weight: 700;
        color: #0f172a;
        font-size: 12px;
        font-variant-numeric: tabular-nums;
        padding: 4px 3px !important;
        background: #fafbfc !important;
    }
    .total-cell.employee-total {
        background: #eef2ff !important;
        color: #4f46e5;
        font-size: 13px;
        border-left: 2px solid #e2e8f0;
    }
    .summary-table th.total-header {
        background: #f1f5f9;
        font-size: 8px;
        width: 40px;
        min-width: 40px;
        max-width: 40px;
        padding: 8px 3px;
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
    @media (max-width: 1200px) {
        .summary-table {
            font-size: 11px;
        }
        .employee-cell {
            width: 130px;
            min-width: 130px;
            max-width: 130px;
            padding: 6px 8px !important;
        }
        th.employee-cell {
            width: 130px;
            min-width: 130px;
            max-width: 130px;
        }
        .day-cell {
            width: 60px;
            min-width: 60px;
            max-width: 60px;
            padding: 2px 3px !important;
        }
        .day-cell .hours-input {
            font-size: 10px;
            height: 24px;
            padding: 2px 2px;
        }
        .day-cell .type-select {
            font-size: 8px;
            height: 20px;
            padding: 2px 14px 2px 3px;
        }
        .total-cell {
            width: 35px;
            min-width: 35px;
            max-width: 35px;
            font-size: 10px;
            padding: 3px 2px !important;
        }
        .total-cell.employee-total {
            font-size: 11px;
        }
        .summary-table th.total-header {
            width: 35px;
            min-width: 35px;
            max-width: 35px;
            font-size: 7px;
            padding: 6px 2px;
        }
        .date-header .day-number {
            font-size: 10px;
        }
        .date-header .day-name {
            font-size: 7px;
        }
        .employee-cell .employee-name {
            font-size: 11px;
        }
        .employee-cell .employee-number {
            font-size: 9px;
        }
    }
    
    @media (max-width: 768px) {
        .summary-header {
            padding: 16px 20px;
        }
        .summary-header .header-title {
            font-size: 18px;
        }
        .summary-header .header-subtitle {
            font-size: 13px;
        }
        .summary-card {
            padding: 16px;
            border-radius: 10px;
        }
        .filter-row .filter-group {
            min-width: 100%;
            flex: 1 1 100%;
        }
        .filter-row .btn-primary {
            width: 100%;
            justify-content: center;
        }
        .table-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .table-actions-left {
            justify-content: center;
            flex-wrap: wrap;
        }
        .table-actions .btn-save {
            width: 100%;
            justify-content: center;
        }
        .summary-table-wrap {
            overflow-x: auto;
            margin: 0 -16px;
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
        .summary-table {
            min-width: 900px;
            font-size: 10px;
        }
        .employee-cell {
            width: 110px;
            min-width: 110px;
            max-width: 110px;
            padding: 4px 6px !important;
        }
        th.employee-cell {
            width: 110px;
            min-width: 110px;
            max-width: 110px;
        }
        .employee-cell .employee-name {
            font-size: 10px;
        }
        .employee-cell .employee-number {
            font-size: 8px;
        }
        .employee-cell .status-badge {
            font-size: 7px;
            padding: 1px 5px;
        }
        .day-cell {
            width: 55px;
            min-width: 55px;
            max-width: 55px;
            padding: 2px 2px !important;
        }
        .day-cell .hours-input {
            font-size: 9px;
            height: 22px;
            padding: 2px 1px;
        }
        .day-cell .type-select {
            font-size: 7px;
            height: 18px;
            padding: 1px 12px 1px 2px;
        }
        .total-cell {
            width: 30px;
            min-width: 30px;
            max-width: 30px;
            font-size: 9px;
            padding: 2px 2px !important;
        }
        .total-cell.employee-total {
            font-size: 10px;
        }
        .summary-table th.total-header {
            width: 30px;
            min-width: 30px;
            max-width: 30px;
            font-size: 6px;
            padding: 4px 2px;
        }
        .date-header .day-number {
            font-size: 9px;
        }
        .date-header .day-name {
            font-size: 6px;
        }
        .date-header .holiday-label {
            font-size: 5px;
            padding: 1px 3px;
        }
        .table-actions-left .fortnight-label {
            font-size: 14px;
        }
        .table-actions-left .fortnight-range {
            font-size: 12px;
        }
        .info-box {
            font-size: 11px;
            padding: 10px 14px;
        }
    }
    
    @media (max-width: 480px) {
        .summary-table {
            min-width: 700px;
            font-size: 9px;
        }
        .employee-cell {
            width: 90px;
            min-width: 90px;
            max-width: 90px;
            padding: 3px 4px !important;
        }
        th.employee-cell {
            width: 90px;
            min-width: 90px;
            max-width: 90px;
        }
        .employee-cell .employee-name {
            font-size: 9px;
        }
        .employee-cell .employee-number {
            font-size: 7px;
        }
        .employee-cell .status-badge {
            font-size: 6px;
            padding: 1px 4px;
        }
        .day-cell {
            width: 45px;
            min-width: 45px;
            max-width: 45px;
            padding: 1px 2px !important;
        }
        .day-cell .hours-input {
            font-size: 8px;
            height: 18px;
            padding: 1px 1px;
            border-width: 1px;
        }
        .day-cell .type-select {
            font-size: 6px;
            height: 16px;
            padding: 1px 10px 1px 2px;
            border-width: 1px;
        }
        .total-cell {
            width: 25px;
            min-width: 25px;
            max-width: 25px;
            font-size: 8px;
            padding: 2px 1px !important;
        }
        .total-cell.employee-total {
            font-size: 9px;
        }
        .summary-table th.total-header {
            width: 25px;
            min-width: 25px;
            max-width: 25px;
            font-size: 5px;
            padding: 3px 1px;
        }
        .date-header .day-number {
            font-size: 8px;
        }
        .date-header .day-name {
            font-size: 5px;
        }
    }
</style>

<div class="py-6">
    <div class="max-w-full px-4 sm:px-6 lg:px-8">
        <!-- HEADER -->
        <div class="summary-header">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="header-title">
                        <i class="fas fa-table text-indigo-300 mr-2"></i> Attendance Summary
                    </div>
                    <div class="header-subtitle">
                        View and edit attendance hours & types for all employees in a compact grid
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
                        <button type="submit" class="btn-save">
                            <i class="fas fa-save"></i> Save All
                        </button>
                    </div>

                    @if($employees->count() > 0)
                        <!-- TABLE -->
                        <div class="summary-table-wrap">
                            <table class="summary-table">
                                <thead>
                                    <tr>
                                        <th class="employee-cell">
                                            <span>Employee</span>
                                        </th>
                                        @for($i = 0; $i < 14; $i++)
                                            @php
                                                $date = $period['start']->copy()->addDays($i);
                                                $dateKey = $date->format('Y-m-d');
                                                $isWeekend = $date->isWeekend();
                                            @endphp
                                            <th class="{{ $isWeekend ? 'weekend-header' : '' }}">
                                                <div class="date-header">
                                                    <span class="day-number">{{ $date->format('d M') }}</span>
                                                    <span class="day-name">{{ $date->format('D') }}</span>
                                                    @if(isset($holidayDates[$dateKey]))
                                                        <span class="holiday-label">🎉</span>
                                                    @endif
                                                </div>
                                            </th>
                                        @endfor
                                        <th class="total-header">Total</th>
                                        <th class="total-header">Reg</th>
                                        <th class="total-header">OT</th>
                                        <th class="total-header">Sun</th>
                                        <th class="total-header">Hol</th>
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
                                            $rowTotal = 0;
                                        @endphp
                                        <tr>
                                            <!-- Employee Cell -->
                                            <td class="employee-cell">
                                                <div class="employee-name" title="{{ $employee->full_name }}">{{ $employee->full_name }}</div>
                                                <div class="employee-number">#{{ $employee->employee_number }}</div>
                                                <span class="status-badge {{ $statusClass }}">
                                                    @if($status === 'Locked') 🔒 @endif
                                                    {{ $status }}
                                                </span>
                                            </td>

                                            <!-- Day Cells -->
                                            @for($i = 0; $i < 14; $i++)
                                                @php
                                                    $date = $period['start']->copy()->addDays($i);
                                                    $dateKey = $date->format('Y-m-d');
                                                    $log = $employeeLogs->get($dateKey);
                                                    $type = $log ? $log->attendance_type : 'Work';
                                                    $hours = $log ? $log->hours_worked : '';
                                                    $isWeekend = $date->isWeekend();
                                                    if (!in_array($type, ['Annual Leave', 'Leave Without Pay', 'Absent'], true)) {
                                                        $rowTotal += (float) ($log->hours_worked ?? 0);
                                                    }
                                                @endphp
                                                <td class="day-cell {{ $isWeekend ? 'weekend-cell' : '' }}">
                                                    <input type="number"
                                                        name="attendance[{{ $employee->id }}][{{ $dateKey }}][hours]"
                                                        value="{{ $hours }}"
                                                        class="hours-input"
                                                        step="any"
                                                        min="0"
                                                        max="24"
                                                        placeholder="0"
                                                        title="Enter hours for {{ $date->format('M d, Y') }}"
                                                        {{ $isLocked ? 'disabled' : '' }}>
                                                    
                                                    <div class="type-select-wrap">
                                                        <select name="attendance[{{ $employee->id }}][{{ $dateKey }}][type]" 
                                                                class="type-select" 
                                                                {{ $isLocked ? 'disabled' : '' }}
                                                                title="Select attendance type for {{ $date->format('M d, Y') }}">
                                                            <option value="Work" {{ $type == 'Work' ? 'selected' : '' }}>Work</option>
                                                            <option value="Sick Leave" {{ $type == 'Sick Leave' ? 'selected' : '' }}>Sick</option>
                                                            <option value="Annual Leave" {{ $type == 'Annual Leave' ? 'selected' : '' }}>AL</option>
                                                            <option value="Leave Without Pay" {{ $type == 'Leave Without Pay' ? 'selected' : '' }}>LWP</option>
                                                            <option value="Absent" {{ $type == 'Absent' ? 'selected' : '' }}>Absent</option>
                                                        </select>
                                                    </div>
                                                </td>
                                            @endfor

                                            <!-- Totals -->
                                            <td class="total-cell employee-total">{{ number_format($rowTotal, 1) }}</td>
                                            <td class="total-cell">{{ number_format($summary->regular_hours ?? 0, 1) }}</td>
                                            <td class="total-cell">{{ number_format($summary->overtime_hours ?? 0, 1) }}</td>
                                            <td class="total-cell">{{ number_format($summary->sunday_hours ?? 0, 1) }}</td>
                                            <td class="total-cell">{{ number_format($summary->holiday_hours ?? 0, 1) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Info Box -->
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <span>Edit hours and attendance type directly in the grid. Locked timesheets are read-only.</span>
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
@endsection
@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Holidays
        </h2>
        <div class="text-sm text-gray-500">
            Management / Holidays
        </div>
    </div>
@endsection

@section('content')
<style>
    .holiday-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .holiday-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .holiday-header .header-subtitle {
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
    
    .btn-add {
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
    .btn-add:hover {
        background: #4338ca;
        color: white;
    }
    .btn-action {
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-action.toggle-active {
        background: #fef3c7;
        color: #92400e;
    }
    .btn-action.toggle-active:hover {
        background: #fde68a;
    }
    .btn-action.toggle-inactive {
        background: #dcfce7;
        color: #166534;
    }
    .btn-action.toggle-inactive:hover {
        background: #bbf7d0;
    }
    .btn-action.edit {
        background: #dbeafe;
        color: #1e40af;
    }
    .btn-action.edit:hover {
        background: #bfdbfe;
    }
    .btn-action.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.delete:hover {
        background: #fecaca;
    }
    
    .badge-status {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.active {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .table-holidays {
        font-size: 13px;
    }
    .table-holidays thead th {
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
    .table-holidays tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-holidays tbody tr:hover {
        background: #f8fafc;
    }
    .table-holidays .holiday-name {
        font-weight: 600;
        color: #0f172a;
    }
    .table-holidays .holiday-date {
        color: #0f172a;
        font-size: 13px;
    }
    .table-holidays .holiday-desc {
        color: #94a3b8;
        font-size: 12px;
    }
    .table-holidays .recurring-yes {
        color: #16a34a;
        font-weight: 600;
    }
    .table-holidays .recurring-no {
        color: #94a3b8;
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
    
    .upcoming-section {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 16px 20px;
        margin-top: 20px;
    }
    .upcoming-section .title {
        font-weight: 600;
        color: #1e40af;
        margin-bottom: 8px;
        font-size: 14px;
    }
    .upcoming-tag {
        display: inline-block;
        background: #dbeafe;
        color: #1e40af;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        margin: 2px 4px 2px 0;
    }
    
    @media (max-width: 768px) {
        .holiday-header { padding: 16px; }
        .holiday-header .header-title { font-size: 16px; }
        .table-holidays { font-size: 11px; }
        .table-holidays thead th, .table-holidays tbody td { padding: 6px 8px; }
        .stat-box .stat-value { font-size: 16px; }
        .action-buttons { gap: 2px; }
        .btn-action { font-size: 10px; padding: 2px 8px; }
    }
</style>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="holiday-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-calendar-day text-indigo-300 mr-2"></i> Holidays
                    </div>
                    <div class="header-subtitle mt-1">
                        Manage public holidays and special days
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('holidays.create') }}" class="btn-add">
                        <i class="fas fa-plus"></i> Add Holiday
                    </a>
                </div>
            </div>
        </div>

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

        <!-- Statistics -->
        @php
            $totalHolidays = $holidays->count();
            $activeHolidays = $holidays->where('is_active', true)->count();
            $upcomingCount = $holidays->where('is_active', true)->where('date', '>=', now())->count();
            $recurringCount = $holidays->where('is_recurring', true)->count();
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div class="stat-box">
                <div class="stat-value blue">{{ $totalHolidays }}</div>
                <div class="stat-label">Total Holidays</div>
            </div>
            <div class="stat-box">
                <div class="stat-value green">{{ $activeHolidays }}</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-box">
                <div class="stat-value purple">{{ $upcomingCount }}</div>
                <div class="stat-label">Upcoming</div>
            </div>
            <div class="stat-box">
                <div class="stat-value orange">{{ $recurringCount }}</div>
                <div class="stat-label">Recurring</div>
            </div>
        </div>

        <!-- Holiday Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-holidays w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Name</th>
                            <th class="text-left">Date</th>
                            <th class="text-left">Description</th>
                            <th class="text-center">Recurring</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $holiday)
                        <tr>
                            <td class="holiday-name">{{ $holiday->name }}</td>
                            <td class="holiday-date">{{ $holiday->date->format('M d, Y') }}</td>
                            <td class="holiday-desc">{{ $holiday->description ?? '-' }}</td>
                            <td class="text-center">
                                @if($holiday->is_recurring)
                                    <span class="recurring-yes">🔄 Yes</span>
                                @else
                                    <span class="recurring-no">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge-status {{ $holiday->is_active ? 'active' : 'inactive' }}">
                                    {{ $holiday->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" action="{{ route('holidays.toggle', $holiday) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-action {{ $holiday->is_active ? 'toggle-active' : 'toggle-inactive' }}">
                                            {{ $holiday->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('holidays.edit', $holiday) }}" class="btn-action edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action delete" onclick="return confirm('Delete this holiday?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <h3>No Holidays Found</h3>
                                    <p>Add your first holiday using the "Add Holiday" button above.</p>
                                    <a href="{{ route('holidays.create') }}" class="btn-add" style="display: inline-block; margin-top: 8px;">
                                        <i class="fas fa-plus"></i> Add Holiday
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upcoming Holidays -->
        @php
            $upcomingHolidays = $holidays->where('is_active', true)
                ->where('date', '>=', now())
                ->sortBy('date')
                ->take(5);
        @endphp

        @if($upcomingHolidays->count() > 0)
        <div class="upcoming-section">
            <div class="title">
                <i class="fas fa-calendar-alt mr-2"></i> Upcoming Holidays
            </div>
            <div>
                @foreach($upcomingHolidays as $holiday)
                    <span class="upcoming-tag">
                        {{ $holiday->date->format('M d') }} - {{ $holiday->name }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
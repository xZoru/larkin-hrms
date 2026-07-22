@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Employees
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / Employees
        </div>
    </div>
@endsection

@section('content')
<style>
    .employee-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .employee-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .employee-header .header-subtitle {
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
    
    .filter-bar {
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .filter-bar .filter-input {
        padding: 6px 12px;
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        background: white;
        transition: border-color 0.2s;
    }
    .filter-bar .filter-input:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .filter-bar .filter-select {
        padding: 6px 12px;
        border: 1.5px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        background: white;
        transition: border-color 0.2s;
    }
    .filter-bar .filter-select:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .btn-filter {
        background: #4f46e5;
        color: white;
        padding: 6px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-filter:hover {
        background: #4338ca;
    }
    .btn-clear {
        background: #e2e8f0;
        color: #475569;
        padding: 6px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-clear:hover {
        background: #cbd5e1;
    }
    .btn-add {
        background: #22c55e;
        color: white;
        padding: 6px 16px;
        border: none;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-add:hover {
        background: #16a34a;
        color: white;
    }
    
    .table-employees {
        font-size: 13px;
    }
    .table-employees thead th {
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
    .table-employees tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-employees tbody tr:hover {
        background: #f8fafc;
    }
    .table-employees .employee-name {
        font-weight: 600;
        color: #0f172a;
        font-size: 13px;
    }
    .table-employees .employee-number {
        font-size: 11px;
        color: #94a3b8;
    }
    .table-employees .employee-details {
        font-size: 10px;
        color: #94a3b8;
    }
    .table-employees .badge-status {
        font-size: 10px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .table-employees .badge-status.active {
        background: #dcfce7;
        color: #166534;
    }
    .table-employees .badge-status.inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    .table-employees .badge-nasfund {
        font-size: 10px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .table-employees .badge-nasfund.yes {
        background: #dcfce7;
        color: #166534;
    }
    .table-employees .badge-nasfund.no {
        background: #fee2e2;
        color: #991b1b;
    }
    .table-employees .btn-action {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
        margin: 0 1px;
    }
    .table-employees .btn-action.view {
        background: #dbeafe;
        color: #1e40af;
    }
    .table-employees .btn-action.view:hover {
        background: #bfdbfe;
    }
    .table-employees .btn-action.edit {
        background: #fef3c7;
        color: #92400e;
    }
    .table-employees .btn-action.edit:hover {
        background: #fde68a;
    }
    .table-employees .btn-action.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    .table-employees .btn-action.delete:hover {
        background: #fecaca;
    }
    .table-employees .avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 11px;
        color: white;
        background: #94a3b8;
        margin: 0 auto;
    }
    .table-employees .avatar img {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
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
    
    @media (max-width: 768px) {
        .employee-header { padding: 16px; }
        .employee-header .header-title { font-size: 16px; }
        .filter-bar { flex-direction: column; align-items: stretch; }
        .filter-bar .filter-input, .filter-bar .filter-select { width: 100%; }
        .table-employees { font-size: 11px; }
        .table-employees thead th, .table-employees tbody td { padding: 6px 8px; }
        .stat-box .stat-value { font-size: 16px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="employee-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-users text-indigo-300 mr-2"></i> Employees
                    </div>
                    <div class="header-subtitle mt-1">
                        Manage employee records and information
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('employees.create') }}" class="btn-add">
                        <i class="fas fa-plus"></i> Add Employee
                    </a>
                </div>
            </div>
        </div>


        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" class="flex flex-wrap items-center gap-2 w-full">
                <input type="text" name="search" placeholder="Search employees..." 
                       class="filter-input" style="flex: 1; min-width: 150px;"
                       value="{{ request('search') }}">
                
                <select name="department" class="filter-select" style="min-width: 130px;">
                    <option value="">All Departments</option>
                    @foreach(\App\Models\Department::where('company_id', auth()->user()->company_id)->get() as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>

                <select name="position_id" class="filter-select" style="min-width: 130px;">
                    <option value="">All Positions</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}" {{ request('position_id') == $position->id ? 'selected' : '' }}>
                            {{ $position->name }}
                        </option>
                    @endforeach
                </select>
                
                <select name="employee_type" class="filter-select" style="min-width: 120px;">
                    <option value="">All Types</option>
                    <option value="National" {{ request('employee_type') == 'National' ? 'selected' : '' }}>National</option>
                    <option value="Expatriate" {{ request('employee_type') == 'Expatriate' ? 'selected' : '' }}>Expatriate</option>
                </select>
                
                <select name="status" class="filter-select" style="min-width: 110px;">
                    <option value="">All Status</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('employees.index') }}" class="btn-clear">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>

        <!-- Employee Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-employees w-full">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 35px;">#</th>
                            <th class="text-center" style="width: 35px;">Image</th>
                            <th style="width: 70px;">Emp No</th>
                            <th>Employee Details</th>
                            <th>Position</th>
                            <th>Workshift</th>
                            <th>Department</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Hourly Rate</th>
                            <th class="text-center">Allowance</th>
                            <th class="text-center">Default Pay</th>
                            <th class="text-center">NASFUND</th>
                            <th class="text-center" style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td class="text-center" style="color: #9ca3af; font-size: 11px;">
                                {{ $loop->iteration }}
                            </td>
                            <td class="text-center">
                                @if($employee->photo_path)
                                    <div class="avatar">
                                        <img src="{{ Storage::url($employee->photo_path) }}" alt="{{ $employee->full_name }}">
                                    </div>
                                @else
                                    <div class="avatar" style="background: #6366f1;">
                                        {{ strtoupper(substr($employee->full_name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="employee-number" style="font-weight: 600; color: #2563eb; font-size: 11px;">
                                {{ $employee->employee_number }}
                            </td>
                            <td>
                                <div class="employee-name">{{ $employee->full_name ?? 'N/A' }}</div>
                                <div class="employee-details">Email: {{ $employee->email ?? 'N/A' }}</div>
                                <div class="employee-details">Contact: {{ $employee->phone ?? 'N/A' }}</div>
                            </td>
                            <td style="font-size: 12px;">
                                @php
                                    $pos = App\Models\Position::find($employee->position_id);
                                @endphp
                                {{ $pos ? $pos->name : 'N/A' }}
                            </td>
                            <td style="font-size: 12px;">
                                <div>{{ $employee->workshift ?? 'Regular Dayshift' }}</div>
                                <div style="font-size: 9px; color: #9ca3af;">08:00 AM - 05:00 PM</div>
                            </td>
                            <td style="font-size: 12px;">{{ $employee->department->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge-status {{ $employee->status == 'Active' ? 'active' : 'inactive' }}">
                                    {{ $employee->status ?? 'Active' }}
                                </span>
                            </td>
                            <td class="text-right" style="font-weight: 500; font-size: 12px;">
                                {{ number_format($employee->hourly_rate ?? 0, 2) }}
                            </td>
                            <td class="text-center" style="font-size: 12px; color: #6b7280;">-</td>
                            <td class="text-center" style="font-size: 12px;">
                                {{ $employee->payment_method == 'Bank Transfer' ? 'Bank' : 'Cash' }}
                            </td>
                            <td class="text-center">
                                <span class="badge-nasfund {{ $employee->nasfund_number ? 'yes' : 'no' }}">
                                    {{ $employee->nasfund_number ? 'YES' : 'NO' }}
                                </span>
                            </td>
                            <td class="text-center" style="white-space: nowrap;">
                                <a href="{{ route('employees.show', $employee) }}" class="btn-action view" title="View Employee">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('employees.edit', $employee) }}" class="btn-action edit" title="Edit Employee">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action delete" title="Delete Employee">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13">
                                <div class="empty-state">
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h3>No Employees Found</h3>
                                    <p>No employees match your search criteria.</p>
                                    <a href="{{ route('employees.create') }}" class="btn-add">
                                        <i class="fas fa-plus"></i> Add Employee
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
        <div class="mt-4 flex flex-wrap items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} entries
            </div>
            <div>
                {{ $employees->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
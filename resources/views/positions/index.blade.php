@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage Positions / Designations
        </h2>
        <div class="text-sm text-gray-500">
            Management / Positions
        </div>
    </div>
@endsection

@section('content')
<style>
    .position-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .position-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .position-header .header-subtitle {
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
    
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .form-card .card-header {
        background: #f8fafc;
        padding: 14px 20px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 600;
        color: #1a1f36;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-card .card-header i {
        color: #6366f1;
    }
    .form-card .card-body {
        padding: 20px;
    }
    
    .table-positions {
        font-size: 13px;
    }
    .table-positions thead th {
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
    .table-positions tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-positions tbody tr:hover {
        background: #f8fafc;
    }
    .table-positions .position-name {
        font-weight: 600;
        color: #0f172a;
    }
    .table-positions .position-dept {
        color: #64748b;
        font-size: 12px;
    }
    .table-positions .position-desc {
        color: #94a3b8;
        font-size: 12px;
    }
    
    .btn-add {
        background: #4f46e5;
        color: white;
        padding: 8px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-add:hover {
        background: #4338ca;
        color: white;
    }
    .btn-action {
        padding: 3px 12px;
        border-radius: 4px;
        font-size: 12px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-action.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.delete:hover {
        background: #fecaca;
    }
    .form-input {
        padding: 8px 14px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
        background: white;
        width: 100%;
    }
    .form-input:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .form-select {
        padding: 8px 14px;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
        background: white;
        width: 100%;
    }
    .form-select:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .form-group label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    .empty-state .icon {
        font-size: 40px;
        color: #d1d5db;
        margin-bottom: 12px;
    }
    .empty-state h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1a1f36;
        margin-bottom: 4px;
    }
    .empty-state p {
        color: #6b7280;
        font-size: 13px;
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
    
    @media (max-width: 768px) {
        .position-header { padding: 16px; }
        .position-header .header-title { font-size: 16px; }
        .form-card .card-body { padding: 16px; }
        .form-card .grid { grid-template-columns: 1fr; }
        .form-card .flex { flex-direction: column; }
        .form-card .flex .btn-add { width: 100%; }
        .table-positions { font-size: 11px; }
        .table-positions thead th, .table-positions tbody td { padding: 6px 8px; }
        .stat-box .stat-value { font-size: 16px; }
    }
</style>

<div class="py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="position-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-briefcase text-indigo-300 mr-2"></i> Manage Positions / Designations
                    </div>
                    <div class="header-subtitle mt-1">
                        Create and manage employee positions and designations
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('departments.index') }}" class="btn-secondary" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 8px; font-size: 13px; text-decoration: none; display: inline-block;">
                        <i class="fas fa-arrow-left"></i> Back to Departments
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


        <!-- Add Position Form -->
        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i> Add New Position
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('positions.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="form-group">
                            <label>Position Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" placeholder="e.g., Senior Developer" 
                                   class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label>Department (Optional)</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group md:col-span-2">
                            <label>Description (Optional)</label>
                            <div class="flex gap-2">
                                <input type="text" name="description" placeholder="Brief description of the position..." 
                                       class="form-input">
                                <button type="submit" class="btn-add">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                    @error('name')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </div>

        <!-- Position List -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-positions w-full">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Position Name</th>
                            <th>Department</th>
                            <th>Description</th>
                            <th class="text-center" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($positions as $index => $position)
                        <tr>
                            <td style="color: #9ca3af; font-size: 12px;">{{ $index + 1 }}</td>
                            <td class="position-name">{{ $position->name }}</td>
                            <td class="position-dept">{{ $position->department->name ?? '—' }}</td>
                            <td class="position-desc">{{ $position->description ?? '-' }}</td>
                            <td class="text-center">
                                @php
                                    $employeeCount = \App\Models\Employee::where('position_id', $position->id)->count();
                                @endphp
                                @if($employeeCount == 0)
                                    <form method="POST" action="{{ route('positions.destroy', $position) }}" 
                                          onsubmit="return confirm('Delete this position?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="btn-action" style="background: #f1f5f9; color: #94a3b8; cursor: not-allowed;" title="Cannot delete position with assigned employees">
                                        <i class="fas fa-lock"></i> Protected
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <h3>No Positions Found</h3>
                                    <p>Add your first position using the form above.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
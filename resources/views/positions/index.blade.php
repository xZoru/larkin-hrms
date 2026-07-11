@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Positions / Designations
        </h2>
        <div class="text-sm text-gray-500">
            Management / Positions
        </div>
    </div>
@endsection

@section('content')
<style>
    .header-card {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .btn-create {
        background: #4f46e5;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-create:hover {
        background: #4338ca;
    }
    .btn-action {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        border: none;
        cursor: pointer;
        white-space: nowrap;
    }
    .btn-action.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.delete:hover {
        background: #fecaca;
    }
    .btn-action.toggle-active {
        background: #dcfce7;
        color: #166534;
    }
    .btn-action.toggle-active:hover {
        background: #bbf7d0;
    }
    .btn-action.toggle-inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.toggle-inactive:hover {
        background: #fecaca;
    }
    
    .table-positions {
        font-size: 13px;
        width: 100%;
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
        text-align: left;
    }
    .table-positions tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-positions tbody tr:hover {
        background: #f8fafc;
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
    
    .action-buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: nowrap;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    .empty-state .icon {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 12px;
    }
    .empty-state h3 {
        font-size: 18px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }
    .empty-state p {
        color: #6b7280;
        font-size: 14px;
    }
    
    .form-inline {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    .form-inline input {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
        min-width: 250px;
    }
    .form-inline input:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    
    /* ✅ Force single line for action buttons */
    .action-buttons form {
        display: inline-block;
        margin: 0;
        padding: 0;
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="header-card">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="company-name">Positions / Designations</div>
                    <div class="text-gray-300 text-sm mt-1">
                        {{ count($positions) }} positions
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <form action="{{ route('positions.store') }}" method="POST" class="form-inline">
                        @csrf
                        <input type="text" name="name" placeholder="Position Name..." required>
                        <button type="submit" class="btn-create">
                            + Add Position
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Positions Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-positions">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($positions as $position)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900">{{ $position->name }}</div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $position->company->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="badge-status {{ $position->is_active ? 'active' : 'inactive' }}">
                                    {{ $position->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="action-buttons">
                                    <form action="{{ route('positions.toggle', $position) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-action {{ $position->is_active ? 'toggle-active' : 'toggle-inactive' }}">
                                            {{ $position->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('positions.destroy', $position) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action delete" onclick="return confirm('Delete this position?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-gray-500">
                                <div class="empty-state">
                                    <div class="icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <h3>No Positions Found</h3>
                                    <p>Create your first position using the form above.</p>
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
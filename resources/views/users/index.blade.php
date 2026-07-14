@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Management
        </h2>
        <div class="text-sm text-gray-500">
            Management / Users
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
    .header-card .company-name {
        font-size: 20px;
        font-weight: 700;
    }
    .stat-box {
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px 16px;
        text-align: center;
        border: 1px solid #e5e7eb;
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
    .stat-box .stat-value.green { color: #16a34a; }
    .stat-box .stat-value.red { color: #dc2626; }
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
    }
    .btn-action.view { background: #dbeafe; color: #1e40af; }
    .btn-action.view:hover { background: #bfdbfe; }
    .btn-action.edit { background: #fef3c7; color: #92400e; }
    .btn-action.edit:hover { background: #fde68a; }
    .btn-action.delete { background: #fee2e2; color: #991b1b; }
    .btn-action.delete:hover { background: #fecaca; }
    .btn-action.activate { background: #dcfce7; color: #166534; }
    .btn-action.activate:hover { background: #bbf7d0; }
    .btn-action.deactivate { background: #fee2e2; color: #991b1b; }
    .btn-action.deactivate:hover { background: #fecaca; }
    
    .table-users {
        font-size: 13px;
        width: 100%;
    }
    .table-users thead th {
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
    .table-users tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-users tbody tr:hover {
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
    
    .badge-role {
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 10px;
        display: inline-block;
        background: #e0e7ff;
        color: #3730a3;
        margin: 2px;
    }
    
    .badge-type {
        font-size: 10px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 10px;
        display: inline-block;
    }
    .badge-type.national {
        background: #dbeafe;
        color: #1e40af;
    }
    .badge-type.expatriate {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-type.all {
        background: #dcfce7;
        color: #166534;
    }
    
    .badge-company {
        font-size: 10px;
        font-weight: 500;
        padding: 2px 8px;
        border-radius: 10px;
        display: inline-block;
        background: #f3f4f6;
        color: #374151;
        margin: 2px;
        border: 1px solid #e5e7eb;
    }
    .badge-company.default {
        background: #dbeafe;
        color: #1e40af;
        border-color: #93c5fd;
    }
    
    .action-buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex-wrap: wrap;
    }
    
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    
    .company-list {
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
        max-width: 250px;
    }
    
    .company-list .badge-company {
        white-space: nowrap;
    }
    
    @media (max-width: 768px) {
        .filter-grid { grid-template-columns: 1fr; }
        .table-users { font-size: 11px; }
        .table-users thead th, .table-users tbody td { padding: 6px 8px; }
        .company-list { max-width: 120px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- ===== HEADER ===== -->
        <div class="header-card">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="company-name">User Management</div>
                    <div class="text-gray-300 text-sm mt-1">
                        {{ $totalUsers }} total users
                        <span class="text-gray-400 mx-2">|</span>
                        <span class="text-green-300">{{ $activeUsers }} Active</span>
                        <span class="text-gray-400 mx-2">|</span>
                        <span class="text-red-300">{{ $inactiveUsers }} Inactive</span>
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('users.create') }}" class="btn-create">
                        + New User
                    </a>
                </div>
            </div>
        </div>

        <!-- ===== STATS ===== -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="stat-box">
                <div class="stat-value">{{ $totalUsers }}</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-box">
                <div class="stat-value green">{{ $activeUsers }}</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-box">
                <div class="stat-value red">{{ $inactiveUsers }}</div>
                <div class="stat-label">Inactive</div>
            </div>
        </div>

        <!-- ===== FILTERS ===== -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
            <form method="GET" action="{{ route('users.index') }}" class="filter-grid">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                    <input type="text" name="search" class="filter-input" 
                           placeholder="Name or Email..." value="{{ request('search') }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                    <select name="role" class="filter-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role') == $role->name)>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active" @selected(request('status') == 'active')>Active</option>
                        <option value="inactive" @selected(request('status') == 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-filter">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- ===== USERS TABLE ===== -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-users">
                    <thead>
                        <tr>
                            <th style="min-width: 120px;">User</th>
                            <th style="min-width: 180px;">Email</th>
                            <th style="min-width: 200px;">Companies</th>
                            <th>User Type</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-center" style="min-width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $user->id }}</div>
                            </td>
                            <td>
                                <div class="text-sm">{{ $user->email }}</div>
                            </td>
                            <td>
                                @if($user->isSuperAdmin())
                                    <span class="badge-company bg-purple-100 text-purple-800 border-purple-200 font-semibold" style="background-color: #f3e8ff; color: #6b21a8; border-color: #e9d5ff;">
                                        All Companies
                                    </span>
                                @else
                                    <div class="company-list">
                                        @if($user->companies->isNotEmpty())
                                            @foreach($user->companies as $comp)
                                                <span class="badge-company {{ $comp->pivot->is_default ? 'default' : '' }}">
                                                    {{ $comp->code }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-xs text-gray-400 italic">Unassigned</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="badge-type {{ $user->user_type ?? 'all' }}">
                                    {{ $user->user_type_label ?? 'All Employees' }}
                                </span>
                            </td>
                            <td>
                                @forelse($user->roles as $role)
                                    <span class="badge-role">{{ ucfirst($role->name) }}</span>
                                @empty
                                    <span class="text-gray-400 text-sm">No Role</span>
                                @endforelse
                            </td>
                            <td>
                                <span class="badge-status {{ $user->is_active ? 'active' : 'inactive' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('users.show', $user) }}" class="btn-action view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn-action edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.toggle-active', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn-action {{ $user->is_active ? 'deactivate' : 'activate' }}" 
                                                    title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action delete" title="Delete" 
                                                    onclick="return confirm('Delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                <div class="text-center">
                                    <i class="fas fa-users text-4xl text-gray-300 mb-3 block"></i>
                                    <h3 class="text-lg font-medium text-gray-700">No Users Found</h3>
                                    <p class="text-gray-500">Create your first user to get started.</p>
                                    <a href="{{ route('users.create') }}" class="btn-create mt-4">
                                        + New User
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===== PAGINATION ===== -->
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>

    </div>
</div>
@endsection
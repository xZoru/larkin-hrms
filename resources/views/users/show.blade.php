@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Details
        </h2>
        <div class="text-sm text-gray-500">
            Management / Users / View
        </div>
    </div>
@endsection

@section('content')
<style>
    .detail-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 24px;
        max-width: 768px;
        margin: 0 auto;
    }
    .detail-card h2 {
        font-size: 18px;
        font-weight: 700;
        color: #1a1f36;
        margin-bottom: 4px;
    }
    .detail-card .subtitle {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 20px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .detail-item .label {
        font-size: 11px;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .detail-item .value {
        font-size: 14px;
        font-weight: 500;
        color: #1a1f36;
        margin-top: 2px;
    }
    .detail-item .value .badge {
        font-size: 12px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .detail-item .value .badge.active {
        background: #dcfce7;
        color: #166534;
    }
    .detail-item .value .badge.inactive {
        background: #fee2e2;
        color: #991b1b;
    }
    .detail-item .value .badge-role {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 10px;
        display: inline-block;
        background: #e0e7ff;
        color: #3730a3;
        margin-right: 4px;
    }
    .badge-type {
        font-size: 11px;
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
    .btn-back {
        background: #f1f5f9;
        color: #475569;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: #e2e8f0;
    }
    .btn-edit {
        background: #4f46e5;
        color: white;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s;
    }
    .btn-edit:hover {
        background: #4338ca;
    }
    .action-bar {
        display: flex;
        gap: 12px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }
    @media (max-width: 640px) {
        .detail-grid { grid-template-columns: 1fr; }
        .action-bar { flex-direction: column; }
        .action-bar .btn-back, .action-bar .btn-edit { text-align: center; }
    }
</style>

<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-4">
            <a href="{{ route('users.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>

        <div class="detail-card">
            <div class="flex items-start justify-between">
                <div>
                    <h2>{{ $user->name }}</h2>
                    <div class="subtitle">{{ $user->email }}</div>
                </div>
                <span class="badge {{ $user->is_active ? 'active' : 'inactive' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="detail-item">
                <div class="label">Companies</div>
                <div class="value">
                    @foreach($user->companies as $company)
                        <span class="badge-role">{{ $company->name }}</span>
                        @if($company->pivot->is_default)
                            <span class="text-xs text-green-600">(Default)</span>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <div class="label">User ID</div>
                    <div class="value">#{{ $user->id }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Company</div>
                    <div class="value">{{ $user->company->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">User Type</div>
                    <div class="value">
                        <span class="badge-type {{ $user->user_type ?? 'all' }}">
                            {{ $user->user_type_label ?? 'All Employees' }}
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="label">Roles</div>
                    <div class="value">
                        @forelse($user->roles as $role)
                            <span class="badge-role">{{ ucfirst($role->name) }}</span>
                        @empty
                            <span class="text-gray-400">No roles assigned</span>
                        @endforelse
                    </div>
                </div>
                <div class="detail-item">
                    <div class="label">Created At</div>
                    <div class="value">{{ $user->created_at->format('d M Y H:i:s') }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Last Updated</div>
                    <div class="value">{{ $user->updated_at->format('d M Y H:i:s') }}</div>
                </div>
                <div class="detail-item">
                    <div class="label">Email Verified</div>
                    <div class="value">
                        @if($user->email_verified_at)
                            {{ $user->email_verified_at->format('d M Y') }}
                        @else
                            <span class="text-yellow-600">Not verified</span>
                        @endif
                    </div>
                </div>
                @if($user->employee)
                <div class="detail-item">
                    <div class="label">Linked Employee</div>
                    <div class="value">
                        {{ $user->employee->full_name }}
                        <span class="text-sm text-gray-500">({{ $user->employee->employee_number }})</span>
                    </div>
                </div>
                @endif
            </div>

            <div class="action-bar">
                <a href="{{ route('users.edit', $user) }}" class="btn-edit">
                    <i class="fas fa-edit"></i> Edit User
                </a>
                <a href="{{ route('users.index') }}" class="btn-back">
                    <i class="fas fa-list"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
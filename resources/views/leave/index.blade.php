@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Leave Management
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / Leave Management
        </div>
    </div>
@endsection

@section('content')
<style>
    .leave-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .leave-header .company-name {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
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
    .stat-box .stat-value.pending { color: #d97706; }
    .stat-box .stat-value.approved { color: #16a34a; }
    .stat-box .stat-value.rejected { color: #dc2626; }
    .stat-box .stat-value.balance { color: #2563eb; }
    
    .table-leave {
        font-size: 13px;
    }
    .table-leave thead th {
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
    .table-leave tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-leave tbody tr:hover {
        background: #f8fafc;
    }
    .badge-status {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.pending {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-status.approved {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status.rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    .badge-status.cancelled {
        background: #f3f4f6;
        color: #6b7280;
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
    .btn-action.view {
        background: #dbeafe;
        color: #1e40af;
    }
    .btn-action.view:hover {
        background: #bfdbfe;
    }
    .btn-action.approve {
        background: #dcfce7;
        color: #166534;
    }
    .btn-action.approve:hover {
        background: #bbf7d0;
    }
    .btn-action.reject {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.reject:hover {
        background: #fecaca;
    }
    .btn-action.delete {
        background: #fee2e2;
        color: #991b1b;
    }
    .btn-action.delete:hover {
        background: #fecaca;
    }
    .btn-action.cancel {
        background: #f3f4f6;
        color: #6b7280;
    }
    .btn-action.cancel:hover {
        background: #e5e7eb;
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
    .action-buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        flex-wrap: wrap;
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
    @media (max-width: 768px) {
        .table-leave { font-size: 11px; }
        .table-leave thead th, .table-leave tbody td { padding: 6px 8px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Leave Header -->
        <div class="leave-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="company-name">{{ auth()->user()->company->name ?? 'Company' }}</div>
                    <div class="text-gray-300 text-sm mt-1">
                        {{ $leaveRequests->total() }} total leave requests
                        <span class="text-gray-400 mx-2">|</span>
                        <span class="text-yellow-300">{{ $statistics->pending }} Pending</span>
                        <span class="text-gray-400 mx-2">|</span>
                        <span class="text-green-300">{{ $statistics->approved }} Approved</span>
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('leave.create') }}" class="btn-create">
                        + New Leave Request
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="stat-box">
                <div class="stat-value pending">{{ $statistics->pending }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-box">
                <div class="stat-value approved">{{ $statistics->approved }}</div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-box">
                <div class="stat-value rejected">{{ $statistics->rejected }}</div>
                <div class="stat-label">Rejected</div>
            </div>
            <div class="stat-box">
                <div class="stat-value cancelled">{{ $statistics->cancelled }}</div>
                <div class="stat-label">Cancelled</div>
            </div>
            <div class="stat-box">
                <div class="stat-value balance">{{ number_format($statistics->total_balance, 1) }}</div>
                <div class="stat-label">Total Balance</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
            <form action="{{ route('leave.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Employee</label>
                    <select name="employee_id" class="filter-select">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>
                                {{ $employee->employee_number }} - {{ $employee->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Pending" @selected(request('status') == 'Pending')>Pending</option>
                        <option value="Approved" @selected(request('status') == 'Approved')>Approved</option>
                        <option value="Rejected" @selected(request('status') == 'Rejected')>Rejected</option>
                        <option value="Cancelled" @selected(request('status') == 'Cancelled')>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date From</label>
                    <input type="date" name="date_from" class="filter-input" value="{{ request('date_from') }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date To</label>
                    <input type="date" name="date_to" class="filter-input" value="{{ request('date_to') }}">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-filter">Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Leave Requests Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-leave w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Employee</th>
                            <th class="text-left">Type</th>
                            <th class="text-left">Period</th>
                            <th class="text-center">Days</th>
                            <th class="text-left">Reason</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $request)
                        <tr>
                            <td>
                                <div class="font-medium text-gray-900">{{ $request->employee->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $request->employee->employee_number }}</div>
                            </td>
                            <td>
                                <span class="text-sm">{{ $request->leave_type ?? 'Annual' }}</span>
                            </td>
                            <td>
                                <div class="text-sm">{{ $request->start_date->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">to {{ $request->end_date->format('d M Y') }}</div>
                            </td>
                            <td class="text-center font-medium">{{ number_format($request->days_requested, 1) }}</td>
                            <td>
                                <div class="text-sm truncate max-w-[150px]" title="{{ $request->reason ?? 'N/A' }}">
                                    {{ Str::limit($request->reason ?? 'N/A', 30) }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-status {{ strtolower($request->status) }}">
                                    {{ $request->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('leave.show', $request) }}" class="btn-action view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($request->status == 'Pending')
                                        <form action="{{ route('leave.approve', $request) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn-action approve" title="Approve" onclick="return confirm('Approve this leave request?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('leave.reject', $request) }}" method="POST" class="inline" id="reject-form-{{ $request->id }}">
                                            @csrf
                                            <button type="button" class="btn-action reject" title="Reject" onclick="showRejectModal({{ $request->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        <a href="{{ route('leave.edit', $request) }}" class="btn-action view" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('leave.cancel', $request) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn-action cancel" title="Cancel" onclick="return confirm('Cancel this leave request?')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('leave.destroy', $request) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action delete" title="Delete" onclick="return confirm('Delete this leave request?')">
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
                                <div class="empty-state">
                                    <div class="icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <h3>No Leave Requests</h3>
                                    <p>No leave requests found matching your criteria.</p>
                                    <a href="{{ route('leave.create') }}" class="btn-create mt-4">
                                        + Create Leave Request
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
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing {{ $leaveRequests->firstItem() ?? 0 }} to {{ $leaveRequests->lastItem() ?? 0 }} of {{ $leaveRequests->total() }} results
            </div>
            <div>
                {{ $leaveRequests->links() }}
            </div>
        </div>

    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="document.getElementById('rejectModal').classList.add('hidden')"></div>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Reject Leave Request</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Please provide a reason for rejecting this leave request.</p>
                            <form id="rejectForm" method="POST" class="mt-3">
                                @csrf
                                <textarea name="rejection_reason" id="rejection_reason" rows="3" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Enter rejection reason..." required></textarea>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" form="rejectForm" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                    Reject
                </button>
                <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showRejectModal(requestId) {
        document.getElementById('rejectModal').classList.remove('hidden');
        const form = document.getElementById('rejectForm');
        form.action = '/leave/' + requestId + '/reject';
    }
</script>
@endsection
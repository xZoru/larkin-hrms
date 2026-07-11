@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Leave Request Details
        </h2>
        <div class="text-sm text-gray-500">
            Leave Management / Details
        </div>
    </div>
@endsection

@section('content')
<style>
    .detail-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 20px;
        margin-bottom: 20px;
    }
    .detail-card h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1a1f36;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f5f9;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
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
    .badge-status {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.pending { background: #fef3c7; color: #92400e; }
    .badge-status.approved { background: #dcfce7; color: #166534; }
    .badge-status.rejected { background: #fee2e2; color: #991b1b; }
    .badge-status.cancelled { background: #f3f4f6; color: #6b7280; }
    .btn-back {
        background: #f1f5f9;
        color: #475569;
        padding: 8px 20px;
        border-radius: 8px;
        font-weight: 500;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }
    .btn-back:hover {
        background: #e2e8f0;
    }
    @media (max-width: 768px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('leave.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Leave Requests
            </a>
        </div>

        <!-- Leave Request Details -->
        <div class="detail-card">
            <div class="flex items-center justify-between">
                <h3>Leave Request #{{ $leaveRequest->id }}</h3>
                <span class="badge-status {{ strtolower($leaveRequest->status) }}">
                    {{ $leaveRequest->status }}
                </span>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="label">Employee</span>
                    <div class="value">{{ $leaveRequest->employee->full_name }}</div>
                    <div class="text-sm text-gray-500">{{ $leaveRequest->employee->employee_number }}</div>
                </div>
                <div class="detail-item">
                    <span class="label">Leave Type</span>
                    <div class="value">{{ $leaveRequest->leave_type ?? 'Annual' }}</div>
                </div>
                <div class="detail-item">
                    <span class="label">Start Date</span>
                    <div class="value">{{ $leaveRequest->start_date->format('d M Y') }}</div>
                </div>
                <div class="detail-item">
                    <span class="label">End Date</span>
                    <div class="value">{{ $leaveRequest->end_date->format('d M Y') }}</div>
                </div>
                <div class="detail-item">
                    <span class="label">Days Requested</span>
                    <div class="value font-bold text-indigo-600">{{ number_format($leaveRequest->days_requested, 1) }}</div>
                </div>
                <div class="detail-item">
                    <span class="label">Leave Balance</span>
                    <div class="value">
                        <span class="font-semibold">{{ number_format($balance->balance, 1) }}</span>
                        <span class="text-sm text-gray-500">(Earned: {{ number_format($balance->earned, 1) }} | Taken: {{ number_format($balance->taken, 1) }})</span>
                    </div>
                </div>
                @if($leaveRequest->reason)
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="label">Reason</span>
                    <div class="value">{{ $leaveRequest->reason }}</div>
                </div>
                @endif
                @if($leaveRequest->status == 'Approved' && $leaveRequest->approver)
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="label">Approved By</span>
                    <div class="value">{{ $leaveRequest->approver->name ?? 'N/A' }}</div>
                    <div class="text-sm text-gray-500">{{ $leaveRequest->approved_date ? $leaveRequest->approved_date->format('d M Y') : 'N/A' }}</div>
                </div>
                @endif
                @if($leaveRequest->status == 'Rejected' && $leaveRequest->rejection_reason)
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="label">Rejection Reason</span>
                    <div class="value text-red-600">{{ $leaveRequest->rejection_reason }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        @if($leaveRequest->status == 'Pending')
        <div class="detail-card">
            <h3>Actions</h3>
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('leave.approve', $leaveRequest) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md transition">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </form>
                <button type="button" onclick="showRejectModal()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md transition">
                    <i class="fas fa-times"></i> Reject
                </button>
                <a href="{{ route('leave.edit', $leaveRequest) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md transition">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('leave.cancel', $leaveRequest) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-md transition" onclick="return confirm('Cancel this leave request?')">
                        <i class="fas fa-ban"></i> Cancel
                    </button>
                </form>
                <form action="{{ route('leave.destroy', $leaveRequest) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md transition" onclick="return confirm('Delete this leave request?')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        @endif

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
                            <form id="rejectForm" method="POST" action="{{ route('leave.reject', $leaveRequest) }}" class="mt-3">
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
    function showRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
    }
</script>
@endsection
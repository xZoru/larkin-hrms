@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Leave Request
        </h2>
        <div class="text-sm text-gray-500">
            Leave Management / Edit
        </div>
    </div>
@endsection

@section('content')
<style>
    .form-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 24px;
        max-width: 768px;
        margin: 0 auto;
    }
    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        border-color: #6366f1;
        outline: none;
        box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .form-control.error {
        border-color: #dc2626;
    }
    .form-error {
        font-size: 12px;
        color: #dc2626;
        margin-top: 4px;
    }
    .btn-submit {
        background: #4f46e5;
        color: white;
        padding: 10px 32px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-submit:hover {
        background: #4338ca;
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
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-grid .full-width {
        grid-column: 1 / -1;
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
    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-4">
            <a href="{{ route('leave.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Leave Requests
            </a>
        </div>

        <div class="form-card">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Edit Leave Request</h2>
                <span class="badge-status {{ strtolower($leaveRequest->status) }}">
                    {{ $leaveRequest->status }}
                </span>
            </div>

            <form action="{{ route('leave.update', $leaveRequest) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <!-- Employee (readonly) -->
                    <div class="full-width">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control bg-gray-100" value="{{ $leaveRequest->employee->full_name }} ({{ $leaveRequest->employee->employee_number }})" readonly>
                    </div>

                    <!-- Leave Type -->
                    <div>
                        <label class="form-label">Leave Type <span class="text-red-500">*</span></label>
                        <select name="leave_type" class="form-control @error('leave_type') error @enderror" required>
                            <option value="Annual" @selected(old('leave_type', $leaveRequest->leave_type) == 'Annual')>Annual</option>
                            <option value="Sick" @selected(old('leave_type', $leaveRequest->leave_type) == 'Sick')>Sick</option>
                            <option value="Casual" @selected(old('leave_type', $leaveRequest->leave_type) == 'Casual')>Casual</option>
                            <option value="Maternity" @selected(old('leave_type', $leaveRequest->leave_type) == 'Maternity')>Maternity</option>
                            <option value="Paternity" @selected(old('leave_type', $leaveRequest->leave_type) == 'Paternity')>Paternity</option>
                            <option value="Bereavement" @selected(old('leave_type', $leaveRequest->leave_type) == 'Bereavement')>Bereavement</option>
                            <option value="Public Holiday" @selected(old('leave_type', $leaveRequest->leave_type) == 'Public Holiday')>Public Holiday</option>
                            <option value="Unpaid" @selected(old('leave_type', $leaveRequest->leave_type) == 'Unpaid')>Unpaid</option>
                        </select>
                        @error('leave_type')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Days (auto-calculated) -->
                    <div>
                        <label class="form-label">Days Requested</label>
                        <input type="text" id="days_display" class="form-control bg-gray-100" readonly value="{{ $leaveRequest->days_requested }} days">
                        <input type="hidden" name="days_requested" id="days_requested" value="{{ $leaveRequest->days_requested }}">
                        <div class="text-xs text-gray-500 mt-1">Auto-calculated from dates</div>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="form-label">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') error @enderror" 
                            value="{{ old('start_date', $leaveRequest->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="form-label">End Date <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') error @enderror" 
                            value="{{ old('end_date', $leaveRequest->end_date->format('Y-m-d')) }}" required>
                        @error('end_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div class="full-width">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control @error('reason') error @enderror" rows="3" 
                            placeholder="Enter reason for leave...">{{ old('reason', $leaveRequest->reason) }}</textarea>
                        @error('reason')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <a href="{{ route('leave.index') }}" class="btn-back">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update Leave Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const daysDisplay = document.getElementById('days_display');
        const daysHidden = document.getElementById('days_requested');

        function calculateDays() {
            if (startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                if (end >= start) {
                    const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    daysDisplay.value = diff + ' days';
                    daysHidden.value = diff;
                    return diff;
                }
            }
            daysDisplay.value = '{{ $leaveRequest->days_requested }} days';
            daysHidden.value = {{ $leaveRequest->days_requested }};
            return 0;
        }

        startDate.addEventListener('change', calculateDays);
        endDate.addEventListener('change', calculateDays);
    });
</script>
@endsection
@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            New Leave Request
        </h2>
        <div class="text-sm text-gray-500">
            Leave Management / Create
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
    .form-control.error:focus {
        box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
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
    .balance-info {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        margin-top: 8px;
        display: none;
    }
    .balance-info.visible {
        display: block;
    }
    .balance-info .label {
        font-size: 12px;
        color: #6b7280;
    }
    .balance-info .value {
        font-size: 18px;
        font-weight: 700;
        color: #1a1f36;
    }
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
            <h2 class="text-xl font-bold text-gray-900 mb-6">Create Leave Request</h2>

            <form action="{{ route('leave.store') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <!-- Employee Selection -->
                    <div class="full-width">
                        <label class="form-label">Employee <span class="text-red-500">*</span></label>
                        <select name="employee_id" id="employee_id" class="form-control @error('employee_id') error @enderror" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                    {{ $employee->employee_number }} - {{ $employee->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror

                        <!-- Balance Info -->
                        <div id="balanceInfo" class="balance-info">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="label">Available Leave Balance</div>
                                    <div class="value" id="leaveBalance">--</div>
                                </div>
                                <div class="text-sm text-gray-500">
                                    Earned: <span id="earnedDays">--</span> | Taken: <span id="takenDays">--</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Type -->
                    <div>
                        <label class="form-label">Leave Type <span class="text-red-500">*</span></label>
                        <select name="leave_type" class="form-control @error('leave_type') error @enderror" required>
                            <option value="Annual" @selected(old('leave_type') == 'Annual')>Annual</option>
                            <option value="Sick" @selected(old('leave_type') == 'Sick')>Sick</option>
                            <option value="Casual" @selected(old('leave_type') == 'Casual')>Casual</option>
                            <option value="Maternity" @selected(old('leave_type') == 'Maternity')>Maternity</option>
                            <option value="Paternity" @selected(old('leave_type') == 'Paternity')>Paternity</option>
                            <option value="Bereavement" @selected(old('leave_type') == 'Bereavement')>Bereavement</option>
                            <option value="Public Holiday" @selected(old('leave_type') == 'Public Holiday')>Public Holiday</option>
                            <option value="Unpaid" @selected(old('leave_type') == 'Unpaid')>Unpaid</option>
                        </select>
                        @error('leave_type')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Days (auto-calculated) -->
                    <div>
                        <label class="form-label">Days Requested</label>
                        <input type="text" id="days_display" class="form-control bg-gray-100" readonly value="0">
                        <input type="hidden" name="days_requested" id="days_requested" value="0">
                        <div class="text-xs text-gray-500 mt-1">Auto-calculated from dates</div>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="form-label">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') error @enderror" 
                            value="{{ old('start_date') }}" required>
                        @error('start_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="form-label">End Date <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') error @enderror" 
                            value="{{ old('end_date') }}" required>
                        @error('end_date')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Reason -->
                    <div class="full-width">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control @error('reason') error @enderror" rows="3" 
                            placeholder="Enter reason for leave...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                    <a href="{{ route('leave.index') }}" class="btn-back">Cancel</a>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Create Leave Request
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
        const employeeSelect = document.getElementById('employee_id');
        const balanceInfo = document.getElementById('balanceInfo');
        const leaveBalance = document.getElementById('leaveBalance');
        const earnedDays = document.getElementById('earnedDays');
        const takenDays = document.getElementById('takenDays');

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
            daysDisplay.value = '0 days';
            daysHidden.value = 0;
            return 0;
        }

        function loadBalance() {
            const employeeId = employeeSelect.value;
            if (employeeId) {
                fetch('/leave/api/balance?employee_id=' + employeeId)
                    .then(response => response.json())
                    .then(data => {
                        balanceInfo.classList.add('visible');
                        leaveBalance.textContent = data.balance.toFixed(1);
                        earnedDays.textContent = data.earned.toFixed(1);
                        takenDays.textContent = data.taken.toFixed(1);
                    })
                    .catch(() => {
                        balanceInfo.classList.remove('visible');
                    });
            } else {
                balanceInfo.classList.remove('visible');
            }
        }

        // Events
        startDate.addEventListener('change', calculateDays);
        endDate.addEventListener('change', calculateDays);
        employeeSelect.addEventListener('change', function() {
            loadBalance();
            calculateDays();
        });

        // Initial load
        if (employeeSelect.value) {
            loadBalance();
        }
        calculateDays();
    });
</script>
@endsection
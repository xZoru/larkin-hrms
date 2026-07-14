@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Payroll
        </h2>
        <div class="text-sm text-gray-500">
            Dashboard / Payroll / Create
        </div>
    </div>
@endsection

@section('content')
<style>
    .payroll-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .payroll-header .header-title {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .payroll-header .header-subtitle {
        font-size: 14px;
        color: #a0aec0;
    }
    .stat-box {
        background: rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 12px 16px;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.05);
    }
    .stat-box .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: white;
    }
    .stat-box .stat-label {
        font-size: 11px;
        color: #a0aec0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .stat-box .stat-value.blue { color: #60a5fa; }
    .stat-box .stat-value.green { color: #34d399; }
    .stat-box .stat-value.yellow { color: #fbbf24; }
    
    .employee-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #4f46e5;
    }
    .select-all-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #4f46e5;
    }
    .table-employee:hover {
        background-color: #f8fafc;
    }
    .selected-count {
        background: #e0e7ff;
        color: #3730a3;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .btn-primary-custom {
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
    .btn-primary-custom:hover {
        background: #4338ca;
        color: white;
    }
    .btn-primary-custom:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-secondary-custom {
        background: #e2e8f0;
        color: #475569;
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
    .btn-secondary-custom:hover {
        background: #cbd5e1;
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
    .btn-load {
        background: #0ea5e9;
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 13px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-load:hover {
        background: #0284c7;
    }
    .table-payroll-create {
        font-size: 13px;
    }
    .table-payroll-create thead th {
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
    .table-payroll-create tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .table-payroll-create tbody tr:hover {
        background: #f8fafc;
    }
    .table-payroll-create .employee-name {
        font-weight: 600;
        color: #0f172a;
    }
    .table-payroll-create .employee-number {
        font-size: 11px;
        color: #94a3b8;
    }
    .table-payroll-create .amount {
        font-weight: 600;
        color: #0f172a;
    }
    .table-payroll-create .amount.gross {
        color: #16a34a;
        font-weight: 700;
    }
    .table-payroll-create tfoot td {
        background: #f1f5f9;
        font-weight: 700;
        padding: 12px;
        border-top: 2px solid #cbd5e1;
        font-size: 13px;
    }
    .table-payroll-create .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    .table-payroll-create .empty-state .icon {
        font-size: 40px;
        color: #d1d5db;
        margin-bottom: 12px;
    }
    .table-payroll-create .empty-state h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1a1f36;
        margin-bottom: 4px;
    }
    .table-payroll-create .empty-state p {
        color: #6b7280;
        font-size: 13px;
    }
    @media (max-width: 768px) {
        .table-payroll-create { font-size: 11px; }
        .table-payroll-create thead th, .table-payroll-create tbody td { padding: 6px 8px; }
        .stat-box .stat-value { font-size: 16px; }
        .payroll-header { padding: 16px; }
        .payroll-header .header-title { font-size: 16px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="payroll-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="header-title">
                        <i class="fas fa-calculator text-indigo-300 mr-2"></i> Create Payroll
                    </div>
                    <div class="header-subtitle mt-1">
                        Select employees and process payroll for the selected fortnight
                    </div>
                </div>
                <div class="mt-2 sm:mt-0">
                    <a href="{{ route('payroll.index') }}" class="btn-secondary-custom" style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-arrow-left"></i> Back to Payroll
                    </a>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert-custom alert-danger mb-4">
                <span class="icon">❌</span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <!-- Fortnight Selector -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
            <form method="GET" action="{{ route('payroll.create') }}" class="flex flex-wrap items-end gap-4">
                <div style="flex: 1; min-width: 250px;">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Select Fortnight</label>
                    <select name="fortnight" class="filter-select">
                        @foreach($allFortnights as $fn)
                            @php
                                $p = $fortnightPeriods[$fn] ?? null;
                                $isCurrent = $fn == $fortnight;
                            @endphp
                            @if($p)
                                <option value="{{ $fn }}" {{ $isCurrent ? 'selected' : '' }}>
                                    {{ $fn }} ({{ $p['start']->format('M-d-Y') }} - {{ $p['end']->format('M-d-Y') }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-load">
                        <i class="fas fa-sync-alt"></i> Load Employees
                    </button>
                </div>
            </form>
        </div>

        <form method="POST" action="{{ route('payroll.store') }}" id="payrollForm">
            @csrf
            <input type="hidden" name="fortnight" value="{{ $fortnight }}">

            <!-- Payroll Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="stat-box" style="background: #f8fafc; border: 1px solid #e5e7eb;">
                    <div class="stat-value" style="color: #1a1f36;">{{ $fortnight }}</div>
                    <div class="stat-label" style="color: #6b7280;">Fortnight</div>
                </div>
                <div class="stat-box" style="background: #f8fafc; border: 1px solid #e5e7eb;">
                    <div class="stat-value" style="color: #1a1f36; font-size: 16px;">{{ $period['start']->format('M d, Y') }} - {{ $period['end']->format('M d, Y') }}</div>
                    <div class="stat-label" style="color: #6b7280;">Period</div>
                </div>
                <div class="stat-box" style="background: #f8fafc; border: 1px solid #e5e7eb;">
                    <div class="stat-value" style="color: #2563eb;">{{ $employees->count() }}</div>
                    <div class="stat-label" style="color: #6b7280;">Total Employees</div>
                </div>
                <div class="stat-box" style="background: #f8fafc; border: 1px solid #e5e7eb;">
                    <div class="stat-value" style="color: #7c3aed;">
                        <span id="selectedCount" class="selected-count">0 selected</span>
                    </div>
                    <div class="stat-label" style="color: #6b7280;">Selected</div>
                </div>
            </div>

            <!-- Employee Selection Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-payroll-create w-full">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="select-all-checkbox" title="Select All">
                                </th>
                                <th class="text-left">Employee</th>
                                <th class="text-left">Department</th>
                                <th class="text-right">Regular Hours</th>
                                <th class="text-right">Overtime</th>
                                <th class="text-right">Sunday</th>
                                <th class="text-right">Holiday</th>
                                <th class="text-right">Rate</th>
                                <th class="text-right">Gross Pay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalGross = 0;
                            @endphp
                            @forelse($employees as $employee)
                            @php
                                $summary = $employee->attendanceSummaries()->where('fortnight_number', $fortnight)->first();
                                $regularHours = $summary ? $summary->regular_hours : 0;
                                $overtimeHours = $summary ? $summary->overtime_hours : 0;
                                $sundayHours = $summary ? $summary->sunday_hours : 0;
                                $holidayHours = $summary ? $summary->holiday_hours : 0;
                                
                                $hourlyRate = $employee->hourly_rate ?? 0;
                                $calculationHourlyRate = $hourlyRate;
                                $fortnightHours = (float) ($employee->fortnight_hours ?? 84);

                                if ((float) $employee->monthly_salary > 0 && $fortnightHours > 0) {
                                    $calculationHourlyRate = ((float) $employee->monthly_salary * 12)
                                        / ($fortnightHours * 26);
                                }
                                
                                $regularPay = round($regularHours * $calculationHourlyRate, 2);
                                $overtimePay = round($overtimeHours * $calculationHourlyRate * 1.5, 2);
                                $sundayPay = round($sundayHours * $calculationHourlyRate * 2, 2);
                                $holidayPay = round($holidayHours * $calculationHourlyRate * 2, 2);
                                $allowance = $employee->allowance ?? 0;
                                $grossPay = $regularPay + $overtimePay + $sundayPay + $holidayPay + $allowance;
                                
                                $totalGross += $grossPay;
                            @endphp
                            <tr class="table-employee">
                                <td class="text-center">
                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" 
                                           class="employee-checkbox employee-checkbox-{{ $employee->id }}"
                                           data-gross="{{ $grossPay }}">
                                </td>
                                <td>
                                    <div class="employee-name">{{ $employee->full_name }}</div>
                                    <div class="employee-number">{{ $employee->employee_number }}</div>
                                </td>
                                <td>
                                    <span style="font-size: 12px; color: #64748b;">{{ $employee->department->name ?? 'N/A' }}</span>
                                </td>
                                <td class="text-right">{{ number_format($regularHours, 1) }}</td>
                                <td class="text-right">{{ number_format($overtimeHours, 1) }}</td>
                                <td class="text-right">{{ number_format($sundayHours, 1) }}</td>
                                <td class="text-right">{{ number_format($holidayHours, 1) }}</td>
                                <td class="text-right">K {{ number_format($hourlyRate, 2) }}</td>
                                <td class="text-right amount gross">K {{ number_format($grossPay, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <div class="icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <h3>No Active Employees</h3>
                                        <p>No active employees found for this company in the selected fortnight.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($employees->count() > 0)
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-right">TOTAL SELECTED GROSS</td>
                                <td class="text-right amount gross" style="font-size: 18px; color: #16a34a;" id="totalGrossDisplay">K 0.00</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-6 flex flex-wrap items-center justify-between">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-users mr-1"></i>
                    <span id="selectedCountDisplay">0</span> employees selected
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('payroll.index') }}" class="btn-secondary-custom">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" id="submitPayroll" class="btn-primary-custom">
                        <i class="fas fa-play"></i> Payrun
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============ AUTO-SUBMIT ON FORTNIGHT CHANGE ============
        const fortnightSelect = document.querySelector('select[name="fortnight"]');
        if (fortnightSelect) {
            fortnightSelect.addEventListener('change', function() {
                this.closest('form').submit();
            });
        }

        // ============ CHECKBOX SELECTION LOGIC ============
        const selectAllCheckbox = document.getElementById('selectAll');
        const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
        const selectedCountSpan = document.getElementById('selectedCount');
        const selectedCountDisplay = document.getElementById('selectedCountDisplay');
        const totalGrossDisplay = document.getElementById('totalGrossDisplay');
        const submitButton = document.getElementById('submitPayroll');

        function updateSelection() {
            const checked = document.querySelectorAll('.employee-checkbox:checked');
            const count = checked.length;
            const total = employeeCheckboxes.length;

            // Update count displays
            selectedCountSpan.textContent = count + ' selected';
            selectedCountDisplay.textContent = count;

            // Update Select All checkbox
            if (selectAllCheckbox) {
                if (count === total && total > 0) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else if (count > 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
            }

            // Calculate total gross for selected employees
            let totalGross = 0;
            checked.forEach(function(checkbox) {
                const gross = parseFloat(checkbox.dataset.gross) || 0;
                totalGross += gross;
            });
            totalGrossDisplay.textContent = 'K ' + totalGross.toFixed(2);

            // Enable/Disable submit button
            submitButton.disabled = count === 0;
            submitButton.classList.toggle('opacity-50', count === 0);
        }

        // Select All functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                employeeCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = isChecked;
                });
                updateSelection();
            });
        }

        // Individual checkbox changes
        employeeCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateSelection();
            });
        });

        // Initial update
        updateSelection();

        // Form validation before submit
        document.getElementById('payrollForm').addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('.employee-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one employee to create payroll.');
            }
        });
    });
</script>
@endsection

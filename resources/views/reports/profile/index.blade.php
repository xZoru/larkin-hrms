@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Employee Profile Report
        </h2>
        <div class="text-sm text-gray-500">
            Reports / Employee Profile
        </div>
    </div>
@endsection

@section('content')
<style>
    .report-header {
        background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
        border-radius: 10px;
        padding: 20px 24px;
        color: white;
        margin-bottom: 24px;
    }
    .report-header .company-name {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: 0.5px;
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
    .btn-export {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-export.pdf {
        background: #dcfce7;
        color: #166534;
    }
    .btn-export.pdf:hover {
        background: #bbf7d0;
    }
    .btn-export.excel {
        background: #dbeafe;
        color: #1e40af;
    }
    .btn-export.excel:hover {
        background: #bfdbfe;
    }
    .profile-section {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 20px;
        margin-bottom: 20px;
    }
    .profile-section h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1a1f36;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f5f9;
    }
    .profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 12px;
    }
    .profile-item {
        display: flex;
        flex-direction: column;
    }
    .profile-item .label {
        font-size: 11px;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .profile-item .value {
        font-size: 14px;
        font-weight: 500;
        color: #1a1f36;
        margin-top: 2px;
    }
    .profile-item .value.badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .profile-item .value.badge.active { background: #dcfce7; color: #166534; }
    .profile-item .value.badge.inactive { background: #fee2e2; color: #991b1b; }
    .profile-item .value.badge.national { background: #dbeafe; color: #1e40af; }
    .profile-item .value.badge.expatriate { background: #fef3c7; color: #92400e; }
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
    .stat-box .stat-value.blue { color: #2563eb; }
    .stat-box .stat-value.green { color: #16a34a; }
    .stat-box .stat-value.purple { color: #7c3aed; }
    .stat-box .stat-value.orange { color: #ea580c; }
    .table-mini {
        font-size: 13px;
        width: 100%;
    }
    .table-mini thead th {
        background: #f1f5f9;
        color: #475569;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        padding: 8px 12px;
        border-bottom: 2px solid #e2e8f0;
        text-align: left;
    }
    .table-mini tbody td {
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-mini tbody tr:hover {
        background: #f8fafc;
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
    .badge-status {
        font-size: 10px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 12px;
        display: inline-block;
    }
    .badge-status.draft { background: #fef3c7; color: #92400e; }
    .badge-status.approved { background: #dcfce7; color: #166534; }
    .badge-status.processing { background: #dbeafe; color: #1e40af; }
    @media (max-width: 768px) {
        .profile-grid { grid-template-columns: 1fr; }
        .stat-box .stat-value { font-size: 16px; }
        .report-header { padding: 16px; }
    }
</style>

<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Report Header -->
        <div class="report-header">
            <div class="flex flex-wrap items-center justify-between">
                <div>
                    <div class="company-name">{{ $company->name ?? 'Company' }}</div>
                    <div class="report-info mt-1 text-gray-300 text-sm">
                        Employee Profile Report
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
            <form action="{{ route('reports.profile.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Select Employee</label>
                    <select name="employee_id" class="filter-select">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected($selectedEmployee == $employee->id)>
                                {{ $employee->employee_number }} - {{ $employee->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-filter">Generate Report</button>
                </div>
            </form>
        </div>

        @if($selectedEmployee && $profileData)
            <!-- Summary Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="stat-box">
                    <div class="stat-value">{{ $profileData->payroll_count }}</div>
                    <div class="stat-label">Payrolls Processed</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value green">K {{ number_format($profileData->total_earnings, 2) }}</div>
                    <div class="stat-label">Total Earnings</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value orange">{{ $profileData->leave_balance->balance }}</div>
                    <div class="stat-label">Leave Balance (Days)</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value blue">{{ $profileData->service_length->years }} yrs</div>
                    <div class="stat-label">Service Length</div>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex flex-wrap gap-3 mb-6">
                <form action="{{ route('reports.profile.export') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $selectedEmployee }}">
                    <input type="hidden" name="format" value="pdf">
                    <button type="submit" class="btn-export pdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </form>
                <form action="{{ route('reports.profile.export') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $selectedEmployee }}">
                    <input type="hidden" name="format" value="excel">
                    <button type="submit" class="btn-export excel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </form>
            </div>

            <!-- Personal Details -->
            <div class="profile-section">
                <h3><i class="fas fa-user text-blue-600 mr-2"></i> Personal Details</h3>
                <div class="profile-grid">
                    <div class="profile-item">
                        <span class="label">Employee Number</span>
                        <span class="value font-bold text-blue-600">{{ $profileData->employee->employee_number }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Full Name</span>
                        <span class="value">{{ $profileData->employee->full_name }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Gender</span>
                        <span class="value">{{ $profileData->employee->gender ?? 'N/A' }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Date of Birth</span>
                        <span class="value">{{ $profileData->employee->date_of_birth ? \Carbon\Carbon::parse($profileData->employee->date_of_birth)->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Age</span>
                        <span class="value">{{ $profileData->employee->age ?? 'N/A' }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Employee Type</span>
                        <span class="value badge {{ strtolower($profileData->employee->employee_type) }}">
                            {{ $profileData->employee->employee_type ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Status</span>
                        <span class="value badge {{ strtolower($profileData->employee->status) }}">
                            {{ $profileData->employee->status ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="profile-item">
                        <span class="label">NASFUND Number</span>
                        <span class="value">{{ $profileData->employee->nasfund_number ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Employment Details -->
            <div class="profile-section">
                <h3><i class="fas fa-briefcase text-green-600 mr-2"></i> Employment Details</h3>
                <div class="profile-grid">
                    <div class="profile-item">
                        <span class="label">Department</span>
                        <span class="value">{{ $profileData->employee->department->name ?? 'N/A' }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Position</span>
                        <span class="value">{{ $profileData->employee->position->name ?? 'N/A' }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Joining Date</span>
                        <span class="value">{{ $profileData->employee->joining_date ? \Carbon\Carbon::parse($profileData->employee->joining_date)->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Service Length</span>
                        <span class="value">{{ $profileData->service_length->formatted }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">End Date</span>
                        <span class="value">{{ $profileData->employee->end_date ? \Carbon\Carbon::parse($profileData->employee->end_date)->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div class="profile-item">
                        <span class="label">Hourly Rate</span>
                        <span class="value">K {{ number_format($profileData->employee->hourly_rate ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Banking Details -->
            <div class="profile-section">
                <h3><i class="fas fa-university text-purple-600 mr-2"></i> Banking Details</h3>
                @if($profileData->employee->bankAccounts->count() > 0)
                    <table class="table-mini">
                        <thead>
                            <tr>
                                <th>Account Name</th>
                                <th>Account Number</th>
                                <th>Bank</th>
                                <th>BSB</th>
                                <th>Preferred</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profileData->employee->bankAccounts as $bank)
                                <tr>
                                    <td>{{ $bank->account_name }}</td>
                                    <td>{{ $bank->account_number }}</td>
                                    <td>{{ $bank->bank_name }}</td>
                                    <td>{{ $bank->bsb_code }}</td>
                                    <td>
                                        @if($bank->is_preferred)
                                            <span class="badge-status approved">Yes</span>
                                        @else
                                            <span class="badge-status draft">No</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">No bank accounts on file.</p>
                @endif
            </div>

            <!-- Leave Summary -->
            <div class="profile-section">
                <h3><i class="fas fa-calendar-alt text-orange-600 mr-2"></i> Leave Summary</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="stat-box">
                        <div class="stat-value">{{ $profileData->leave_balance->earned }}</div>
                        <div class="stat-label">Days Earned</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value orange">{{ $profileData->leave_balance->taken }}</div>
                        <div class="stat-label">Days Taken</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value green">{{ $profileData->leave_balance->balance }}</div>
                        <div class="stat-label">Balance</div>
                    </div>
                </div>
            </div>

            <!-- Payroll Summary -->
            <div class="profile-section">
                <h3><i class="fas fa-wallet text-blue-600 mr-2"></i> Payroll Summary</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="stat-box">
                        <div class="stat-value">{{ $profileData->payroll_count }}</div>
                        <div class="stat-label">Payrolls</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value green">K {{ number_format($profileData->total_earnings, 2) }}</div>
                        <div class="stat-label">Gross Earnings</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value orange">K {{ number_format($profileData->total_tax, 2) }}</div>
                        <div class="stat-label">Total Tax</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value blue">K {{ number_format($profileData->total_net, 2) }}</div>
                        <div class="stat-label">Net Pay</div>
                    </div>
                </div>

                @if($profileData->employee->payrollItems->count() > 0)
                    <h4 class="text-sm font-semibold text-gray-700 mt-4 mb-2">Recent Payroll History</h4>
                    <table class="table-mini">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Fortnight</th>
                                <th class="text-right">Gross</th>
                                <th class="text-right">Tax</th>
                                <th class="text-right">Net</th>
                                <th>Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profileData->employee->payrollItems->take(5) as $item)
                                <tr>
                                    <td>{{ $item->created_at->format('d M Y') }}</td>
                                    <td>{{ $item->payroll->fortnight_number ?? 'N/A' }}</td>
                                    <td class="text-right">K {{ number_format($item->gross_wage, 2) }}</td>
                                    <td class="text-right">K {{ number_format($item->tax, 2) }}</td>
                                    <td class="text-right font-medium text-green-600">K {{ number_format($item->net_pay, 2) }}</td>
                                    <td>{{ $item->payment_method ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Footer Info -->
            <div class="mt-4 text-sm text-gray-500 flex justify-between">
                <span>
                    Employee Profile for {{ $profileData->employee->full_name }}
                </span>
                <span>
                    Generated: {{ now()->format('d M Y H:i:s') }} | By: {{ auth()->user()->name }}
                </span>
            </div>
        @elseif($selectedEmployee)
            <!-- Empty State - No Data -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="empty-state">
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Employee Not Found</h3>
                    <p>The selected employee could not be found or has no data.</p>
                </div>
            </div>
        @else
            <!-- Empty State - No Selection -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="empty-state">
                    <div class="icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3>Select an Employee</h3>
                    <p>Choose an employee from the dropdown above to view their profile report.</p>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
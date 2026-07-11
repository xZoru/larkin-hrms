<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Profile - {{ $profileData->employee->full_name }}</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #1a1f36;
            line-height: 1.4;
        }
        .header {
            background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 4px 0 0 0;
            opacity: 0.8;
            font-size: 12px;
        }
        .section {
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 16px;
        }
        .section h2 {
            font-size: 13px;
            font-weight: 600;
            color: #1a1f36;
            margin: 0 0 10px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #f1f5f9;
        }
        .grid {
            display: flex;
            flex-wrap: wrap;
        }
        .grid-item {
            width: 50%;
            padding: 3px 0;
        }
        .grid-item .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
        }
        .grid-item .value {
            font-size: 12px;
            font-weight: 500;
        }
        .stats {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }
        .stat-box {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 8px 10px;
            text-align: center;
        }
        .stat-box .stat-value {
            font-size: 16px;
            font-weight: 700;
        }
        .stat-box .stat-label {
            font-size: 8px;
            color: #94a3b8;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        thead {
            background: #f1f5f9;
        }
        th {
            font-size: 8px;
            text-transform: uppercase;
            padding: 4px 6px;
            text-align: left;
            color: #475569;
        }
        td {
            padding: 4px 6px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: 600;
        }
        .badge.active { background: #dcfce7; color: #166534; }
        .badge.inactive { background: #fee2e2; color: #991b1b; }
        .badge.national { background: #dbeafe; color: #1e40af; }
        .badge.expatriate { background: #fef3c7; color: #92400e; }
        .badge.approved { background: #dcfce7; color: #166534; }
        .badge.draft { background: #fef3c7; color: #92400e; }
        .footer {
            margin-top: 20px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }
        .page-break {
            page-break-before: always;
        }
        .text-green { color: #16a34a; }
        .text-blue { color: #2563eb; }
        .text-orange { color: #ea580c; }
    </style>
</head>
<body>
    @php
        $employee = $profileData->employee;
        $leave = $profileData->leave_balance;
        $service = $profileData->service_length;
    @endphp

    <!-- Header -->
    <div class="header">
        <h1>Employee Profile Report</h1>
        <p>{{ $company->name }}</p>
        <p style="font-size: 10px;">{{ $employee->full_name }} ({{ $employee->employee_number }})</p>
    </div>

    <!-- Summary Stats -->
    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">{{ $profileData->payroll_count }}</div>
            <div class="stat-label">Payrolls</div>
        </div>
        <div class="stat-box">
            <div class="stat-value text-green">K {{ number_format($profileData->total_earnings, 2) }}</div>
            <div class="stat-label">Earnings</div>
        </div>
        <div class="stat-box">
            <div class="stat-value text-orange">{{ $leave->balance }}</div>
            <div class="stat-label">Leave Balance</div>
        </div>
        <div class="stat-box">
            <div class="stat-value text-blue">{{ $service->years }} yrs</div>
            <div class="stat-label">Service</div>
        </div>
    </div>

    <!-- Personal Details -->
    <div class="section">
        <h2>Personal Details</h2>
        <div class="grid">
            <div class="grid-item">
                <div class="label">Employee Number</div>
                <div class="value" style="color: #2563eb; font-weight: 700;">{{ $employee->employee_number }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Full Name</div>
                <div class="value">{{ $employee->full_name }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Gender</div>
                <div class="value">{{ $employee->gender ?? 'N/A' }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Date of Birth</div>
                <div class="value">{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d M Y') : 'N/A' }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Age</div>
                <div class="value">{{ $employee->age ?? 'N/A' }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Employee Type</div>
                <div class="value"><span class="badge {{ strtolower($employee->employee_type) }}">{{ $employee->employee_type ?? 'N/A' }}</span></div>
            </div>
            <div class="grid-item">
                <div class="label">Status</div>
                <div class="value"><span class="badge {{ strtolower($employee->status) }}">{{ $employee->status ?? 'N/A' }}</span></div>
            </div>
            <div class="grid-item">
                <div class="label">NASFUND Number</div>
                <div class="value">{{ $employee->nasfund_number ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Employment Details -->
    <div class="section">
        <h2>Employment Details</h2>
        <div class="grid">
            <div class="grid-item">
                <div class="label">Department</div>
                <div class="value">{{ $employee->department->name ?? 'N/A' }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Position</div>
                <div class="value">{{ $employee->position->name ?? 'N/A' }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Joining Date</div>
                <div class="value">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : 'N/A' }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Service Length</div>
                <div class="value">{{ $service->formatted }}</div>
            </div>
            <div class="grid-item">
                <div class="label">End Date</div>
                <div class="value">{{ $employee->end_date ? \Carbon\Carbon::parse($employee->end_date)->format('d M Y') : 'N/A' }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Hourly Rate</div>
                <div class="value">K {{ number_format($employee->hourly_rate ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Banking Details -->
    <div class="section">
        <h2>Banking Details</h2>
        @if($employee->bankAccounts->count() > 0)
            <table>
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
                    @foreach($employee->bankAccounts as $bank)
                        <tr>
                            <td>{{ $bank->account_name }}</td>
                            <td>{{ $bank->account_number }}</td>
                            <td>{{ $bank->bank_name }}</td>
                            <td>{{ $bank->bsb_code }}</td>
                            <td>{{ $bank->is_preferred ? 'Yes' : 'No' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #94a3b8;">No bank accounts on file.</p>
        @endif
    </div>

    <!-- Leave Summary -->
    <div class="section">
        <h2>Leave Summary</h2>
        <div class="stats" style="margin-bottom: 0;">
            <div class="stat-box">
                <div class="stat-value">{{ $leave->earned }}</div>
                <div class="stat-label">Earned</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-orange">{{ $leave->taken }}</div>
                <div class="stat-label">Taken</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-green">{{ $leave->balance }}</div>
                <div class="stat-label">Balance</div>
            </div>
        </div>
    </div>

    <!-- Payroll Summary -->
    <div class="section">
        <h2>Payroll Summary</h2>
        <div class="stats" style="margin-bottom: 8px;">
            <div class="stat-box">
                <div class="stat-value">{{ $profileData->payroll_count }}</div>
                <div class="stat-label">Payrolls</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-green">K {{ number_format($profileData->total_earnings, 2) }}</div>
                <div class="stat-label">Gross Earnings</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-orange">K {{ number_format($profileData->total_tax, 2) }}</div>
                <div class="stat-label">Total Tax</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-blue">K {{ number_format($profileData->total_net, 2) }}</div>
                <div class="stat-label">Net Pay</div>
            </div>
        </div>

        @if($employee->payrollItems->count() > 0)
            <h3 style="font-size: 10px; font-weight: 600; color: #475569; margin: 6px 0 4px 0;">Recent Payrolls</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Fortnight</th>
                        <th class="text-right">Gross</th>
                        <th class="text-right">Tax</th>
                        <th class="text-right">Net</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->payrollItems->take(8) as $item)
                        <tr>
                            <td>{{ $item->created_at->format('d M Y') }}</td>
                            <td>{{ $item->payroll->fortnight_number ?? 'N/A' }}</td>
                            <td class="text-right">K {{ number_format($item->gross_wage, 2) }}</td>
                            <td class="text-right">K {{ number_format($item->tax, 2) }}</td>
                            <td class="text-right">K {{ number_format($item->net_pay, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">
        Generated: {{ now()->format('d M Y H:i:s') }} | Generated By: {{ auth()->user()->name ?? 'System' }}
    </div>
</body>
</html>
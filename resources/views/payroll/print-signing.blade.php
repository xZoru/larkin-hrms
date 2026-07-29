<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>For Signing - Cash Employees - {{ $payroll->fortnight_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
            margin: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header .subtitle {
            font-size: 14px;
            color: #dc2626;
            font-weight: bold;
            margin-top: 5px;
            text-transform: uppercase;
        }
        .header .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }
        .header .info {
            margin-top: 8px;
            font-size: 12px;
        }
        .header .info span {
            display: inline-block;
            margin: 0 15px;
        }
        .header .info .label {
            font-weight: bold;
        }
        .signing-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signing-table th {
            background: #e8e8e8;
            border: 1px solid #000;
            padding: 10px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .signing-table td {
            border: 1px solid #000;
            padding: 10px 12px;
            font-size: 12px;
            vertical-align: middle;
        }
        .signing-table .number-cell {
            text-align: center;
            width: 40px;
            font-weight: bold;
        }
        .signing-table .emp-no-cell {
            width: 120px;
            font-weight: bold;
        }
        .signing-table .name-cell {
            width: 250px;
        }
        .signing-table .amount-cell {
            text-align: right;
            width: 130px;
            font-weight: bold;
            font-size: 13px;
        }
        .signing-table .signature-cell {
            text-align: center;
            width: 180px;
        }
        .signing-table .signature-cell .sig-line {
            display: inline-block;
            width: 140px;
            border-bottom: 1px solid #000;
            margin-top: 18px;
        }
        .signing-table .signature-cell .sig-label {
            display: block;
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        .signing-table .date-cell {
            text-align: center;
            width: 130px;
        }
        .signing-table .date-cell .date-line {
            display: inline-block;
            width: 110px;
            border-bottom: 1px solid #000;
            margin-top: 18px;
        }
        .signing-table .date-cell .date-label {
            display: block;
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        .signing-table tr:hover {
            background: #f9fafb;
        }
        .footer {
            margin-top: 25px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 12px;
        }
        .total-box {
            margin-top: 20px;
            padding: 12px 20px;
            background: #f8f8f8;
            border: 2px solid #000;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            display: flex;
            justify-content: center;
            gap: 40px;
        }
        .total-box span {
            display: inline-block;
        }
        .total-box .highlight {
            color: #dc2626;
            font-size: 16px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            font-size: 100px;
            font-weight: bold;
            color: #000;
            z-index: -1;
            text-transform: uppercase;
            letter-spacing: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #999;
            font-size: 16px;
        }
        .empty-state .icon {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">CASH PAYMENT</div>
    
    <!-- Header -->
    <div class="header">
        <h1>FOR-SIGNING</h1>
        <div class="subtitle">★ CASH EMPLOYEES ★</div>
        <div class="company-name">{{ $company->name ?? 'Paragon Tech Limited' }}</div>
        <div class="info">
            <span><span class="label">Fortnight:</span> {{ $payroll->fortnight_number }}</span>
            <span><span class="label">Period:</span> {{ $payroll->period_start->format('M d, Y') }} - {{ $payroll->period_end->format('M d, Y') }}</span>
            <span><span class="label">Generated:</span> {{ $generated_date->format('M d, Y H:i') }}</span>
        </div>
    </div>

    <!-- Cash Employees Count -->
    <div style="text-align: right; font-size: 11px; color: #666; margin-bottom: 5px;">
        Total Cash Employees: <strong>{{ $total_cash_employees ?? $payrollItems->count() }}</strong>
    </div>

    <!-- Signing Table -->
    @if($payrollItems->count() > 0)
    <table class="signing-table">
        <thead>
            <tr>
                <th style="text-align: center;">#</th>
                <th>EMP. NO</th>
                <th>EMPLOYEE NAME</th>
                <th style="text-align: right;">AMOUNT</th>
                <th style="text-align: center;">SIGNATURE</th>
                <th style="text-align: center;">DATE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrollItems as $index => $item)
            <tr>
                <td class="number-cell">{{ $loop->iteration }}</td>
                <td class="emp-no-cell">{{ $item->employee->employee_number ?? 'N/A' }}</td>
                <td class="name-cell">{{ strtoupper($item->employee->full_name ?? 'N/A') }}</td>
                <td class="amount-cell">K {{ number_format($item->net_pay ?? 0, 2) }}</td>
                <td class="signature-cell">
                    <div class="sig-line"></div>
                    <span class="sig-label">Employee Signature</span>
                </td>
                <td class="date-cell">
                    <div class="date-line"></div>
                    <span class="date-label">Date</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total Box -->
    <div class="total-box">
        <span>Total Cash Employees: <strong>{{ $payrollItems->count() }}</strong></span>
        <span>Total Cash Payout: <strong class="highlight">K {{ number_format($total_cash_payout ?? $payrollItems->sum('net_pay'), 2) }}</strong></span>
    </div>
    @else
    <!-- Empty State -->
    <div class="empty-state">
        <span class="icon">💵</span>
        <h3>No Cash Employees Found</h3>
        <p>This payroll does not have any employees with cash payment method.</p>
    </div>
    @endif

    <!-- Signing Instructions -->
    <div style="margin-top: 20px; padding: 12px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 4px; font-size: 11px;">
        <strong>⚠️ INSTRUCTIONS:</strong>
        <ol style="margin: 5px 0 0 20px; padding: 0;">
            <li>Employee must sign and date in the presence of the Payroll Officer</li>
            <li>Verify identity before signing</li>
            <li>Keep this document for audit purposes</li>
            <li>Cash payout must match the amount shown above</li>
        </ol>
    </div>

    <!-- Footer -->
    <div class="footer">
        This is a system generated document. {{ $company->name ?? '' }} | {{ $generated_date->format('M d, Y H:i') }}
        <br>
        <span style="color: #dc2626; font-weight: bold;">CASH PAYMENT - SIGNING REQUIRED</span>
    </div>
</body>
</html>
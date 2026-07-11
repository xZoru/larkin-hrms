<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summary of Earnings - {{ $year }}</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #1a1f36;
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
        .summary {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }
        .summary-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }
        .summary-card .label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
        }
        .summary-card .value {
            font-size: 14px;
            font-weight: bold;
            color: #1a1f36;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead {
            background: #f1f5f9;
        }
        th {
            font-size: 9px;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            color: #475569;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
        }
        tfoot {
            background: #f1f5f9;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 25px;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            margin: 20px 0 10px 0;
            color: #1a1f36;
        }
        .page-break {
            page-break-before: always;
        }
        .badge {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 600;
        }
        .badge.draft { background: #fef3c7; color: #92400e; }
        .badge.approved { background: #dcfce7; color: #166534; }
        .badge.processing { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <!-- Main Header -->
    <div class="header">
        <h1>Summary of Earnings - {{ $year }}</h1>
        <p>{{ $company->name }}</p>
        <p style="font-size: 10px;">Generated: {{ now()->format('d M Y H:i:s') }} | By: {{ auth()->user()->name }}</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary">
        <div class="summary-card">
            <div class="label">Total Employees</div>
            <div class="value">{{ $summary->total_employees }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Gross Wage</div>
            <div class="value">K {{ number_format($summary->total_gross, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Tax</div>
            <div class="value">K {{ number_format($summary->total_tax, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Net Pay</div>
            <div class="value">K {{ number_format($summary->total_net, 2) }}</div>
        </div>
    </div>

    <!-- Employee Summary -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee #</th>
                <th>Employee Name</th>
                <th style="text-align: right;">Gross Wage</th>
                <th style="text-align: right;">Tax</th>
                <th style="text-align: right;">Net Pay</th>
                <th style="text-align: center;">Payrolls</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $index => $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->employee_number }}</td>
                    <td>{{ $item->full_name }}</td>
                    <td class="text-right">K {{ number_format($item->total_gross, 2) }}</td>
                    <td class="text-right">K {{ number_format($item->total_tax, 2) }}</td>
                    <td class="text-right">K {{ number_format($item->total_net, 2) }}</td>
                    <td class="text-center">{{ $item->payroll_count }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;">TOTAL</td>
                <td class="text-right">K {{ number_format($summary->total_gross, 2) }}</td>
                <td class="text-right">K {{ number_format($summary->total_tax, 2) }}</td>
                <td class="text-right">K {{ number_format($summary->total_net, 2) }}</td>
                <td class="text-center">{{ $summary->total_payrolls }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Fortnightly Summary -->
    @if($fortnightData->count() > 0)
    <div class="section-title">Fortnight-by-Fortnight Summary</div>
    <table>
        <thead>
            <tr>
                <th>Fortnight</th>
                <th>Period</th>
                <th style="text-align: center;">Employees</th>
                <th style="text-align: right;">Gross Wage</th>
                <th style="text-align: right;">Tax</th>
                <th style="text-align: right;">Net Pay</th>
                <th style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fortnightData as $fn)
                <tr>
                    <td>{{ $fn->fortnight_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($fn->period_start)->format('d M') }} - {{ \Carbon\Carbon::parse($fn->period_end)->format('d M, Y') }}</td>
                    <td class="text-center">{{ $fn->total_employees }}</td>
                    <td class="text-right">K {{ number_format($fn->total_gross, 2) }}</td>
                    <td class="text-right">K {{ number_format($fn->total_tax, 2) }}</td>
                    <td class="text-right">K {{ number_format($fn->total_net, 2) }}</td>
                    <td class="text-center">
                        <span class="badge {{ strtolower($fn->status) }}">{{ $fn->status }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: right;">TOTAL</td>
                <td class="text-center">{{ $fortnightData->sum('total_employees') }}</td>
                <td class="text-right">K {{ number_format($fortnightData->sum('total_gross'), 2) }}</td>
                <td class="text-right">K {{ number_format($fortnightData->sum('total_tax'), 2) }}</td>
                <td class="text-right">K {{ number_format($fortnightData->sum('total_net'), 2) }}</td>
                <td class="text-center"></td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="footer">
        Generated: {{ now()->format('d M Y H:i:s') }} | Generated By: {{ auth()->user()->name ?? 'System' }}
    </div>
</body>
</html>
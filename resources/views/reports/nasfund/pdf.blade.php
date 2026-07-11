<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NASFUND Report</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            color: #1a1f36;
        }
        .header {
            background: linear-gradient(135deg, #1a1f36 0%, #2d3555 100%);
            padding: 20px 24px;
            border-radius: 10px;
            color: white;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0 0 0;
            opacity: 0.8;
        }
        .summary {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .summary-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            text-align: center;
        }
        .summary-card .label {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
        }
        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #1a1f36;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: #f1f5f9;
        }
        th {
            font-size: 10px;
            text-transform: uppercase;
            padding: 8px 12px;
            text-align: left;
            color: #475569;
        }
        td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
        }
        tfoot {
            background: #f1f5f9;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-green {
            color: #16a34a;
        }
        .text-blue {
            color: #2563eb;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>NASFUND Contribution Report</h1>
        <p>{{ $company->name }}</p>
        <p style="font-size: 12px;">Period: {{ $fortnight }} - {{ $period->formatted }}</p>
    </div>

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
            <div class="label">EE Contributions (6%)</div>
            <div class="value" style="color: #16a34a;">K {{ number_format($summary->total_ee, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="label">ER Contributions (8.4%)</div>
            <div class="value" style="color: #2563eb;">K {{ number_format($summary->total_er, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee #</th>
                <th>Employee Name</th>
                <th>NASFUND #</th>
                <th style="text-align: right;">Gross Wage</th>
                <th style="text-align: right;">EE (6%)</th>
                <th style="text-align: right;">ER (8.4%)</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $index => $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->employee_number }}</td>
                    <td>{{ $item->full_name }}</td>
                    <td>{{ $item->nasfund_number }}</td>
                    <td class="text-right">K {{ number_format($item->gross_wage, 2) }}</td>
                    <td class="text-right text-green">K {{ number_format($item->ee_contribution, 2) }}</td>
                    <td class="text-right text-blue">K {{ number_format($item->er_contribution, 2) }}</td>
                    <td class="text-right">K {{ number_format($item->total_contribution, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;">TOTAL</td>
                <td class="text-right">K {{ number_format($summary->total_gross, 2) }}</td>
                <td class="text-right text-green">K {{ number_format($summary->total_ee, 2) }}</td>
                <td class="text-right text-blue">K {{ number_format($summary->total_er, 2) }}</td>
                <td class="text-right">K {{ number_format($summary->total_contributions, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated: {{ now()->format('d M Y H:i:s') }} | Generated By: {{ auth()->user()->name ?? 'System' }}
    </div>
</body>
</html>
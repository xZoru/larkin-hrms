<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>For Signing - Cash Employees - {{ $payroll->fortnight_number }}</title>
    <style>
        @page {
            size: 8.26in 11.69in;
            margin: 15mm 12mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 0;
            margin: 0;
            color: #000;
        }
        .header {
            margin-bottom: 20px;
        }
        .header .logo {
            text-align: left;
            margin-bottom: 10px;
        }
        .header .logo img {
            height: 120px;
            max-width: auto;
        }
        .header h1 {
            text-align: center;
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header .info {
            font-size: 12px;
            line-height: 1.6;
        }
        .header .info div {
            margin: 0;
        }
        .signing-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signing-table th,
        .signing-table td {
            box-sizing: border-box;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .signing-table th {
            background: #e8e8e8;
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .signing-table td {
            border: 1px solid #000;
            padding: 8px 10px;
            font-size: 11px;
            vertical-align: middle;
        }
        .signing-table tbody tr:nth-child(odd) td {
            background: #f2f2f2;
        }
        .signing-table tbody tr:nth-child(even) td {
            background: #fff;
        }
        .signing-table .number-cell {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #666;
            text-align: center;
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

    <!-- Header -->
    <div class="header">
        @php
            $logoPath = null;
            $logoDataUri = null;
            if (!empty($company->logo_path)) {
                // Handles either a full/absolute path or a relative path stored from public/
                $logoPath = str_starts_with($company->logo_path, '/')
                    ? $company->logo_path
                    : public_path(ltrim($company->logo_path, '/'));

                if ($logoPath && file_exists($logoPath)) {
                    $imageData = base64_encode(file_get_contents($logoPath));
                    $mimeType = mime_content_type($logoPath) ?: 'image/png';
                    $logoDataUri = 'data:' . $mimeType . ';base64,' . $imageData;
                }
            }
        @endphp
        @if($logoDataUri)
        <div class="logo">
            <img src="{{ $logoDataUri }}" alt="{{ $company->name ?? 'Company' }} logo">
        </div>
        @endif

        <h1>FOR-SIGNING</h1>

        <div class="info">
            <div><strong>Company:</strong> {{ $company->name ?? 'N/A' }}</div>
            <div><strong>Fortnight:</strong> {{ $payroll->fortnight_number }}</div>
            <div><strong>Date Range:</strong> {{ $payroll->period_start->format('M d, Y') }} - {{ $payroll->period_end->format('M d, Y') }}</div>
            <div><strong>Date Generated:</strong> {{ $generated_date->format('M d, Y') }}</div>
        </div>
    </div>

    <!-- Signing Table -->
    @if($payrollItems->count() > 0)
    <table class="signing-table">
        <colgroup>
            <col style="width: 4%;">
            <col style="width: 15%;">
            <col style="width: 36%;">
            <col style="width: 45%;">
        </colgroup>
        <thead>
            <tr>
                <th style="text-align: center; width: 4%;">#</th>
                <th style="width: 15%;">EMP. NO</th>
                <th style="width: 36%;">EMPLOYEE NAME</th>
                <th style="width: 45%;">SIGNATURE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payrollItems as $index => $item)
            <tr>
                <td class="number-cell" style="width: 4%;">{{ $loop->iteration }}</td>
                <td class="emp-no-cell" style="width: 15%;">{{ $item->employee->employee_number ?? 'N/A' }}</td>
                <td class="name-cell" style="width: 36%;">{{ strtoupper($item->employee->full_name ?? 'N/A') }}</td>
                <td class="signature-cell" style="width: 45%;">&nbsp;</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <!-- Empty State -->
    <div class="empty-state">
        <span class="icon">💵</span>
        <h3>No Cash Employees Found</h3>
        <p>This payroll does not have any employees with cash payment method.</p>
    </div>
    @endif

</body>
</html>
{{-- resources/views/payroll/print-payslips.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payslips - FN{{ $payroll->fortnight_number }}</title>
    <style>
        @page {
            margin: 8mm 8mm 8mm 8mm;
            size: A4 landscape;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #000000;
        }

        .payslip-page {
            page-break-after: always;
            width: 100%;
        }

        .payslip-page:last-child {
            page-break-after: auto;
        }

        .payslip-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .payslip-grid td.grid-cell {
            width: 25%;
            vertical-align: top;
            padding: 0 5px 10px 0;
        }

        /* ===== LOGO ===== */
        .logo-block {
            margin-bottom: 3px;
        }

        .logo-block .brand-name {
            font-size: 20px;
            font-weight: 800;
            font-style: italic;
            color: #d0021b;
            line-height: 1;
            letter-spacing: -0.3px;
        }

        .logo-block .brand-tagline {
            font-size: 6px;
            letter-spacing: 1.5px;
            color: #555555;
            text-transform: uppercase;
            border-top: 1px solid #d0021b;
            padding-top: 1px;
            display: inline-block;
        }

        .logo-block .logo-img {
            max-height: 30px;
            max-width: 250px;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        /* ===== TITLE ===== */
        .payslip-title {
            font-size: 9px;
            margin-bottom: 3px;
        }

        .payslip-title .label {
            font-weight: bold;
        }

        /* ===== CARD / BORDER WRAPPER ===== */
        .payslip-card {
            border: 1px solid #000000;
            width: 100%;
        }

        /* ===== EMPLOYEE INFO ===== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 1px 4px;
            font-size: 8.5px;
            vertical-align: top;
        }

        .info-table td.info-label {
            width: 42%;
        }

        /* ===== FINANCE TABLE ===== */
        .finance-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #000000;
        }

        .finance-table th {
            text-align: right;
            font-size: 7.5px;
            font-weight: normal;
            padding: 1px 4px;
        }

        .finance-table th.col-item {
            text-align: left;
        }

        .finance-table td {
            font-size: 8.5px;
            padding: 1px 4px;
        }

        .finance-table td.col-hours,
        .finance-table th.col-hours {
            text-align: right;
            width: 22%;
        }

        .finance-table td.col-kina,
        .finance-table th.col-kina {
            text-align: right;
            width: 26%;
            font-weight: bold;
        }

        .finance-table td.section-label {
            width: 14px;
            text-align: center;
            font-weight: bold;
            font-size: 7.5px;
            letter-spacing: 0.5px;
            border-right: 1px solid #000000;
            writing-mode: vertical-rl;
        }

        /* ===== TOTAL ROW ===== */
        .total-row td {
            border-top: 1px solid #000000;
            font-weight: bold;
            padding-top: 2px;
        }

        .total-row .total-label {
            text-align: left;
        }

        .total-row .total-hours {
            text-align: right;
            font-weight: normal;
            font-size: 7.5px;
        }

        .total-row .total-kina {
            text-align: right;
            font-size: 13.5px;
            color: #1ad002;
        }

        /* ===== FOOTER (inside card) ===== */
        .payslip-footer-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #000000;
        }

        .payslip-footer-table td {
            font-size: 7.5px;
            padding: 1.5px 4px;
        }

        .payslip-footer-table td.footer-label {
            width: 42%;
        }
    </style>
</head>
<body>

@php
    $items = $payrollItems->values();
    $perPage = 8; // 4 columns x 2 rows
    $perRow = 4;
    $chunks = $items->chunk($perPage);
@endphp

@foreach($chunks as $pageItems)
    <div class="payslip-page">
        <table class="payslip-grid">
            @foreach($pageItems->chunk($perRow) as $rowItems)
                <tr>
                    @foreach($rowItems as $item)
                        @php
                            $employee = $item->employee;
                            $totalHours = ($item->regular_hours ?? 0) + ($item->overtime_hours ?? 0) + ($item->sunday_hours ?? 0) + ($item->holiday_hours ?? 0);
                        @endphp
                        <td class="grid-cell">

                            <!-- ===== LOGO ===== -->
                            <div class="logo-block">
                                @if(isset($company->logo_data) && $company->logo_data)
                                    <img src="{{ $company->logo_data }}" class="logo-img" alt="{{ $company->name ?? 'Larkin' }}">
                                @else
                                    <div class="brand-name">{{ $company->name ?? 'Larkin' }}</div>
                                    <div class="brand-tagline">{{ $company->tagline ?? 'Enterprises Ltd' }}</div>
                                @endif
                            </div>

                            <!-- ===== TITLE ===== -->
                            <div class="payslip-title">
                                <span class="label">Payslip:</span>
                                FN{{ $payroll->fortnight_number }}
                                | {{ $payroll->period_start->format('Y-m-d') }} - {{ $payroll->period_end->format('Y-m-d') }}
                            </div>

                            <div class="payslip-card">

                                <!-- ===== EMPLOYEE INFO ===== -->
                                <table class="info-table">
                                    <tr>
                                        <td class="info-label">Emp. No.</td>
                                        <td>{{ $employee->employee_number ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Name</td>
                                        <td>{{ $employee->full_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Position</td>
                                        <td>{{ $employee->position_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">FN Hourly Rate</td>
                                        <td>{{ number_format($item->hourly_rate ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Dependents</td>
                                        <td>{{ $employee->nasfund_dependents ?? 0 }}</td>
                                    </tr>
                                </table>

                                <!-- ===== EARNINGS + DEDUCTIONS ===== -->
                                <table class="finance-table">
                                    <tr>
                                        <th class="col-item" colspan="2"></th>
                                        <th class="col-hours">Hours</th>
                                        <th class="col-kina">Kina</th>
                                    </tr>

                                    <tr>
                                        <td class="section-label" rowspan="6">GROSS</td>
                                        <td>Regular Hrs</td>
                                        <td class="col-hours">{{ number_format($item->regular_hours ?? 0, 2) }}</td>
                                        <td class="col-kina">{{ number_format($item->regular_pay ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>OT - 1.5</td>
                                        <td class="col-hours">{{ number_format($item->overtime_hours ?? 0, 2) }}</td>
                                        <td class="col-kina">{{ number_format($item->overtime_pay ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Sun OT - 2.0</td>
                                        <td class="col-hours">{{ number_format($item->sunday_hours ?? 0, 2) }}</td>
                                        <td class="col-kina">{{ number_format($item->sunday_pay ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Holidays</td>
                                        <td class="col-hours">{{ number_format($item->holiday_hours ?? 0, 2) }}</td>
                                        <td class="col-kina">{{ number_format($item->holiday_pay ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>PLP/ALP/FP</td>
                                        <td class="col-hours">0.00</td>
                                        <td class="col-kina">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>Other</td>
                                        <td class="col-hours"></td>
                                        <td class="col-kina">{{ number_format($item->allowance ?? 0, 2) }}</td>
                                    </tr>

                                    <tr>
                                        <td class="section-label" rowspan="5">DEDUCTION</td>
                                        <td>FN Tax</td>
                                        <td class="col-hours"></td>
                                        <td class="col-kina">{{ number_format($item->tax ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>NPF-6%</td>
                                        <td class="col-hours"></td>
                                        <td class="col-kina">{{ number_format($item->nasfund_ee ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>NCSL</td>
                                        <td class="col-hours"></td>
                                        <td class="col-kina">0.00</td>
                                    </tr>
                                    <tr>
                                        <td>Cash Adv.</td>
                                        <td class="col-hours"></td>
                                        <td class="col-kina">{{ number_format($item->loan_deduction ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Others</td>
                                        <td class="col-hours"></td>
                                        <td class="col-kina">{{ number_format($item->other_deductions ?? 0, 2) }}</td>
                                    </tr>

                                    <tr class="total-row">
                                        <td class="total-label" colspan="2">TOTAL</td>
                                        <td class="total-hours">{{ number_format($totalHours, 2) }}</td>
                                        <td class="total-kina">{{ number_format($item->net_pay ?? 0, 2) }}</td>
                                    </tr>
                                </table>

                                <!-- ===== FOOTER ===== -->
                                <table class="payslip-footer-table">
                                    <tr>
                                        <td class="footer-label">NASFUND ID No.</td>
                                        <td>{{ $employee->nasfund_number ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="footer-label">Bank Acct No.</td>
                                        <td>{{ $item->bank_account ?? optional($employee->bankAccounts->first())->account_number ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="footer-label">NPF-ER:</td>
                                        <td>{{ $item->nasfund_er ? number_format($item->nasfund_er, 2) : '-' }}</td>
                                    </tr>
                                </table>

                            </div>
                        </td>
                    @endforeach

                    {{-- pad out incomplete rows so borders/columns stay aligned --}}
                    @for($i = $rowItems->count(); $i < $perRow; $i++)
                        <td class="grid-cell"></td>
                    @endfor
                </tr>
            @endforeach
        </table>
    </div>
@endforeach

</body>
</html>
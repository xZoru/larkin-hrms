<!doctype html>
<html><head><style>
@page { margin: 13mm 10mm; }
html, body { margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.3; }
.copies { width: 100%; border-collapse: collapse; table-layout: fixed; }
.copies td { width: 50%; vertical-align: top; border: 1.5px solid #000; padding: 16px; }
.header { height: 65px; } .logo { max-width: 165px; max-height: 50px; } .right { text-align: right; }
.name, .amount { font-weight: bold; }
.employee-details { margin: 0 0 0 30px; border-collapse: collapse; line-height: 1.25; }
.employee-details td { border: 0; padding: 0; } .employee-details .label { width: 118px; } .employee-details .value { font-weight: bold; }
.calculation { margin: 28px 0 22px; } .calc { margin: 10px 0 0 76px; border-collapse: collapse; } .calc td { border: 0; padding: 2px 0; white-space: nowrap; }
.calc .operator { width: 22px; } .calc .figure { text-align: right; padding-right: 14px; } .calc .description { white-space: nowrap; }
.calc .bold-figure { font-weight: bold; }
.calc .total { border-top: 1px solid #222; font-size: 15px; font-weight: bold; text-align: left; padding-top: 4px; padding-right: 0; }
.signature-table { margin-top: 28px; }
.signature-table td { border: 0; padding: 6px 0; vertical-align: top; }
.sig-label { text-align: right; white-space: nowrap; }
.sig-label span { font-weight: bold; margin-right: 10px; }
.sig-value { text-align: left; }
.issuer-position { font-style: italic; margin-left: 90px; display: inline-block; }
.line { display: inline-block; width: 170px; text-align: center; border-bottom: 1px solid #222; }
.received-name { display: inline-block; margin-left: 90px; }
</style></head><body>
<table class="copies"><tr>@for($copy = 0; $copy < 2; $copy++)<td>
    <div class="header">@if($company?->logo_data)<img class="logo" src="{{ $company->logo_data }}">@endif<div class="right">{{ now()->format('F d, Y') }}</div></div>
    <table class="employee-details">
        <tr><td class="label">Employee Number:</td><td class="value">{{ $payment->employee->employee_number }}</td></tr>
        <tr><td class="label">Name:</td><td class="value">{{ $payment->employee->full_name }}</td></tr>
        <tr><td class="label">Position:</td><td class="value">{{ $payment->employee->position }}</td></tr>
        <tr><td class="label">Date Commenced:</td><td class="value">{{ $payment->commenced_at->format('Y-m-d') }}</td></tr>
        <tr><td class="label">End Date:</td><td class="value">{{ $payment->ended_at->format('Y-m-d') }}</td></tr>
        <tr><td class="label">Hours per day:</td><td class="value">{{ rtrim(rtrim(number_format($payment->hours_per_day, 2), '0'), '.') }}</td></tr>
        <tr><td class="label">Hourly rate:</td><td class="value">{{ number_format($payment->hourly_rate, 2) }}</td></tr>
        <tr><td class="label">Final Pay:</td><td class="value">{{ number_format($payment->amount, 2) }}</td></tr>
    </table>
    <div class="calculation">Calculation:<table class="calc">
        <tr><td class="operator"></td><td class="figure">1.5</td><td class="description"></td></tr>
        <tr><td class="operator">x</td><td class="figure">{{ rtrim(rtrim(number_format($payment->hours_per_day, 2), '0'), '.') }}</td><td class="description">Hours/day</td></tr>
        <tr><td class="operator">x</td><td class="figure">{{ number_format($payment->hourly_rate, 2) }}</td><td class="description">Hourly rate</td></tr>
        <tr><td class="operator">x</td><td class="figure bold-figure">{{ number_format($payment->service_months, 2) }}</td><td class="description">Months in Service</td></tr>
        <tr><td class="operator total"></td><td class="figure total">K {{ number_format($payment->amount, 2) }}</td><td class="description total"></td></tr>
    </table></div>
    <table class="signature-table">
        <tr><td class="sig-label"><span>Issued by:</span></td><td class="sig-value"><b>{{ strtoupper($payment->issuer_name) }}</b><br><span class="issuer-position">{{ $payment->issuer_position }}</span></td></tr>
        <tr><td class="sig-label"><span>Received by:</span></td><td class="sig-value"><span class="line">&nbsp;</span><br><span class="received-name">{{ $payment->employee->full_name }}</span></td></tr>
        <tr><td class="sig-label"><span>Date:</span></td><td class="sig-value"><span class="line">&nbsp;</span></td></tr>
    </table>
</td>@endfor</tr></table></body></html>
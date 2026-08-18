<!doctype html>
<html><head><style>
@page { margin: 13mm 10mm; }
html, body { margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.3; }
.copies { width: 100%; border-collapse: collapse; table-layout: fixed; }
.copies td { width: 50%; vertical-align: top; border: 1px solid #222; padding: 16px; }
.header { height: 65px; } .logo { max-width: 165px; max-height: 50px; } .right { text-align: right; }
.name, .amount { font-weight: bold; }
.employee-details { margin: 0 0 0 30px; border-collapse: collapse; line-height: 1.25; }
.employee-details td { border: 0; padding: 0; } .employee-details .label { width: 118px; } .employee-details .value { font-weight: bold; }
.calculation { margin: 28px 0 22px; } .calc { margin: 10px 0 0 76px; border-collapse: collapse; } .calc td { border: 0; padding: 2px 0; width: auto; }
.calc .operator { width: 22px; } .calc .figure { width: 55px; text-align: right; padding-right: 14px; } .calc .description { white-space: nowrap; }
.total { border-top: 1px solid #222; font-size: 15px; font-weight: bold; } .signature { margin-top: 28px; }
.issued { text-align: center; } .issuer-position { margin-left: 100px; font-style: italic; } .signature-row { margin: 16px 0 0 20px; }
.line { display: inline-block; width: 170px; text-align: center; border-bottom: 1px solid #222; }
.received-name { display: inline-block; width: 170px; margin-left: 92px; text-align: center; }
</style></head><body>
<table class="copies"><tr>@for($copy = 0; $copy < 2; $copy++)<td>
    <div class="header">@if($company?->logo_data)<img class="logo" src="{{ $company->logo_data }}">@endif<div class="right">{{ now()->format('F d, Y') }}</div></div>
    <table class="employee-details">
        <tr><td class="label">Employee Number:</td><td class="value">{{ $payment->employee->employee_number }}</td></tr>
        <tr><td class="label">Name:</td><td class="value">{{ $payment->employee->full_name }}</td></tr>
        <tr><td class="label">Position:</td><td class="value">{{ $payment->employee->position }}</td></tr>
        <tr><td class="label">Date Commenced:</td><td class="value">{{ $payment->commenced_at->format('Y-m-d') }}</td></tr>
        <tr><td class="label">Last day at work:</td><td class="value">{{ $payment->ended_at->format('Y-m-d') }}</td></tr>
        <tr><td class="label">Hourly rate:</td><td class="value">{{ number_format($payment->hourly_rate, 2) }}</td></tr>
        <tr><td class="label">Annual Leave Pay:</td><td class="value">{{ number_format($payment->amount, 2) }}</td></tr>
    </table>
    <div class="calculation">Calculation:<table class="calc"><tr><td class="operator"></td><td class="figure">{{ number_format($payment->hourly_rate, 2) }}</td><td class="description">Hourly rate</td></tr><tr><td class="operator">x</td><td class="figure">{{ rtrim(rtrim(number_format($payment->hours_per_week, 2), '0'), '.') }}</td><td class="description">Hours per week</td></tr><tr><td class="operator">x</td><td class="figure">{{ number_format($payment->leave_weeks, 2) }}</td><td class="description">Annual Leave in Weeks</td></tr><tr><td class="operator">x</td><td class="figure total">{{ rtrim(rtrim(number_format($payment->service_fraction * 100, 2), '0'), '.') }}%</td><td class="description">Days of Service / 1 Yr</td></tr><tr><td class="operator"></td><td class="figure total">K {{ number_format($payment->amount, 2) }}</td><td class="description"></td></tr></table></div>
    <div class="signature"><div class="issued"><b>Issued by:</b> <span class="name">{{ strtoupper($payment->issuer_name) }}</span><br><span class="issuer-position">{{ $payment->issuer_position }}</span></div><div class="signature-row"><b>Received by:</b> <span class="line">&nbsp;</span><br><span class="received-name">{{ $payment->employee->full_name }}</span></div><div class="signature-row"><b>Date:</b> <span class="line">&nbsp;</span></div></div>
</td>@endfor</tr></table></body></html>
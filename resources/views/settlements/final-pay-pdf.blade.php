<!doctype html>
<html><head><style>
@page { margin: 13mm 10mm; }
body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.3; }
.copies { width: 100%; border-collapse: collapse; table-layout: fixed; }
.copies td { width: 50%; vertical-align: top; border: 1px solid #222; padding: 16px; }
.header { height: 65px; } .logo { max-width: 165px; max-height: 50px; } .right { text-align: right; }
.name, .amount { font-weight: bold; } .calculation { margin: 28px 0 22px; } .calc td { border: 0; padding: 2px; width: auto; }
.total { border-top: 1px solid #222; font-size: 15px; font-weight: bold; } .signature { margin-top: 28px; }
.line { display: inline-block; width: 150px; text-align: center; border-bottom: 1px solid #222; }
</style></head><body>
<table class="copies"><tr>@for($copy = 0; $copy < 2; $copy++)<td>
    <div class="header">@if($company?->logo_data)<img class="logo" src="{{ $company->logo_data }}">@endif<div class="right">{{ now()->format('F d, Y') }}</div></div>
    <p>Employee Number: <span class="name">{{ $payment->employee->employee_number }}</span><br>Name: <span class="name">{{ $payment->employee->full_name }}</span><br>Position: {{ $payment->employee->position }}<br>Date Commenced: {{ $payment->commenced_at->format('Y-m-d') }}<br>End Date: {{ $payment->ended_at->format('Y-m-d') }}<br>Hours per day: {{ number_format($payment->hours_per_day, 2) }}<br>Hourly rate: {{ number_format($payment->hourly_rate, 2) }}<br>Final Pay: <span class="name">{{ number_format($payment->amount, 2) }}</span></p>
    <div class="calculation">Calculation:<table class="calc"><tr><td></td><td>1.5</td></tr><tr><td>x</td><td>{{ number_format($payment->hours_per_day, 2) }}</td><td>Hours/day</td></tr><tr><td>x</td><td>{{ number_format($payment->hourly_rate, 2) }}</td><td>Hourly rate</td></tr><tr><td>x</td><td class="total">{{ number_format($payment->service_months, 2) }}</td><td>Months in Service</td></tr><tr><td></td><td class="total">K {{ number_format($payment->amount, 2) }}</td></tr></table></div>
    <div class="signature"><b>Issued by:</b> <span class="name">{{ strtoupper($payment->issuer_name) }}</span><br><span style="margin-left:100px;font-style:italic">{{ $payment->issuer_position }}</span><p><b>Received by:</b> <span class="line">{{ $payment->employee->full_name }}</span></p><p><b>Date:</b> <span class="line">&nbsp;</span></p></div>
</td>@endfor</tr></table></body></html>

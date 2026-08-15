<!doctype html>
<html><head><style>
@page { margin: 18mm 12mm; }
body { font-family: Arial, sans-serif; font-size: 10px; }
.copies { width: 100%; border-collapse: collapse; table-layout: fixed; }
.copies td { width: 50%; vertical-align: top; border: 1px solid #222; padding: 14px; }
.header { height: 55px; } .logo { max-width: 145px; max-height: 42px; } .right { text-align: right; }
.name, .amount { font-weight: bold; } .calculation { margin: 24px 0 18px; } .calc td { border: 0; padding: 2px; width: auto; }
.total { border-top: 1px solid #222; font-size: 14px; font-weight: bold; } .signature { margin-top: 24px; }
.line { display: inline-block; width: 150px; text-align: center; border-bottom: 1px solid #222; }
</style></head><body>
<table class="copies"><tr>@for($copy = 0; $copy < 2; $copy++)<td>
    <div class="header">@if($company?->logo_data)<img class="logo" src="{{ $company->logo_data }}">@endif<div class="right">{{ now()->format('F d, Y') }}</div></div>
    <p>Employee Number: <span class="name">{{ $payment->employee->employee_number }}</span><br>Name: <span class="name">{{ $payment->employee->full_name }}</span><br>Position: {{ $payment->employee->position }}<br>Date Commenced: {{ $payment->commenced_at->format('Y-m-d') }}<br>Last day at work: {{ $payment->ended_at->format('Y-m-d') }}<br>Hourly rate: {{ number_format($payment->hourly_rate, 2) }}<br>Annual Leave Pay: <span class="name">{{ number_format($payment->amount, 2) }}</span></p>
    <div class="calculation">Calculation:<table class="calc"><tr><td></td><td>{{ number_format($payment->hourly_rate, 2) }}</td><td>Hourly rate</td></tr><tr><td>x</td><td>{{ number_format($payment->hours_per_week, 2) }}</td><td>Hours per week</td></tr><tr><td>x</td><td>{{ number_format($payment->leave_weeks, 2) }}</td><td>Annual Leave in Weeks</td></tr><tr><td>x</td><td class="total">{{ number_format($payment->service_fraction * 100, 2) }}%</td><td>Days of Service / 1 Yr</td></tr><tr><td></td><td class="total">K {{ number_format($payment->amount, 2) }}</td></tr></table></div>
    <div class="signature"><b>Issued by:</b> <span class="name">{{ strtoupper($payment->issuer_name) }}</span><br><span style="margin-left:100px;font-style:italic">{{ $payment->issuer_position }}</span><p><b>Received by:</b> <span class="line">{{ $payment->employee->full_name }}</span></p><p><b>Date:</b> <span class="line">&nbsp;</span></p></div>
</td>@endfor</tr></table></body></html>

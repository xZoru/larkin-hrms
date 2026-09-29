<!doctype html>
<html><head><meta charset="utf-8"><style>
    body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 12px; line-height: 1.6; }
    .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 14px; margin-bottom: 28px; }
    .header h1 { font-size: 20px; margin: 0 0 4px; }
    .muted { color: #666; }
    .meta { width: 100%; margin: 20px 0; border-collapse: collapse; }
    .meta td { padding: 7px 8px; border: 1px solid #ddd; }
    .label { width: 24%; font-weight: bold; background: #f5f5f5; }
    .section { margin-top: 22px; }
    .section h3 { font-size: 13px; margin: 0 0 6px; }
    .text { white-space: pre-wrap; }
    .memo-content { white-space: pre-wrap; margin-top: 24px; }
    .signatures { margin-top: 65px; width: 100%; }
    .signatures td { width: 50%; padding-right: 35px; vertical-align: top; }
    .line { border-top: 1px solid #555; padding-top: 6px; }
</style></head><body>
    <div class="header">
        <h1>{{ $record->employee->company->name ?? 'Company' }}</h1>
        <div class="muted">{{ $record->template?->category ?? 'Human Resources' }}</div>
    </div>
    <h2 style="text-align:center">{{ $record->template?->title ?? 'Memo' }}</h2>
    <table class="meta">
        <tr><td class="label">Memo Number</td><td>{{ $record->memo_number }}</td></tr>
        <tr><td class="label">Date Issued</td><td>{{ $record->date_issued?->format('d F Y') }}</td></tr>
        <tr><td class="label">To</td><td>{{ $record->employee->full_name }} ({{ $record->employee->employee_number }})</td></tr>
        <tr><td class="label">Position</td><td>{{ $record->employee->position_name ?? '—' }}</td></tr>
        @if($record->effectivity_date)<tr><td class="label">Effectivity Date</td><td>{{ $record->effectivity_date->format('d F Y') }}</td></tr>@endif
    </table>
    @if($record->purpose)<div class="section"><h3>Purpose</h3><div class="text">{{ $record->purpose }}</div></div>@endif
    @foreach(['Reason 1' => $record->reason_1, 'Reason 2' => $record->reason_2, 'Reason 3' => $record->reason_3] as $label => $reason)
        @if($reason)<div class="section"><h3>{{ $label }}</h3><div class="text">{{ $reason }}</div></div>@endif
    @endforeach
    <div class="memo-content">{{ $memoContent }}</div>
    @if($record->decision)<div class="section"><h3>Decision</h3><div class="text">{{ $record->decision }}</div></div>@endif
    @if($record->remarks)<div class="section"><h3>Additional Remarks</h3><div class="text">{{ $record->remarks }}</div></div>@endif
    <table class="signatures"><tr>
        <td><div class="line">Issued by: {{ $record->issuedBy->name ?? '' }}<br><span class="muted">Authorized representative</span></div></td>
        <td><div class="line">Received by: {{ $record->employee->full_name }}<br><span class="muted">Date: ____________________</span></div></td>
    </tr></table>
</body></html>

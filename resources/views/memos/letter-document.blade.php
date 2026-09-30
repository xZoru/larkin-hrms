<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:DejaVu Sans,sans-serif;font-size:10pt;line-height:1.45;color:#222}
        .page{padding:38px 48px}.letterhead{width:100%;border-collapse:collapse;margin-bottom:18px}
        .letterhead-logo{width:88px;vertical-align:middle;padding-right:12px}.letterhead-logo img{width:78px;max-height:72px}
        .letterhead-info{vertical-align:middle}.company-name{font-size:15px;font-weight:bold;color:#651b1b;margin-bottom:7px}
        .contact-line{font-size:10px;color:#244b9b;line-height:1.55}.contact-icon{display:inline-block;width:14px;font-weight:bold;color:#c52d36}
        .divider{height:5px;border-top:3px solid #171717;border-bottom:1px solid #bd272d;margin:12px 0 26px}
        .reference{font-size:10px;line-height:1.9;margin-bottom:26px}.date{margin-top:2px}
        .address{margin-bottom:22px}.body{white-space:pre-wrap;text-align:left;line-height:1.45}
        .body strong{font-weight:bold}.employment-heading{text-align:center;font-weight:bold;letter-spacing:1px;line-height:1.5;margin:30px 0 36px;padding:8px 0}
        .sign{margin-top:55px}.sign strong,.sign-title{display:block}.signature-line{display:block;margin:12px 0 4px;letter-spacing:1px}.footer{margin-top:36px;color:#777;font-size:9px}
    </style>
</head>
<body>
@php
    $letterBody = $record->body;
    $isEmploymentConfirmation = str_starts_with($letterBody, '**CONFIRMATION OF EMPLOYMENT**');
    $letterTitle = match ($record->letter_type) {
        'Employment' => 'CONFIRMATION OF EMPLOYMENT',
        'Salary' => 'CONFIRMATION OF SALARY',
        'Bank / Loan' => 'LETTER OF CONFIRMATION FOR BANKING PURPOSES',
        'Visa / Travel' => 'LETTER OF SUPPORT FOR VISA APPLICATION',
        'Custom Letter' => 'CONFIRMATION LETTER',
        default => null,
    };
    if ($isEmploymentConfirmation) {
        $letterBody = preg_replace('/^\*\*CONFIRMATION OF EMPLOYMENT\*\*\R*/', '', $letterBody, 1);
    }
@endphp
<div class="page">
    @php($company = $record->employee->company)
    <table class="letterhead"><tr>
        <td class="letterhead-logo">@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" alt="">@endif</td>
        <td class="letterhead-info">
            <div class="company-name">{{ $company->name ?? config('app.name') }}</div>
            @if($company?->address)<div class="contact-line"><span class="contact-icon">&#9675;</span>{{ $company->address }}</div>@endif
            @if($company?->email)<div class="contact-line"><span class="contact-icon">&#9993;</span>{{ $company->email }}</div>@endif
            @if(data_get($company?->settings, 'website'))<div class="contact-line"><span class="contact-icon">&#9678;</span>{{ data_get($company?->settings, 'website') }}</div>@endif
        </td>
    </tr></table>
    <div class="divider"></div>
    <div class="reference">Ref: {{ $record->letter_number }}<br><div class="date">{{ $record->date_issued->format('j F Y') }}</div></div>
    @if($letterTitle)<div class="employment-heading">{{ $letterTitle }}</div>@endif
    <div class="body">{!! preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', e($letterBody)) !!}</div>
    @unless(str_contains($record->body, 'Yours sincerely,'))
        <div class="sign">Yours sincerely,<br><br><span class="signature-line">____________________________</span><strong>{{ $record->signatory_name }}</strong><span class="sign-title">{{ $record->signatory_title }}</span><strong>{{ $record->employee->company->name ?? config('app.name') }}</strong></div>
    @endunless
    <div class="footer">{{ $record->letter_number }} · Prepared for {{ $record->employee->full_name }}</div>
</div>
</body>
</html>

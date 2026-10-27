<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page{size:A4;margin:30px 42px 52px}
        body{font-family:DejaVu Sans,sans-serif;font-size:10pt;line-height:1.4;color:#222}
        .page{padding-bottom:12px}.letterhead{width:100%;border-collapse:collapse;margin-bottom:24px}
        .letterhead-logo{width:60%;vertical-align:middle;padding-right:12px}.letterhead-logo img{width:250px;max-height:82px;height:auto;object-fit:contain;object-position:left center}
        .letterhead-info{width:40%;vertical-align:middle;text-align:right}.company-name{font-size:10px;font-weight:bold;margin-bottom:5px}
        .contact-line{font-size:8px;font-style:italic;color:#555;line-height:1.4}
        .date{margin:0 0 24px}.recipient{margin:0 0 22px;line-height:1.45}.subject{font-weight:bold;text-decoration:underline;margin:0 0 24px}
        .salutation{margin:0 0 20px}.section{margin:0 0 16px;page-break-inside:avoid}.section-title{font-weight:bold;margin:0 0 5px}
        .text{white-space:pre-wrap}.sign{margin-top:28px;page-break-inside:avoid}.signature-line{display:block;width:180px;height:1px;border-bottom:1px solid #222;margin:90px 0 6px}
        .muted{color:#666}.footer{position:fixed;left:0;right:0;bottom:-44px;border-top:3px solid #bfc0c4;padding-top:5px;text-align:center;color:#777;font-size:8px}
    </style>
</head>
<body>
@php
    $company = $record->employee->company;
    $settings = $company?->settings ?? [];
    $subject = $record->purpose_template_name ?: 'OFFICIAL MEMORANDUM';
    $footerItems = array_filter([
        data_get($settings, 'website'),
        $company?->email,
        $company?->phone,
    ]);
@endphp
<div class="page">
    <table class="letterhead"><tr>
        <td class="letterhead-logo">@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" alt="">@endif</td>
        <td class="letterhead-info">
            <div class="company-name">{{ $company?->name ?? 'Company' }}</div>
            @if($company?->address)<div class="contact-line">{{ $company->address }}</div>@endif
            @if($company?->phone)<div class="contact-line">{{ $company->phone }}</div>@endif
            @if($company?->email)<div class="contact-line">{{ $company->email }}</div>@endif
        </td>
    </tr></table>

    <div class="date">{{ $record->date_issued?->format('j F Y') }}</div>
    <div class="recipient">
        <strong>To:</strong> {{ $record->employee->full_name }} ({{ $record->employee->employee_number }})<br>
        <strong>Position:</strong> {{ $record->employee->position_name ?? '—' }}
    </div>
    <div class="subject">RE: &nbsp;&nbsp; {{ strtoupper($subject) }}</div>
    <div class="salutation">Dear {{ $record->employee->full_name }},</div>

    @if($record->purpose)<div class="section"><div class="text">{{ $record->purpose }}</div></div>@endif
    @foreach([
        $record->reason_1_template_name => $record->reason_1,
        $record->reason_2_template_name => $record->reason_2,
        $record->reason_3_template_name => $record->reason_3,
    ] as $label => $reason)
        @if($reason)<div class="section">@if($label)<div class="section-title">{{ $label }}</div>@endif<div class="text">{{ $reason }}</div></div>@endif
    @endforeach
    @if($record->decision)<div class="section"><div class="section-title">Decision</div><div class="text">{{ $record->decision }}</div></div>@endif
    @if($record->effectivity_date)<div class="section"><div class="section-title">Effectivity Date</div><div class="text">{{ $record->effectivity_date->format('j F Y') }}</div></div>@endif
    @if($record->remarks)<div class="section"><div class="section-title">Additional Remarks</div><div class="text">{{ $record->remarks }}</div></div>@endif

    <div class="sign">Sincerely,<br>{{ $company?->name ?? 'Company' }}
        <span class="signature-line"></span><strong>{{ $record->issuer_name ?: ($record->issuedBy->name ?? '') }}</strong>
        <span class="muted">Authorized representative</span>
    </div>
</div>
<div class="footer">{{ implode(' | ', $footerItems) }}</div>
</body>
</html>

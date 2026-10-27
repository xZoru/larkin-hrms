<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page{size:A4;margin:30px 42px 52px}
        body{font-family:DejaVu Sans,sans-serif;font-size:10pt;line-height:1.4;color:#222}
        .page{padding-bottom:12px}.letterhead{width:100%;border-collapse:collapse;margin-bottom:24px}
        .letterhead-logo{width:60%;vertical-align:middle;padding-right:12px}.letterhead-logo img{width:250px;max-height:82px;height:auto;object-fit:contain;object-position:left center}
        .letterhead-info{width:40%;vertical-align:middle;text-align:right}.company-name{font-size:10px;font-weight:bold;color:#222;margin-bottom:5px}
        .contact-line{font-size:8px;font-style:italic;color:#555;line-height:1.4}.contact-icon{display:none}
        .date{margin:0 0 24px}.address{margin:0 0 22px;line-height:1.35}
        .subject{font-weight:bold;text-decoration:underline;margin:0 0 24px}.body{white-space:pre-wrap;text-align:left;line-height:1.4}
        .body strong{font-weight:bold}
        .sign{margin-top:28px;page-break-inside:avoid}.sign strong,.sign-title{display:block}.signature-line{display:block;width:180px;height:1px;border-bottom:1px solid #222;margin:46px 0 6px}
        .footer{position:fixed;left:0;right:0;bottom:-44px;border-top:3px solid #bfc0c4;padding-top:5px;text-align:center;color:#777;font-size:8px}
    </style>
</head>
<body>
@php
    $letterBody = $record->body;
    $isEmploymentConfirmation = str_starts_with($letterBody, '**CONFIRMATION OF EMPLOYMENT**');
    if ($isEmploymentConfirmation) {
        $letterBody = preg_replace('/^\*\*CONFIRMATION OF EMPLOYMENT\*\*\R*/', '', $letterBody, 1);
    }
    $letterBody = preg_replace('/\R*(?:Yours sincerely,?|Sincerely,)\s*[\s\S]*$/i', '', $letterBody);
    $company = $record->employee->company;
    $letterAddress = $company?->address;
@endphp
<div class="page">
    <table class="letterhead"><tr>
        <td class="letterhead-logo">@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" alt="">@endif</td>
        <td class="letterhead-info">
            <div class="company-name">{{ $letterCompanyName }}</div>
            @if($letterAddress)<div class="contact-line">{{ $letterAddress }}</div>@endif
            @if($company?->phone)<div class="contact-line">Phone: {{ $company->phone }}</div>@endif
        </td>
    </tr></table>
    <div class="date">{{ $record->date_issued->format('j F Y') }}</div>
    @if($record->addressed_to)<div class="address">{{ $record->addressed_to }};</div>@endif
    <div class="subject">RE: &nbsp;&nbsp; {{ strtoupper($record->template?->title ?: $record->template_name) }}</div>
    <div class="body">{!! preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', e($letterBody)) !!}</div>
    <div class="sign">Yours sincerely,<strong>{{ $letterCompanyName }}</strong><span class="signature-line"></span><strong>{{ $record->signatory_name }}</strong><span class="sign-title">{{ $record->signatory_title }}</span></div>
</div>
<div class="footer">{{ data_get($company?->settings, 'website') ?: $company?->email }}@if(data_get($company?->settings, 'website') && $company?->email) &nbsp; | &nbsp; {{ $company->email }}@endif</div>
</body>
</html>

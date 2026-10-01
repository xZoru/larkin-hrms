<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page{margin:38px 48px 66px}
        body{font-family:DejaVu Sans,sans-serif;font-size:10pt;line-height:1.45;color:#222}
        .page{padding-bottom:26px}.letterhead{width:100%;border-collapse:collapse;margin-bottom:18px}
        .letterhead-logo{width:60%;vertical-align:bottom;padding-right:12px}.letterhead-logo img{width:250px;max-height:82px;height:auto;object-fit:contain;object-position:left bottom}
        .letterhead-info{width:40%;vertical-align:bottom;text-align:right}.company-name{font-size:10px;font-weight:bold;color:#222;margin-bottom:5px}
        .contact-line{font-size:8px;font-style:italic;color:#222;line-height:1.4}.contact-icon{display:none}
        .divider{height:5px;border-top:3px solid #171717;border-bottom:1px solid #bd272d;margin:12px 0 26px}
        .reference{font-size:10px;line-height:1.9;margin-bottom:26px}.date{margin-top:2px}
        .address{margin-bottom:22px}.body{white-space:pre-wrap;text-align:left;line-height:1.45}
        .body strong{font-weight:bold}.employment-heading{text-align:center;font-weight:bold;letter-spacing:1px;line-height:1.5;margin:30px 0 36px;padding:8px 0}
        .sign{margin-top:55px}.sign strong,.sign-title{display:block}.signature-line{display:block;width:180px;height:1px;border-bottom:1px solid #222;margin:24px 0 8px}
        .footer{position:fixed;left:0;right:0;bottom:-44px;border-top:1px solid #bd272d;padding-top:8px;text-align:center;color:#244b9b;font-size:8px}
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
    $letterBody = preg_replace('/\R*(?:Yours sincerely,?|Sincerely,)\s*[\s\S]*$/i', '', $letterBody);
@endphp
<div class="page">
    @php($company = $record->employee->company)
    @php
        $letterCompanyNames = ['LKE-POM' => 'Larkin Enterprises Limited', 'LKE-LAE' => 'Larkin Enterprises Limited', 'YJS-POM' => 'Yellow Jacket Security Limited', 'YJS-LAE' => 'Yellow Jacket Security Limited', 'PARA' => 'Paragon Tech Limited', 'ADF' => 'Larkin Enterprises t/a Ad Focus', 'WAVE' => 'Wave Restaurant', 'CARO' => "Caroline's Diner", 'HYVE' => 'Hyve'];
        $letterCompanyName = $letterCompanyNames[$company?->code ?? ''] ?? ($company->name ?? config('app.name'));
        $normalizedCompanyName = strtolower($company->name ?? '');
        if (str_contains($normalizedCompanyName, 'ad focus')) {
            $letterCompanyName = 'Larkin Enterprises t/a Ad Focus';
        } elseif (str_contains($normalizedCompanyName, 'larkin')) {
            $letterCompanyName = 'Larkin Enterprises Limited';
        } elseif (str_contains($normalizedCompanyName, 'yellow jacket')) {
            $letterCompanyName = 'Yellow Jacket Security Limited';
        }
        $letterAddress = 'Portion 3205C Fungi Street, Taurama Road Port Moresby, NCD papua new guinea';
    @endphp
    <table class="letterhead"><tr>
        <td class="letterhead-logo">@if(!empty($logoDataUri))<img src="{{ $logoDataUri }}" alt="">@endif</td>
        <td class="letterhead-info">
            <div class="company-name">{{ $letterCompanyName }}</div>
            <div class="contact-line"><span class="contact-icon">&#9675;</span>{{ $letterAddress }}</div>
        </td>
    </tr></table>
    <div class="divider"></div>
    <div class="reference">Ref: {{ $record->letter_number }}<br><div class="date">{{ $record->date_issued->format('j F Y') }}</div></div>
    @if($letterTitle)<div class="employment-heading">{{ $letterTitle }}</div>@endif
    <div class="body">{!! preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', e($letterBody)) !!}</div>
    <div class="sign">Yours sincerely,<strong>{{ $letterCompanyName }}</strong><span class="signature-line"></span><strong>{{ $record->signatory_name }}</strong><span class="sign-title">{{ $record->signatory_title }}</span></div>
</div>
<div class="footer">{{ data_get($company?->settings, 'website') ?: $company?->email }}@if(data_get($company?->settings, 'website') && $company?->email) &nbsp; | &nbsp; {{ $company->email }}@endif</div>
</body>
</html>

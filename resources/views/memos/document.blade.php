<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 portrait; margin: 12mm 16mm 14mm; }
        html, body { margin: 0; padding: 0; }
        body { font-family: "DejaVu Serif", Georgia, serif; font-size: 10pt; line-height: 1.3; color: #111; }
        .letterhead { width: 100%; border-collapse: collapse; margin: 0 0 20mm; }
        .logo-cell { width: 62%; vertical-align: top; }
        .logo { display: block; width: 52mm; max-height: 24mm; object-fit: contain; object-position: left top; }
        .contact { width: 38%; vertical-align: top; text-align: right; font-size: 7.5pt; font-style: italic; line-height: 1.25; color: #666; }
        .date { margin: 0 0 9mm; }
        .recipient { margin: 0 0 8mm; line-height: 1.3; }
        .subject { margin: 0 0 9mm; font-weight: bold; text-decoration: underline; }
        .salutation { margin: 0 0 8mm; }
        .section { margin: 0 0 5mm; page-break-inside: avoid; }
        .section-title { margin: 0 0 1mm; font-weight: bold; }
        .text { white-space: pre-wrap; }
        .sign { margin-top: 9mm; page-break-inside: avoid; }
        .signature-line { display: block; width: 34mm; height: 1px; border-bottom: 1px solid #333; margin: 13mm 0 1mm; }
        .footer { position: fixed; left: 0; right: 0; bottom: -8mm; border-top: 2px solid #bfc0c4; padding-top: 1.5mm; text-align: center; color: #777; font: 7.5pt "DejaVu Serif", Georgia, serif; }
    </style>
</head>
<body>
@php
    $company = $record->employee->company;
    $settings = $company?->settings ?? [];
    $subject = $record->purpose_template_name ?: 'OFFICIAL MEMORANDUM';
    $footerEmail = in_array($company?->code, ['LKE-POM', 'LKE-LAE'], true) ? 'info@larkinpng.com' : $company?->email;
    $footerItems = array_filter([data_get($settings, 'website'), $footerEmail]);
@endphp
<table class="letterhead"><tr>
    <td class="logo-cell">@if(!empty($logoDataUri))<img class="logo" src="{{ $logoDataUri }}" alt="">@endif</td>
    <td class="contact">
        @if($company?->address)<div>{{ $company->address }}</div>@endif
        @if($company?->phone)<div>Phone: {{ $company->phone }}</div>@endif
        @if($company?->fax)<div>Fax: {{ $company->fax }}</div>@endif
    </td>
</tr></table>

<div class="date">{{ $record->date_issued?->format('j F Y') }}</div>
<div class="recipient">
    <strong>{{ $record->employee->full_name }}</strong><br>
    {{ $record->employee->position_name ?? '' }}<br>
    {{ $company?->address }}
</div>
<div class="salutation">To Whom It May Concern;</div>
<div class="subject">RE: &nbsp;&nbsp;&nbsp; {{ strtoupper($subject) }}</div>

@if($record->purpose)<div class="section"><div class="text">{{ $record->purpose }}</div></div>@endif
@foreach([$record->reason_1_template_name => $record->reason_1, $record->reason_2_template_name => $record->reason_2, $record->reason_3_template_name => $record->reason_3] as $label => $reason)
    @if($reason)<div class="section">@if($label)<div class="section-title">{{ $label }}</div>@endif<div class="text">{{ $reason }}</div></div>@endif
@endforeach
@if($record->decision)<div class="section"><div class="text">{{ $record->decision }}</div></div>@endif
@if($record->effectivity_date)<div class="section"><div class="text">Effectivity Date: {{ $record->effectivity_date->format('j F Y') }}</div></div>@endif
@if($record->remarks)<div class="section"><div class="text">{{ $record->remarks }}</div></div>@endif

<div class="sign">Sincerely,<br>{{ $company?->name ?? 'Company' }}
    <span class="signature-line"></span><strong>{{ $record->issuer_name ?: ($record->issuedBy->name ?? '') }}</strong>
    <div>{{ $record->issuedBy?->position ?? 'Director' }}</div>
</div>
<div class="footer">{{ implode(' | ', $footerItems) }}</div>
</body>
</html>

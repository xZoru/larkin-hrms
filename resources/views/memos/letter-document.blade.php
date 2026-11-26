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
        .address { margin: 0 0 8mm; line-height: 1.3; }
        .subject { margin: 0 0 9mm; font-weight: bold; text-decoration: underline; }
        .body { white-space: pre-wrap; text-align: left; line-height: 1.3; }
        .body strong { font-weight: bold; }
        .sign { margin-top: 9mm; page-break-inside: avoid; }
        .sign strong, .sign-title { display: block; }
        .signature-line { display: block; width: 34mm; height: 1px; border-bottom: 1px solid #333; margin: 13mm 0 1mm; }
        .footer { position: fixed; left: 0; right: 0; bottom: -8mm; border-top: 2px solid #bfc0c4; padding-top: 1.5mm; text-align: center; color: #777; font: 7.5pt "DejaVu Serif", Georgia, serif; }
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
    $footerItems = array_filter([data_get($company?->settings, 'website'), $company?->email]);
@endphp
<table class="letterhead"><tr>
    <td class="logo-cell">@if(!empty($logoDataUri))<img class="logo" src="{{ $logoDataUri }}" alt="">@endif</td>
    <td class="contact">
        @if($letterAddress)<div>{{ $letterAddress }}</div>@endif
        @if($company?->phone)<div>Phone: {{ $company->phone }}</div>@endif
        @if($company?->fax)<div>Fax: {{ $company->fax }}</div>@endif
    </td>
</tr></table>
<div class="date">{{ $record->date_issued->format('j F Y') }}</div>
@if($record->addressed_to)<div class="address">{{ $record->addressed_to }};</div>@endif
<div class="subject">RE: &nbsp;&nbsp;&nbsp; {{ strtoupper($record->template?->title ?: $record->template_name) }}</div>
<div class="body">{!! preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', e($letterBody)) !!}</div>
<div class="sign">Yours sincerely,<br>{{ $letterCompanyName }}<span class="signature-line"></span><strong>{{ $record->signatory_name }}</strong><span class="sign-title">{{ $record->signatory_title }}</span></div>
<div class="footer">{{ implode(' | ', $footerItems) }}</div>
</body>
</html>

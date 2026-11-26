@extends('layouts.app')
@section('content')
@php($defaultLetterheadAddress = "Portion 3205C Fungi Street\nTaurama Rd. Port Moresby, NCD\nP.O. Box 1850 Boroko\nPhone: (675) 320-2777\nFax (675) 320-0551")
<style>
.letter-workspace{display:grid;grid-template-columns:315px minmax(0,1fr);gap:16px;color:#18324a}.letter-left{display:grid;gap:14px;align-content:start}.letter-card,.letter-preview-card{overflow:hidden;border:1px solid #c7e8f4;border-radius:11px;background:#fff;box-shadow:0 3px 12px #2030400b}.letter-head{padding:12px 15px;background:#eff9fc;border-bottom:1px solid #c7e8f4;color:#0876ce;font-weight:700;font-size:13px}.letter-head small{display:block;margin:2px 0 0 34px;color:#a56767;font-size:10px;font-weight:400}.letter-content{padding:15px}.letter-label{display:block;margin:0 0 5px;color:#a14e4e;font-size:10px;text-transform:uppercase;letter-spacing:.4px}.letter-content .form-control,.letter-content .form-select{border-color:#b9e4f4;background:#f2fafc;color:#176cb0;font-size:12px}.letter-content textarea{min-height:110px}.letter-options{display:grid;grid-template-columns:1fr 1fr;gap:8px}.letter-type{padding:10px;border:1px solid #b9e4f4;border-radius:8px;background:#f3fafc;color:#0876ce;font-size:11px;cursor:pointer}.letter-type.active{outline:2px solid #168bd0;background:#e9f7fc}.letter-preview-card{grid-column:2;grid-row:1 / span 3;min-height:540px}.letter-preview-top{padding:13px;background:#eff9fc;border-bottom:1px solid #b9e4f4;color:#0876ce;font-size:13px;font-weight:700}.letter-paper-wrap{display:grid;place-items:center;min-height:500px;padding:22px;background:#d2d6dc}.letter-paper{position:relative;box-sizing:border-box;width:min(100%,595px);min-height:842px;padding:38px 42px 54px;background:white;color:#111;font-family:"DejaVu Serif",Georgia,serif;box-shadow:0 3px 12px #0002;font-size:12px;line-height:1.35}.letter-paper .paper-sign{margin-top:28px}.letter-buttons{display:flex;gap:8px}.letter-buttons .btn{font-size:12px}.letter-token-help{color:#728197;font-size:10px;margin-top:6px}@media(max-width:850px){.letter-workspace{grid-template-columns:1fr}.letter-preview-card{grid-column:auto;grid-row:auto}.letter-paper-wrap{min-height:400px}.letter-paper{padding:35px 28px}}
.letter-signature-line{display:block;width:110px;height:1px;border-bottom:1px solid #333;margin:54px 0 4px}#preview-sign-title{display:block}#preview-sign-company{display:block;margin-top:0;font-weight:400}
.preview-letterhead{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:24px}.preview-letterhead-logo{width:220px;height:72px;object-fit:contain;object-position:left top}.preview-letterhead-info{min-width:0;flex:1;text-align:right}.preview-letterhead-info strong{display:none}.preview-contact{color:#666;font-size:9px;font-style:italic;line-height:1.3;white-space:pre-line}.preview-contact-icon{display:none}.preview-date{margin:0 0 26px}.preview-address{margin:0 0 24px;line-height:1.35}.preview-subject{font-weight:bold;text-decoration:underline;margin:0 0 26px}.preview-body{white-space:pre-wrap;line-height:1.35}.letter-paper [contenteditable="true"]{cursor:text}.letter-paper [contenteditable="true"]:focus{outline:1px dashed #8b93a1;outline-offset:2px}.preview-letter-footer{position:absolute;left:42px;right:42px;bottom:22px;padding-top:5px;border-top:3px solid #bfc0c4;text-align:center;color:#777;font-size:9px}
.letter-workspace{color:#1a1f36}.letter-card,.letter-preview-card{border-color:#e5e7eb;border-radius:9px;box-shadow:0 1px 3px rgba(0,0,0,.06)}.letter-head,.letter-preview-top{background:#f8fafc;border-color:#e5e7eb;color:#1a1f36}.letter-head small,.letter-preview-top small{color:#64748b!important}.letter-content .letter-label{color:#64748b}.letter-content .form-control,.letter-content .form-select{border-color:#d7dce5;background:#fff;color:#1f2937}.letter-content .form-control:focus,.letter-content .form-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}.letter-type{border-color:#d7dce5;background:#fff;color:#334155}.letter-type:hover{border-color:#a5b4fc;background:#f8faff;color:#4338ca}.letter-type.active{outline:2px solid #6366f1;background:#eef2ff;color:#4338ca}.letter-buttons .btn-primary{border-color:#6366f1;background:#6366f1}.letter-buttons .btn-primary:hover{border-color:#4f46e5;background:#4f46e5}.letter-token-help{color:#94a3b8}.letter-preview-card .letter-paper-wrap{background:#e5e7eb}
/* Keep the on-screen sheet in the same physical dimensions and spacing as the A4 PDF. */
.letter-paper{width:min(100%,210mm);min-height:297mm;padding:12mm 16mm 20mm;font-size:10pt;line-height:1.3}
.preview-letterhead{display:grid;grid-template-columns:62% 38%;align-items:start;gap:0;margin-bottom:20mm;font-family:"DejaVu Serif",Georgia,serif}
.preview-letterhead-logo{width:52mm;height:auto;max-height:24mm;object-fit:contain;object-position:left top}
.preview-contact{font-size:7.5pt;line-height:1.25}
.preview-date{margin:0 0 9mm}
.preview-address{margin:0 0 8mm;line-height:1.3}
.recipient-address:empty{min-height:1em}
.preview-subject{margin:0 0 9mm}
.letter-paper .paper-sign{margin-top:9mm;page-break-inside:avoid}
.letter-signature-line{width:34mm;margin:13mm 0 1mm}
.preview-letter-footer{left:16mm;right:16mm;bottom:7mm;border-top:2px solid #bfc0c4;padding-top:1.5mm;font:7.5pt "DejaVu Serif",Georgia,serif}
@media(max-width:850px){.letter-paper{width:min(100%,210mm);min-height:297mm;padding:12mm 16mm 20mm}}
</style>
<form method="POST" action="{{ route('memos.letters.store') }}" id="letter-form">@csrf<input type="hidden" name="addressed_to" id="addressed_to"><input type="hidden" name="recipient_address" id="recipient_address"><input type="hidden" name="subject" id="letter_subject"><input type="hidden" name="letterhead_address" id="letterhead_address">
<div class="letter-workspace"><div class="letter-left"><div class="d-flex justify-content-between align-items-center"><h1 style="font-size:20px;margin:0">Employee Letters</h1><a href="{{ route('memos.templates.index') }}" class="btn btn-sm btn-outline-secondary">Edit templates</a></div>
<section class="letter-card"><div class="letter-head">① &nbsp;Select Employee<small>Choose the staff member this letter is for</small></div><div class="letter-content"><label class="letter-label" for="employee_id">Employee</label><select class="form-select" id="employee_id" name="employee_id" required data-default-company="{{ $defaultCompany?->name }}" data-default-code="{{ $defaultCompany?->code }}" data-default-address="{{ $defaultLetterheadAddress }}" data-default-email="{{ $defaultCompany?->email }}" data-default-website="{{ data_get($defaultCompany?->settings, 'website') }}" data-default-logo="{{ $defaultCompany?->logo_path ? asset($defaultCompany->logo_path) : '' }}"><option value="">— Select an employee —</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" data-name="{{ $employee->full_name }}" data-number="{{ $employee->employee_number }}" data-position="{{ $employee->position_name }}" data-salary="{{ $employee->monthly_salary ?? $employee->base_salary }}" data-fortnightly-salary="{{ $employee->base_salary }}" data-company="{{ $employee->company?->name }}" data-code="{{ $employee->company?->code }}" data-address="{{ $employee->company?->address }}" data-email="{{ $employee->company?->email }}" data-website="{{ data_get($employee->company?->settings, 'website') }}" data-logo="{{ $employee->company?->logo_path ? asset($employee->company->logo_path) : '' }}">{{ $employee->employee_number }} — {{ $employee->full_name }}</option>@endforeach</select></div></section>
<section class="letter-card"><div class="letter-head">② &nbsp;Letter Type<small>Select a saved letter template</small></div><div class="letter-content"><label class="letter-label" for="letter-template">Template</label><select class="form-select" id="letter-template" name="template_id" required>@foreach($templates as $template)<option value="{{ $template->id }}" data-name="{{ $template->name }}" data-title="{{ $template->title }}" @selected($template->name === 'General Memo')>{{ $template->name }}</option>@endforeach</select><input type="hidden" name="letter_type" id="letter_type" value="Memo" required></div></section>
<section class="letter-card"><div class="letter-head">③ &nbsp;Letter Details<small>Date and editable content</small></div><div class="letter-content"><label class="letter-label" for="date_issued">Letter date</label><input class="form-control mb-2" id="date_issued" name="date_issued" type="date" value="{{ now()->toDateString() }}" required><label class="letter-label" for="body">Letter content</label><textarea class="form-control" id="body" name="body" required></textarea><div class="letter-token-help">Available: {employee_name}, {employee_number}, {position}, {salary}, {company_name}, {fortnightly_salary}, {date}, {signatory_name}, {signatory_title}</div></div></section>
<section class="letter-card"><div class="letter-head">⑤ &nbsp;Actions</div><div class="letter-content letter-buttons"><button class="btn btn-primary" type="submit"><i class="fas fa-file-pdf me-1"></i>Issue &amp; Save PDF</button></div></section></div>
<section class="letter-preview-card"><div class="letter-preview-top">Live Preview <small style="font-weight:400;color:#64748b">Click text to edit; click the blank recipient area to add an address.</small></div><div class="letter-paper-wrap"><div class="letter-paper"><div class="preview-letterhead"><img id="preview-logo" class="preview-letterhead-logo" alt="" hidden><div class="preview-letterhead-info"><div id="preview-address" class="preview-contact" contenteditable="true" role="textbox" aria-label="Letterhead address"></div></div></div><div id="preview-date" class="preview-date"></div><div class="preview-address recipient-address" id="preview-recipient-address" contenteditable="true" role="textbox" aria-label="Recipient address" aria-multiline="true"></div><div class="preview-address" id="preview-recipient" contenteditable="true" role="textbox" aria-label="Salutation">To Whom It May Concern;</div><div class="preview-subject" id="preview-subject" contenteditable="true" role="textbox" aria-label="Subject">RE: &nbsp;&nbsp;&nbsp; GENERAL MEMO</div><div id="preview-body" class="preview-body" contenteditable="true" role="textbox" aria-label="Letter body" aria-multiline="true"></div><div class="paper-sign" id="preview-sign">Yours sincerely,<strong id="preview-sign-company"></strong><span class="letter-signature-line"></span><strong id="preview-sign-name" contenteditable="true" role="textbox" aria-label="Signatory name"></strong><span id="preview-sign-title" contenteditable="true" role="textbox" aria-label="Signatory title"></span></div><div class="preview-letter-footer" id="preview-footer"></div></div></div><div class="letter-content" style="display:grid;grid-template-columns:1fr 1fr;gap:10px"><div><label class="letter-label" for="signatory_name">Signatory name</label><input class="form-control" id="signatory_name" name="signatory_name" placeholder="Full name of the signatory" required></div><div><label class="letter-label" for="signatory_title">Job title</label><input class="form-control" id="signatory_title" name="signatory_title" placeholder="e.g. Human Resources Manager"></div></div></section></div></form>
<script>
(()=>{
    const $ = id => document.getElementById(id);
    const type = $('letter_type'), body = $('body'), emp = $('employee_id'), date = $('date_issued'), templateSelect = $('letter-template');
    const templates = @json($templates->keyBy('id')->map(fn ($template) => ['name' => $template->name, 'content' => $template->content]));
    const typeByTemplate = {
        'General Memo': 'Memo', 'Employment Confirmation': 'Employment', 'Salary Confirmation': 'Salary',
        'Bank / Loan Support': 'Bank / Loan', 'Visa / Travel Support': 'Visa / Travel', 'Custom Letter': 'Custom Letter'
    };
    const companyNames = {'LKE-POM':'Larkin Enterprises Limited','LKE-LAE':'Larkin Enterprises Limited','YJS-POM':'Yellow Jacket Security Limited','YJS-LAE':'Yellow Jacket Security Limited','PARA':'Paragon Tech Limited','ADF':'Larkin Enterprises t/a Ad Focus','WAVE':'Wave Restaurant','CARO':"Caroline's Diner",'HYVE':'Hyve'};
    const displayCompanyName = (code, name) => /ad\s*focus/i.test(name || '') ? 'Larkin Enterprises t/a Ad Focus' : companyNames[code] || (/larkin/i.test(name || '') ? 'Larkin Enterprises Limited' : /yellow jacket/i.test(name || '') ? 'Yellow Jacket Security Limited' : name || '');
    const defaults = {
        Memo: 'This memo is issued to {employee_name} (Employee No. {employee_number}).\n\nThis memorandum is provided for official communication and record purposes.\n\nPlease be guided accordingly.',
        Employment: '',
        Salary: 'This is to certify that {employee_name} is employed as {position} with a monthly salary of {salary}. This letter is issued upon the employee’s request for whatever lawful purpose it may serve.',
        'Bank / Loan': 'This letter confirms that {employee_name} (Employee No. {employee_number}) is employed by our company as {position}. This is issued upon the employee’s request in support of a bank or loan application.',
        'Visa / Travel': 'This is to certify that {employee_name} (Employee No. {employee_number}) is employed by our company as {position}. This certification is issued in support of the employee’s visa or travel requirements.',
        'Custom Letter': ''
    };
    let manuallyEdited = false;
    const formatDate = value => value ? new Date(value + 'T00:00:00').toLocaleDateString('en-AU', {day:'numeric', month:'long', year:'numeric'}) : '';
    const escapeHtml = value => value.replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
    function fill(value) {
        const option = emp.selectedOptions[0];
        const values = {
            employee_name: option?.dataset.name || '[Employee name]', employee_number: option?.dataset.number || '[Employee number]',
            position: option?.dataset.position || '[Position]', salary: option?.dataset.salary || '[Salary]',
            fortnightly_salary: option?.dataset.fortnightlySalary || '[Fortnightly gross salary]',
            company_name: displayCompanyName(option?.dataset.code, option?.dataset.company) || '[Company name]', date: formatDate(date.value),
            signatory_name: $('signatory_name').value || '[Signatory name]',
            signatory_title: $('signatory_title').value || '[Title]'
        };
        return Object.entries(values).reduce((text, [key, replacement]) => text.replaceAll('{' + key + '}', replacement), value || '');
    }
    function render(reset = {}) {
        let text = fill(body.value);
        text = text.replace(/\n*(?:Yours sincerely,?|Sincerely,)[\s\S]*$/i, '').trimEnd();
        const employmentHeading = type.value === 'Employment' && text.startsWith('**CONFIRMATION OF EMPLOYMENT**');
        if (employmentHeading) text = text.replace(/^\*\*CONFIRMATION OF EMPLOYMENT\*\*\n*/, '');
        text = escapeHtml(text);
        $('preview-body').innerHTML = text.replace(/\*\*(.+?)\*\*/gs, '<strong>$1</strong>').replace(/\n/g, '<br>');
        $('preview-date').textContent = formatDate(date.value);
        const company = emp.selectedOptions[0];
        const displayCompany = displayCompanyName(company?.dataset.code, company?.dataset.company) || displayCompanyName('', emp.dataset.defaultCompany);
        const logo = $('preview-logo');
        const logoUrl = company?.dataset.logo || emp.dataset.defaultLogo || '';
        logo.hidden = !logoUrl;
        if (logoUrl) logo.src = logoUrl;
        const companyAddress = emp.dataset.defaultAddress || company?.dataset.address || '';
        const address = reset.company || !$('letterhead_address').value ? companyAddress : $('letterhead_address').value;
        $('preview-address').textContent = address;
        $('letterhead_address').value = address;
        const templateSubject = templateSelect.selectedOptions[0]?.dataset.title || templateSelect.selectedOptions[0]?.dataset.name || 'GENERAL MEMO';
        const subject = reset.template || !$('letter_subject').value ? templateSubject : $('letter_subject').value;
        $('preview-subject').innerHTML = 'RE: &nbsp;&nbsp;&nbsp; ' + escapeHtml(subject.toUpperCase());
        $('letter_subject').value = subject;
        const recipientAddress = $('recipient_address').value;
        const recipientLines = recipientAddress.split(/\r?\n/).filter(Boolean);
        $('preview-recipient-address').innerHTML = recipientLines.map((line, index) => index < 2 ? '<strong>' + escapeHtml(line) + '</strong>' : escapeHtml(line)).join('<br>');
        $('recipient_address').value = recipientAddress;
        const recipient = $('addressed_to').value || 'To Whom It May Concern';
        $('preview-recipient').textContent = recipient.replace(/;\s*$/, '') + ';';
        $('addressed_to').value = recipient.replace(/;\s*$/, '');
        $('preview-sign').hidden = false;
        $('preview-sign-name').textContent = $('signatory_name').value;
        $('preview-sign-title').textContent = $('signatory_title').value || '';
        $('preview-sign-company').textContent = displayCompany;
        const companyCode = company?.dataset.code || emp.dataset.defaultCode;
        const footerEmail = ['LKE-POM', 'LKE-LAE'].includes(companyCode) ? 'info@larkinpng.com' : (company?.dataset.email || emp.dataset.defaultEmail);
        $('preview-footer').textContent = [company?.dataset.website || emp.dataset.defaultWebsite, footerEmail].filter(Boolean).join(' | ');
    }
    function selectTemplate() {
        const template = templates[templateSelect.value];
        type.value = typeByTemplate[template?.name] || 'Custom Letter';
        if (!manuallyEdited) body.value = template?.content || defaults[type.value] || '';
        render({template: true, company: true});
    }
    templateSelect.addEventListener('change', () => {manuallyEdited = false; selectTemplate();});
    emp.addEventListener('change', () => render({company: true}));
    body.addEventListener('input', () => {manuallyEdited = true; render();});
    $('preview-body').addEventListener('input', () => { body.value = $('preview-body').innerText; manuallyEdited = true; });
    $('preview-subject').addEventListener('input', () => { $('letter_subject').value = $('preview-subject').innerText.replace(/^\s*RE:\s*/i, '').trim(); });
    $('preview-recipient-address').addEventListener('input', () => { $('recipient_address').value = $('preview-recipient-address').innerText.trim(); });
    $('preview-recipient').addEventListener('input', () => { $('addressed_to').value = $('preview-recipient').innerText.trim().replace(/;\s*$/, ''); });
    $('preview-address').addEventListener('input', () => { $('letterhead_address').value = $('preview-address').innerText.trim(); });
    $('preview-sign-name').addEventListener('input', () => { $('signatory_name').value = $('preview-sign-name').innerText.trim(); });
    $('preview-sign-title').addEventListener('input', () => { $('signatory_title').value = $('preview-sign-title').innerText.trim(); });
    document.querySelectorAll('#employee_id,#date_issued,#signatory_name,#signatory_title').forEach(input => input.addEventListener('input', render));
    selectTemplate();
})();
</script>
@endsection


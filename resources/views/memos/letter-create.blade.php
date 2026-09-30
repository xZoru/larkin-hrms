@extends('layouts.app')
@section('content')
<style>
.letter-workspace{display:grid;grid-template-columns:315px minmax(0,1fr);gap:16px;color:#18324a}.letter-left{display:grid;gap:14px;align-content:start}.letter-card,.letter-preview-card{overflow:hidden;border:1px solid #c7e8f4;border-radius:11px;background:#fff;box-shadow:0 3px 12px #2030400b}.letter-head{padding:12px 15px;background:#eff9fc;border-bottom:1px solid #c7e8f4;color:#0876ce;font-weight:700;font-size:13px}.letter-head small{display:block;margin:2px 0 0 34px;color:#a56767;font-size:10px;font-weight:400}.letter-content{padding:15px}.letter-label{display:block;margin:0 0 5px;color:#a14e4e;font-size:10px;text-transform:uppercase;letter-spacing:.4px}.letter-content .form-control,.letter-content .form-select{border-color:#b9e4f4;background:#f2fafc;color:#176cb0;font-size:12px}.letter-content textarea{min-height:110px}.letter-options{display:grid;grid-template-columns:1fr 1fr;gap:8px}.letter-type{padding:10px;border:1px solid #b9e4f4;border-radius:8px;background:#f3fafc;color:#0876ce;font-size:11px;cursor:pointer}.letter-type.active{outline:2px solid #168bd0;background:#e9f7fc}.letter-preview-card{grid-column:2;grid-row:1 / span 3;min-height:540px}.letter-preview-top{padding:13px;background:#eff9fc;border-bottom:1px solid #b9e4f4;color:#0876ce;font-size:13px;font-weight:700}.letter-paper-wrap{display:grid;place-items:center;min-height:500px;padding:22px;background:#d2d6dc}.letter-paper{box-sizing:border-box;width:min(100%,595px);min-height:470px;padding:58px 54px;background:white;color:#222;font-family:Georgia,serif;box-shadow:0 3px 12px #0002;white-space:pre-wrap;font-size:13px;line-height:1.7}.letter-paper .paper-company{text-align:center;font-family:Arial,sans-serif;font-weight:bold;margin-bottom:35px}.letter-paper .paper-date{text-align:right;margin-bottom:30px}.letter-paper .paper-sign{margin-top:38px}.letter-buttons{display:flex;gap:8px}.letter-buttons .btn{font-size:12px}.letter-token-help{color:#728197;font-size:10px;margin-top:6px}@media(max-width:850px){.letter-workspace{grid-template-columns:1fr}.letter-preview-card{grid-column:auto;grid-row:auto}.letter-paper-wrap{min-height:400px}.letter-paper{padding:35px 28px}}
.letter-employment-heading{margin:30px 0 36px;padding:8px 0;text-align:center;font-weight:700;letter-spacing:1px;line-height:1.5}.letter-signature-line{display:block;margin:16px 0 5px;font-family:Arial,sans-serif;letter-spacing:1px}#preview-sign-title{display:block}
.preview-letterhead{display:flex;align-items:center;gap:14px;margin-bottom:12px;font-family:Arial,sans-serif}.preview-letterhead-logo{width:76px;max-height:68px;object-fit:contain}.preview-letterhead-info{min-width:0}.preview-letterhead-info strong{display:block;margin-bottom:5px;color:#651b1b;font-size:15px}.preview-contact{color:#244b9b;font-family:Arial,sans-serif;font-size:10px;line-height:1.5}.preview-contact-icon{display:inline-block;width:15px;color:#c52d36}.preview-divider{height:5px;margin:12px 0 20px;border-top:3px solid #171717;border-bottom:1px solid #bd272d}.preview-reference{font-size:10px;line-height:1.8;margin-bottom:26px}.preview-date{text-align:left!important;margin:0!important}
.letter-workspace{color:#1a1f36}.letter-card,.letter-preview-card{border-color:#e5e7eb;border-radius:9px;box-shadow:0 1px 3px rgba(0,0,0,.06)}.letter-head,.letter-preview-top{background:#f8fafc;border-color:#e5e7eb;color:#1a1f36}.letter-head small,.letter-preview-top small{color:#64748b!important}.letter-content .letter-label{color:#64748b}.letter-content .form-control,.letter-content .form-select{border-color:#d7dce5;background:#fff;color:#1f2937}.letter-content .form-control:focus,.letter-content .form-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}.letter-type{border-color:#d7dce5;background:#fff;color:#334155}.letter-type:hover{border-color:#a5b4fc;background:#f8faff;color:#4338ca}.letter-type.active{outline:2px solid #6366f1;background:#eef2ff;color:#4338ca}.letter-buttons .btn-primary{border-color:#6366f1;background:#6366f1}.letter-buttons .btn-primary:hover{border-color:#4f46e5;background:#4f46e5}.letter-token-help{color:#94a3b8}.letter-preview-card .letter-paper-wrap{background:#e5e7eb}
</style>
<form method="POST" action="{{ route('memos.letters.store') }}" id="letter-form">@csrf
<div class="letter-workspace"><div class="letter-left"><div class="d-flex justify-content-between align-items-center"><h1 style="font-size:20px;margin:0">Employee Letters</h1><a href="{{ route('memos.templates.index') }}" class="btn btn-sm btn-outline-secondary">Edit templates</a></div>
<section class="letter-card"><div class="letter-head">① &nbsp;Select Employee<small>Choose the staff member this letter is for</small></div><div class="letter-content"><label class="letter-label" for="employee_id">Employee</label><select class="form-select" id="employee_id" name="employee_id" required data-default-company="{{ $defaultCompany?->name }}" data-default-address="{{ $defaultCompany?->address }}" data-default-email="{{ $defaultCompany?->email }}" data-default-website="{{ data_get($defaultCompany?->settings, 'website') }}" data-default-logo="{{ $defaultCompany?->logo_path ? asset($defaultCompany->logo_path) : '' }}"><option value="">— Select an employee —</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" data-name="{{ $employee->full_name }}" data-number="{{ $employee->employee_number }}" data-position="{{ $employee->position_name }}" data-salary="{{ $employee->monthly_salary ?? $employee->base_salary }}" data-fortnightly-salary="{{ $employee->base_salary }}" data-company="{{ $employee->company?->name }}" data-address="{{ $employee->company?->address }}" data-email="{{ $employee->company?->email }}" data-website="{{ data_get($employee->company?->settings, 'website') }}" data-logo="{{ $employee->company?->logo_path ? asset($employee->company->logo_path) : '' }}">{{ $employee->employee_number }} — {{ $employee->full_name }}</option>@endforeach</select></div></section>
<section class="letter-card"><div class="letter-head">② &nbsp;Letter Type<small>Select the purpose of this letter</small></div><div class="letter-content"><div class="letter-options">@foreach(['Memo','Employment','Salary','Bank / Loan','Visa / Travel','Custom Letter'] as $type)<button class="letter-type" type="button" data-type="{{ $type }}">{{ $type }}</button>@endforeach</div><input type="hidden" name="letter_type" id="letter_type" value="Memo" required></div></section>
<section class="letter-card"><div class="letter-head">③ &nbsp;Letter Details<small>Date and editable content</small></div><div class="letter-content"><label class="letter-label" for="date_issued">Letter date</label><input class="form-control mb-2" id="date_issued" name="date_issued" type="date" value="{{ now()->toDateString() }}" required><label class="letter-label" for="body">Letter content</label><textarea class="form-control" id="body" name="body" required></textarea><div class="letter-token-help">Available: {employee_name}, {employee_number}, {position}, {salary}, {company_name}, {fortnightly_salary}, {date}, {signatory_name}, {signatory_title}</div></div></section>
<section class="letter-card"><div class="letter-head">⑤ &nbsp;Actions</div><div class="letter-content letter-buttons"><button class="btn btn-primary" type="submit"><i class="fas fa-file-pdf me-1"></i>Issue &amp; Save PDF</button></div></section></div>
<section class="letter-preview-card"><div class="letter-preview-top">◉ &nbsp; Live Preview<small style="display:block;margin:3px 0 0 25px;color:#a56767;font-weight:400">Preview updates as you edit</small></div><div class="letter-paper-wrap"><div class="letter-paper"><div class="preview-letterhead"><img id="preview-logo" class="preview-letterhead-logo" alt="" hidden><div class="preview-letterhead-info"><strong id="preview-company"></strong><div id="preview-address" class="preview-contact" hidden><span class="preview-contact-icon">&#9675;</span> <span></span></div><div id="preview-email" class="preview-contact" hidden><span class="preview-contact-icon">&#9993;</span> <span></span></div><div id="preview-website" class="preview-contact" hidden><span class="preview-contact-icon">&#9678;</span> <span></span></div></div></div><div class="preview-divider"></div><div class="preview-reference">Ref: <span>New letter</span><div id="preview-date" class="preview-date"></div></div><div id="preview-employment-heading" class="letter-employment-heading" hidden></div><div id="preview-body"></div><div class="paper-sign" id="preview-sign">Yours sincerely,<br><br><span class="letter-signature-line">____________________________</span><strong id="preview-sign-name"></strong><span id="preview-sign-title"></span><strong id="preview-sign-company"></strong></div></div></div><div class="letter-content" style="display:grid;grid-template-columns:1fr 1fr;gap:10px"><div><label class="letter-label" for="signatory_name">Signatory name</label><input class="form-control" id="signatory_name" name="signatory_name" placeholder="Full name of the signatory" required></div><div><label class="letter-label" for="signatory_title">Job title</label><input class="form-control" id="signatory_title" name="signatory_title" placeholder="e.g. Human Resources Manager"></div></div></section></div></form>
<script>
(()=>{
    const $ = id => document.getElementById(id);
    const type = $('letter_type'), body = $('body'), emp = $('employee_id'), date = $('date_issued');
    const templates = @json($templates->keyBy('name')->map->content);
    const templateNames = {
        Memo: 'General Memo',
        Employment: 'Employment Confirmation',
        Salary: 'Salary Confirmation',
        'Bank / Loan': 'Bank / Loan Support',
        'Visa / Travel': 'Visa / Travel Support',
        'Custom Letter': 'Custom Letter'
    };
    const defaults = {
        Memo: 'This memo is issued to {employee_name} (Employee No. {employee_number}).\n\nThis memorandum is provided for official communication and record purposes.\n\nPlease be guided accordingly.',
        Employment: '',
        Salary: 'This is to certify that {employee_name} is employed as {position} with a monthly salary of {salary}. This letter is issued upon the employee’s request for whatever lawful purpose it may serve.',
        'Bank / Loan': 'This letter confirms that {employee_name} (Employee No. {employee_number}) is employed by our company as {position}. This is issued upon the employee’s request in support of a bank or loan application.',
        'Visa / Travel': 'This is to certify that {employee_name} (Employee No. {employee_number}) is employed by our company as {position}. This certification is issued in support of the employee’s visa or travel requirements.',
        'Custom Letter': ''
    };
    let manuallyEdited = false;
    const formatDate = value => value ? new Date(value + 'T00:00:00').toLocaleDateString(undefined, {year:'numeric', month:'long', day:'numeric'}) : '';
    const escapeHtml = value => value.replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
    function fill(value) {
        const option = emp.selectedOptions[0];
        const values = {
            employee_name: option?.dataset.name || '[Employee name]', employee_number: option?.dataset.number || '[Employee number]',
            position: option?.dataset.position || '[Position]', salary: option?.dataset.salary || '[Salary]',
            fortnightly_salary: option?.dataset.fortnightlySalary || '[Fortnightly gross salary]',
            company_name: option?.dataset.company || '[Company name]', date: formatDate(date.value),
            signatory_name: $('signatory_name').value || '[Signatory name]',
            signatory_title: $('signatory_title').value || '[Title]'
        };
        return Object.entries(values).reduce((text, [key, replacement]) => text.replaceAll('{' + key + '}', replacement), value || '');
    }
    function render() {
        let text = fill(body.value);
        const titles = {
            Employment: 'CONFIRMATION OF EMPLOYMENT',
            Salary: 'CONFIRMATION OF SALARY',
            'Bank / Loan': 'LETTER OF CONFIRMATION FOR BANKING PURPOSES',
            'Visa / Travel': 'LETTER OF SUPPORT FOR VISA APPLICATION',
            'Custom Letter': 'CONFIRMATION LETTER'
        };
        const employmentHeading = type.value === 'Employment' && text.startsWith('**CONFIRMATION OF EMPLOYMENT**');
        $('preview-employment-heading').hidden = !titles[type.value];
        $('preview-employment-heading').textContent = titles[type.value] || '';
        if (employmentHeading) text = text.replace(/^\*\*CONFIRMATION OF EMPLOYMENT\*\*\n*/, '');
        text = escapeHtml(text);
        $('preview-body').innerHTML = text.replace(/\*\*(.+?)\*\*/gs, '<strong>$1</strong>').replace(/\n/g, '<br>');
        $('preview-date').textContent = formatDate(date.value);
        const company = emp.selectedOptions[0];
        $('preview-company').textContent = company?.dataset.company || emp.dataset.defaultCompany || '';
        const logo = $('preview-logo');
        const logoUrl = company?.dataset.logo || emp.dataset.defaultLogo || '';
        logo.hidden = !logoUrl;
        if (logoUrl) logo.src = logoUrl;
        [['preview-address', company?.dataset.address || emp.dataset.defaultAddress], ['preview-email', company?.dataset.email || emp.dataset.defaultEmail], ['preview-website', company?.dataset.website || emp.dataset.defaultWebsite]].forEach(([id, value]) => {
            const line = $(id);
            line.hidden = !value;
            line.lastElementChild.textContent = value || '';
        });
        const hasClosing = /(?:Yours sincerely|Sincerely),/i.test(body.value);
        $('preview-sign').hidden = type.value === 'Employment' || hasClosing;
        $('preview-sign-name').textContent = $('signatory_name').value;
        $('preview-sign-title').textContent = $('signatory_title').value || '';
        $('preview-sign-company').textContent = company?.dataset.company || emp.dataset.defaultCompany || '';
    }
    function selectType(value) {
        type.value = value;
        document.querySelectorAll('.letter-type').forEach(button => button.classList.toggle('active', button.dataset.type === value));
        const templateName = templateNames[value];
        if (!manuallyEdited) body.value = (templateName && templates[templateName]) || defaults[value] || '';
        render();
    }
    document.querySelectorAll('.letter-type').forEach(button => button.addEventListener('click', () => {manuallyEdited = false; selectType(button.dataset.type);}));
    body.addEventListener('input', () => {manuallyEdited = true; render();});
    document.querySelectorAll('#employee_id,#date_issued,#signatory_name,#signatory_title').forEach(input => input.addEventListener('input', render));
    selectType('Memo');
})();
</script>
@endsection


@extends('layouts.app')

@section('content')
<style>
    .memo-form-page { max-width:1120px; margin:0 auto; color:#1a1f36; }
    .memo-form-hero { display:flex; justify-content:space-between; align-items:center; gap:16px; padding:20px 24px; margin-bottom:18px; color:white; border-radius:10px; background:linear-gradient(135deg,#1a1f36 0%,#2d3555 100%); }
    .memo-form-title { display:flex; align-items:center; gap:13px; }
    .memo-form-title .hero-icon { display:flex; width:42px; height:42px; align-items:center; justify-content:center; border-radius:10px; color:#c7d2fe; background:rgba(255,255,255,.12); font-size:17px; }
    .memo-form-title h1 { margin:0 0 4px; font-size:20px; font-weight:700; }
    .memo-form-title p { margin:0; color:#c4cad8; font-size:12px; }
    .memo-back { padding:8px 12px; border:1px solid rgba(255,255,255,.28); border-radius:6px; color:white; font-size:12px; text-decoration:none; white-space:nowrap; }
    .memo-back:hover { background:rgba(255,255,255,.1); color:white; }
    .memo-form-card { overflow:hidden; border:1px solid #e5e7eb; border-radius:9px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    .memo-form-intro { display:flex; gap:12px; padding:16px 20px; border-bottom:1px solid #e5e7eb; background:#f8fafc; }
    .memo-form-intro i { margin-top:2px; color:#6366f1; }
    .memo-form-intro strong { display:block; color:#334155; font-size:13px; }
    .memo-form-intro span { display:block; margin-top:3px; color:#64748b; font-size:12px; }
    .memo-form-body { padding:22px; }
    .memo-section-title { display:flex; align-items:center; gap:9px; margin:0 0 16px; color:#1a1f36; font-size:13px; font-weight:700; }
    .memo-section-title .section-icon { display:flex; width:27px; height:27px; align-items:center; justify-content:center; border-radius:7px; color:#4f46e5; background:#eef2ff; font-size:11px; }
    .memo-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:15px 18px; }
    .memo-field.full { grid-column:1/-1; }
    .memo-field label { display:block; margin:0 0 6px; color:#475569; font-size:12px; font-weight:600; }
    .memo-field label .required { color:#dc2626; }
    .memo-field .form-control, .memo-field .form-select { min-height:40px; border-color:#d7dce5; border-radius:6px; color:#1f2937; font-size:13px; box-shadow:none; }
    .memo-field textarea.form-control { min-height:96px; resize:vertical; }
    .memo-field .form-control:focus, .memo-field .form-select:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.12); }
    .memo-field .hint { margin-top:5px; color:#94a3b8; font-size:11px; }
    .memo-divider { margin:21px 0; border:0; border-top:1px solid #edf0f4; }
    .template-detail-box { padding:16px; border:1px solid #e0e7ff; border-radius:8px; background:#f8faff; }
    .template-detail-box .memo-section-title { margin-bottom:13px; }
    .memo-form-actions { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:15px 22px; border-top:1px solid #e5e7eb; background:#fafbfc; }
    .memo-form-actions .action-note { color:#94a3b8; font-size:11px; }
    .memo-submit { padding:9px 16px; border:0; border-radius:6px; color:#fff; background:#4f46e5; font-size:12px; font-weight:700; }
    .memo-submit:hover { background:#4338ca; }
    .memo-cancel { padding:8px 14px; border:1px solid #d7dce5; border-radius:6px; color:#475569; background:white; font-size:12px; text-decoration:none; }
    @media(max-width:650px) { .memo-fields { grid-template-columns:1fr; } .memo-field.full { grid-column:auto; } .memo-form-hero { align-items:flex-start; flex-direction:column; } .memo-form-body { padding:16px; } .memo-form-actions { align-items:flex-start; flex-direction:column; } }
</style>

<div class="memo-form-page">
    <div class="memo-form-hero">
        <div class="memo-form-title"><span class="hero-icon"><i class="fas fa-file-signature"></i></span><div><h1>Issue a Memo</h1><p>Prepare a memo for an employee and add it to their record.</p></div></div>
        <a href="{{ route('memos.index') }}" class="memo-back"><i class="fas fa-arrow-left me-2"></i>Memo history</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm"><strong>Please review the highlighted information.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="memo-form-card">
        <div class="memo-form-intro"><i class="fas fa-info-circle"></i><div><strong>Issuing creates the official memo record</strong><span>A PDF copy will be generated and available from memo history after submission.</span></div></div>
        <form method="POST" action="{{ route('memos.store') }}">
            @csrf
            <div class="memo-form-body">
                <h2 class="memo-section-title"><span class="section-icon"><i class="fas fa-user"></i></span>Employee and memo details</h2>
                <div class="memo-fields">
                    <div class="memo-field">
                        <label for="employee_id">Employee <span class="required">*</span></label>
                        <select class="form-select" id="employee_id" name="employee_id" required>
                            <option value="">Choose an employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->employee_number }} — {{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="memo-field">
                        <label for="memo_template_id">Memo type <span class="required">*</span></label>
                        <select class="form-select" id="memo_template_id" name="memo_template_id" required>
                            <option value="">Choose a memo type</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" data-content="{{ $template->content }}" @selected(old('memo_template_id') == $template->id)>{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="memo-field">
                        <label for="date_issued">Date issued <span class="required">*</span></label>
                        <input class="form-control" type="date" id="date_issued" name="date_issued" value="{{ old('date_issued', now()->toDateString()) }}" required>
                    </div>
                    <div class="memo-field">
                        <label for="effectivity_date">Effectivity date</label>
                        <input class="form-control" type="date" id="effectivity_date" name="effectivity_date" value="{{ old('effectivity_date') }}">
                    </div>
                    <div class="memo-field">
                        <label for="follow_up_date">Follow-up / response due date</label>
                        <input class="form-control" type="date" id="follow_up_date" name="follow_up_date" value="{{ old('follow_up_date') }}">
                        <div class="hint">Optional. Use for a response deadline or review date.</div>
                    </div>
                    <div class="memo-field">
                        <label for="purpose_template_id">Purpose template <span class="required">*</span></label>
                        <select class="form-select" id="purpose_template_id" name="purpose_template_id" required>
                            <option value="">Choose a purpose</option>
                            @foreach($purposeTemplates as $item)
                                <option value="{{ $item->id }}" @selected(old('purpose_template_id') == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach([1, 2, 3] as $reasonNumber)
                        <div class="memo-field">
                            <label for="reason_{{ $reasonNumber }}_template_id">Reason {{ $reasonNumber }} template</label>
                            <select class="form-select" id="reason_{{ $reasonNumber }}_template_id" name="reason_{{ $reasonNumber }}_template_id">
                                <option value="">No reason selected</option>
                                @foreach($reasonTemplates as $item)
                                    <option value="{{ $item->id }}" @selected(old('reason_'.$reasonNumber.'_template_id') == $item->id)>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <div class="memo-field">
                        <label for="decision_template_id">Decision template <span class="required">*</span></label>
                        <select class="form-select" id="decision_template_id" name="decision_template_id" required>
                            <option value="">Choose a decision</option>
                            @foreach($decisionTemplates as $item)
                                <option value="{{ $item->id }}" @selected(old('decision_template_id') == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="memo-divider">
                <h2 class="memo-section-title"><span class="section-icon"><i class="fas fa-align-left"></i></span>Memo content</h2>
                <div class="memo-fields">
                    <div class="memo-field full">
                        <label for="offense_description">Incident details <span class="required">*</span></label>
                        <textarea class="form-control" id="offense_description" name="offense_description" rows="4" required placeholder="Describe the reason or circumstances for this memo.">{{ old('offense_description') }}</textarea>
                    </div>
                    <div class="memo-field full">
                        <label for="action_taken">Action required / action taken <span class="required">*</span></label>
                        <textarea class="form-control" id="action_taken" name="action_taken" rows="4" required placeholder="State the action required, expectations, or action already taken.">{{ old('action_taken') }}</textarea>
                    </div>
                    <div class="memo-field full">
                        <label for="remarks">Additional remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Optional supporting notes.">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                    <div class="template-detail-box mt-4" id="template-fields" hidden>
                    <h2 class="memo-section-title"><span class="section-icon"><i class="fas fa-list-check"></i></span>Template-specific details</h2>
                    <div class="memo-fields" id="template-fields-list"></div>
                </div>
            </div>
            <div class="memo-form-actions">
                <span class="action-note"><i class="fas fa-lock me-1"></i> This memo is recorded under the selected employee.</span>
                <div class="d-flex gap-2">
                    <a href="{{ route('memos.index') }}" class="memo-cancel">Cancel</a>
                    <button type="submit" class="memo-submit" onclick="return confirm('Issue this memo and add it to the employee record?')"><i class="fas fa-paper-plane me-2"></i>Issue Memo</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('memo_template_id');
    const wrapper = document.getElementById('template-fields');
    const list = document.getElementById('template-fields-list');
    const oldValues = @json(old('template_values', []));
    const builtIn = new Set(['employee_name', 'employee_number', 'date_issued', 'offense', 'reason', 'allegations', 'action', 'issued_by', 'response_due_date']);
    const labels = { previous_warnings: 'Previous warnings', consequences: 'Consequences', suspension_date: 'Suspension effective date', duration: 'Duration', terms: 'Terms', termination_date: 'Termination effective date', final_pay: 'Final pay details', balances: 'Outstanding balances', days: 'Response period (days)', area_of_concern: 'Area of concern', goals: 'Improvement goals', timeline: 'Timeline', support: 'Support provided', review_dates: 'Review dates' };
    const render = () => {
        list.replaceChildren();
        const content = select.selectedOptions[0]?.dataset.content || '';
        const keys = [...content.matchAll(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g)].map(match => match[1]);
        const extras = [...new Set(keys)].filter(key => !builtIn.has(key));
        wrapper.hidden = extras.length === 0;
        extras.forEach(key => {
            const col = document.createElement('div');
            col.className = 'memo-field';
            const label = document.createElement('label');
            label.textContent = labels[key] || key.replaceAll('_', ' ').replace(/\b\w/g, letter => letter.toUpperCase());
            const input = document.createElement('textarea');
            input.className = 'form-control';
            input.name = `template_values[${key}]`;
            input.rows = 2;
            input.value = oldValues[key] || '';
            col.append(label, input);
            list.append(col);
        });
    };
    select.addEventListener('change', render);
    render();
});
</script>
@endsection

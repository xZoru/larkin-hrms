@extends('layouts.app')

@section('content')
<style>
    .template-page { color:#1a1f36; }
    .template-hero { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 24px; margin-bottom:18px; color:#fff; border-radius:10px; background:linear-gradient(135deg,#1a1f36 0%,#2d3555 100%); }
    .template-hero h1 { margin:0 0 4px; font-size:20px; font-weight:700; }
    .template-hero p { margin:0; color:#c4cad8; font-size:12px; }
    .template-hero .hero-icon { display:inline-flex; width:40px; height:40px; align-items:center; justify-content:center; margin-right:12px; border-radius:9px; color:#c7d2fe; background:rgba(255,255,255,.12); }
    .template-back { padding:8px 12px; border:1px solid rgba(255,255,255,.28); border-radius:6px; color:white; font-size:12px; text-decoration:none; white-space:nowrap; }
    .template-back:hover { color:white; background:rgba(255,255,255,.1); }
    .template-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; align-items:start; }
    .template-card { overflow:hidden; border:1px solid #e5e7eb; border-radius:9px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    .template-card-head { display:flex; align-items:center; gap:10px; padding:14px 16px; border-bottom:1px solid #e5e7eb; background:#f8fafc; }
    .template-card-head .cat-icon { display:flex; width:30px; height:30px; align-items:center; justify-content:center; border-radius:7px; color:#4f46e5; background:#eef2ff; font-size:12px; }
    .template-card-head h2 { margin:0; font-size:13px; font-weight:700; }
    .template-card-head span { display:block; margin-top:2px; color:#94a3b8; font-size:10px; }
    .template-add { padding:14px 16px; border-bottom:1px solid #edf0f4; }
    .template-add-title { margin-bottom:10px; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
    .template-list-wrap { overflow-x:auto; }
    .template-table { width:100%; min-width:540px; margin:0; border-collapse:collapse; font-size:11px; }
    .template-table th { padding:9px 11px; border-bottom:1px solid #e2e8f0; background:#f8fafc; color:#64748b; font-size:9px; font-weight:700; letter-spacing:.4px; text-align:left; text-transform:uppercase; }
    .template-table td { padding:11px; border-bottom:1px solid #f1f5f9; vertical-align:top; }
    .template-table tr:last-child td { border-bottom:0; }
    .template-name { color:#334155; font-size:11px; font-weight:700; }
    .template-preview { display:-webkit-box; max-width:210px; margin:0; overflow:hidden; color:#64748b; font-size:10px; line-height:1.45; white-space:pre-wrap; overflow-wrap:anywhere; -webkit-box-orient:vertical; -webkit-line-clamp:3; }
    .template-edit summary { display:inline-flex; align-items:center; gap:6px; color:#4f46e5; font-size:11px; font-weight:600; cursor:pointer; list-style:none; }
    .template-edit summary::-webkit-details-marker { display:none; }
    .template-edit summary::before { content:'+'; display:inline-flex; width:17px; height:17px; align-items:center; justify-content:center; border-radius:4px; background:#eef2ff; font-size:13px; }
    .template-edit[open] summary::before { content:'−'; }
    .template-edit .template-form { margin-top:12px; padding-top:12px; border-top:1px solid #edf0f4; }
    .template-badge { padding:3px 8px; border-radius:15px; font-size:9px; font-weight:700; text-transform:uppercase; }
    .template-badge.active { color:#166534; background:#dcfce7; }
    .template-badge.inactive { color:#64748b; background:#e2e8f0; }
    .template-form label { display:block; margin:0 0 4px; color:#64748b; font-size:10px; font-weight:600; }
    .template-form .form-control, .template-form .form-select { margin-bottom:9px; border-color:#d7dce5; border-radius:6px; font-size:12px; box-shadow:none; }
    .template-form .form-control:focus, .template-form .form-select:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
    .template-form textarea { min-height:76px; resize:vertical; }
    .template-form .btn { padding:6px 10px; border-radius:6px; font-size:11px; font-weight:600; }
    .template-empty { padding:17px 16px; color:#94a3b8; font-size:11px; text-align:center; }
    .saved-templates { margin-top:22px; overflow:hidden; border:1px solid #e5e7eb; border-radius:9px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    .saved-templates-head { padding:16px 18px; border-bottom:1px solid #e5e7eb; }
    .saved-templates-head h2 { margin:0; color:#1a1f36; font-size:14px; font-weight:700; }
    .saved-templates-head p { margin:4px 0 0; color:#94a3b8; font-size:11px; }
    .saved-category + .saved-category { border-top:1px solid #e5e7eb; }
    .saved-category-head { display:flex; align-items:center; gap:9px; padding:12px 16px; background:#f8fafc; }
    .saved-category-head i { color:#4f46e5; }
    .saved-category-head strong { color:#334155; font-size:12px; }
    .saved-category-head span { margin-left:auto; color:#94a3b8; font-size:10px; }
    @media(max-width:1000px) { .template-grid { grid-template-columns:1fr 1fr; } }
    @media(max-width:650px) { .template-grid { grid-template-columns:1fr; } .template-hero { align-items:flex-start; flex-direction:column; } }
</style>

<div class="template-page">
    <div class="template-hero">
        <div class="d-flex align-items-center"><span class="hero-icon"><i class="fas fa-layer-group"></i></span><div><h1>Memo Templates</h1><p>Manage the reusable purpose, reason, and decision choices for memo issuance.</p></div></div>
        <a href="{{ route('memos.index') }}" class="template-back"><i class="fas fa-arrow-left me-2"></i>Memo history</a>
    </div>

    @if(session('success'))<div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger border-0 shadow-sm"><strong>Please check the template details.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="template-grid">
        @foreach(['Purpose', 'Reason', 'Decision'] as $category)
            @php
                $icon = ['Purpose' => 'fa-bullseye', 'Reason' => 'fa-list-check', 'Decision' => 'fa-gavel'][$category];
                $help = ['Purpose' => 'Why the memo is being issued', 'Reason' => 'Reusable reason statements', 'Decision' => 'Outcome or action selected'][$category];
            @endphp
            <section class="template-card">
                <div class="template-card-head"><span class="cat-icon"><i class="fas {{ $icon }}"></i></span><div><h2>{{ $category }} templates</h2><span>{{ $help }}</span></div></div>
                <div class="template-add">
                    <div class="template-add-title">Add {{ strtolower($category) }} template</div>
                    <form class="template-form" method="POST" action="{{ route('memos.templates.store') }}">
                        @csrf
                        <input type="hidden" name="category" value="{{ $category }}">
                        <label for="new-{{ strtolower($category) }}-name">Template name</label>
                        <input id="new-{{ strtolower($category) }}-name" class="form-control" name="name" maxlength="255" required placeholder="e.g. Attendance concern">
                        <label for="new-{{ strtolower($category) }}-content">Text inserted into the memo</label>
                        <textarea id="new-{{ strtolower($category) }}-content" class="form-control" name="content" maxlength="10000" placeholder="Enter reusable text for this option (optional)"></textarea>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-plus me-1"></i>Add template</button>
                    </form>
                </div>
            </section>
        @endforeach
    </div>

    <section class="saved-templates">
        <div class="saved-templates-head"><h2><i class="fas fa-folder-open text-primary me-2"></i>Created Templates</h2><p>Review and edit the templates already in the library.</p></div>
        @foreach(['Purpose', 'Reason', 'Decision'] as $category)
            @php $icon = ['Purpose' => 'fa-bullseye', 'Reason' => 'fa-list-check', 'Decision' => 'fa-gavel'][$category]; @endphp
            <div class="saved-category">
                <div class="saved-category-head"><i class="fas {{ $icon }}"></i><strong>{{ $category }} templates</strong><span>{{ $templateGroups->get($category, collect())->count() }} template(s)</span></div>
                @if($templateGroups->get($category, collect())->isNotEmpty())
                    <div class="template-list-wrap">
                        <table class="template-table">
                            <thead><tr><th>Template Name</th><th>Template Text</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                @foreach($templateGroups->get($category, collect()) as $template)
                                    <tr>
                                        <td><span class="template-name">{{ $template->name }}</span></td>
                                        <td><span class="template-preview" title="{{ $template->content }}">{{ $template->content ?: 'No template text has been added.' }}</span></td>
                                        <td><span class="template-badge {{ $template->is_active ? 'active' : 'inactive' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span></td>
                                        <td>
                                            <details class="template-edit">
                                                <summary>Edit</summary>
                                                <form class="template-form" method="POST" action="{{ route('memos.templates.update', $template) }}">
                                                    @csrf @method('PUT')
                                                    <label for="name-{{ $template->id }}">Template name</label>
                                                    <input id="name-{{ $template->id }}" class="form-control" name="name" value="{{ $template->name }}" maxlength="255" required>
                                                    <label for="content-{{ $template->id }}">Text inserted into the memo</label>
                                                    <textarea id="content-{{ $template->id }}" class="form-control" name="content" maxlength="10000">{{ $template->content }}</textarea>
                                                    <label for="active-{{ $template->id }}">Availability</label>
                                                    <select id="active-{{ $template->id }}" class="form-select" name="is_active" required>
                                                        <option value="1" @selected($template->is_active)>Active</option>
                                                        <option value="0" @selected(!$template->is_active)>Inactive</option>
                                                    </select>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-save me-1"></i>Save changes</button>
                                                        <button class="btn btn-outline-danger" type="submit" form="delete-template-{{ $template->id }}" onclick="return confirm('Delete this template? This cannot be undone.')"><i class="fas fa-trash-alt me-1"></i>Delete</button>
                                                    </div>
                                                </form>
                                                <form id="delete-template-{{ $template->id }}" method="POST" action="{{ route('memos.templates.destroy', $template) }}" class="d-none">
                                                    @csrf @method('DELETE')
                                                </form>
                                            </details>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="template-empty">No {{ strtolower($category) }} templates yet.</div>
                @endif
            </div>
        @endforeach
    </section>
</div>
@endsection

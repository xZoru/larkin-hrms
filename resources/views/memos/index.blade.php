@extends('layouts.app')

@section('content')
<style>
    .memo-page { color: #1a1f36; }
    .memo-hero { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:22px 26px; margin-bottom:20px; color:#fff; border-radius:10px; background:linear-gradient(135deg,#1a1f36 0%,#2d3555 100%); }
    .memo-hero h1 { margin:0 0 5px; font-size:21px; font-weight:700; letter-spacing:.2px; }
    .memo-hero p { margin:0; color:#c4cad8; font-size:13px; }
    .memo-hero .hero-icon { display:inline-flex; width:42px; height:42px; align-items:center; justify-content:center; margin-right:14px; border-radius:10px; color:#c7d2fe; background:rgba(255,255,255,.12); font-size:18px; }
    .memo-hero .btn { background:#6366f1; border-color:#6366f1; border-radius:7px; padding:9px 15px; font-size:13px; font-weight:600; white-space:nowrap; }
    .memo-hero .btn:hover { background:#4f46e5; border-color:#4f46e5; }
    .memo-summary { display:flex; align-items:center; gap:12px; padding:15px 18px; margin-bottom:18px; border:1px solid #e5e7eb; border-radius:8px; background:#fff; }
    .memo-summary-icon { display:flex; width:38px; height:38px; align-items:center; justify-content:center; border-radius:8px; color:#4f46e5; background:#eef2ff; }
    .memo-summary-value { color:#1a1f36; font-size:19px; line-height:1.1; font-weight:700; }
    .memo-summary-label { margin-top:3px; color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:.45px; font-weight:600; }
    .memo-table-card { overflow:hidden; border:1px solid #e5e7eb; border-radius:9px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    .memo-table-head { display:flex; align-items:center; justify-content:space-between; padding:15px 18px; border-bottom:1px solid #e5e7eb; }
    .memo-table-head h2 { margin:0; color:#1a1f36; font-size:14px; font-weight:700; }
    .memo-table-head span { color:#94a3b8; font-size:12px; }
    .memo-table { width:100%; min-width:1180px; margin:0; font-size:12px; }
    .memo-table thead th { padding:11px 15px; border-bottom:1px solid #e2e8f0; background:#f8fafc; color:#64748b; font-size:10px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; white-space:nowrap; }
    .memo-table tbody td { padding:13px 15px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
    .memo-table tbody tr:last-child td { border-bottom:0; }
    .memo-table tbody tr:hover { background:#fafbff; }
    .memo-number { color:#4f46e5; font-weight:700; white-space:nowrap; }
    .memo-employee { color:#1f2937; font-weight:600; }
    .memo-sub { display:block; margin-top:2px; color:#94a3b8; font-size:11px; }
    .memo-type { display:inline-block; padding:4px 9px; border-radius:20px; background:#eef2ff; color:#4338ca; font-size:11px; font-weight:600; }
    .memo-date { color:#475569; white-space:nowrap; }
    .memo-user { color:#475569; }
    .memo-cell-text { display:block; max-width:190px; overflow:hidden; color:#475569; text-overflow:ellipsis; white-space:nowrap; }
    .memo-download { padding:5px 9px; border:1px solid #dbeafe; border-radius:6px; color:#2563eb; background:#eff6ff; font-size:11px; font-weight:600; text-decoration:none; white-space:nowrap; }
    .memo-download:hover { color:#1d4ed8; background:#dbeafe; }
    .memo-empty { padding:48px 16px!important; text-align:center; }
    .memo-empty i { display:block; margin-bottom:12px; color:#cbd5e1; font-size:30px; }
    .memo-empty strong { display:block; color:#334155; font-size:14px; }
    .memo-empty span { display:block; margin-top:4px; color:#94a3b8; font-size:12px; }
    .memo-pagination { padding:12px 16px; border-top:1px solid #f1f5f9; }
    @media(max-width:640px) { .memo-hero { align-items:flex-start; flex-direction:column; padding:18px; } .memo-table-head span { display:none; } }
</style>

<div class="memo-page">
    <div class="memo-hero">
        <div class="d-flex align-items-center">
            <span class="hero-icon"><i class="fas fa-file-signature"></i></span>
            <div><h1>Memo Issuance</h1><p>Create, issue, and review employee memos.</p></div>
        </div>
        <a href="{{ route('memos.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Issue Memo</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif

    <div class="memo-summary">
        <div class="memo-summary-icon"><i class="fas fa-folder-open"></i></div>
        <div><div class="memo-summary-value">{{ number_format($records->total()) }}</div><div class="memo-summary-label">Issued memos</div></div>
    </div>

    <div class="memo-table-card">
        <div class="memo-table-head"><h2><i class="fas fa-list-ul me-2 text-primary"></i>Memo history</h2><span>Latest issued memos for the selected company</span></div>
        <div class="table-responsive">
            <table class="memo-table">
                <thead><tr><th>Request #</th><th>Employee</th><th>Purpose</th><th>Reason 1</th><th>Reason 2</th><th>Reason 3</th><th>Effectivity Date</th><th>Decision</th><th>Created At</th><th>Issuer</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td><span class="memo-number">{{ $record->memo_number }}</span></td>
                            <td><span class="memo-employee">{{ $record->employee?->full_name ?? 'Employee unavailable' }}</span><span class="memo-sub">{{ $record->employee?->employee_number }}</span></td>
                            <td><span class="memo-cell-text" title="{{ $record->purpose }}">{{ $record->purpose ?: $record->template?->name ?? 'Memo' }}</span></td>
                            <td><span class="memo-cell-text" title="{{ $record->reason_1 }}">{{ $record->reason_1 ?: $record->offense_description ?: '—' }}</span></td>
                            <td><span class="memo-cell-text" title="{{ $record->reason_2 }}">{{ $record->reason_2 ?: $record->action_taken ?: '—' }}</span></td>
                            <td><span class="memo-cell-text" title="{{ $record->reason_3 }}">{{ $record->reason_3 ?: $record->remarks ?: '—' }}</span></td>
                            <td class="memo-date">{{ $record->effectivity_date?->format('d M Y') ?? '—' }}</td>
                            <td><span class="memo-cell-text" title="{{ $record->decision }}">{{ $record->decision ?: '—' }}</span></td>
                            <td class="memo-date">{{ $record->created_at?->format('d M Y, h:i A') }}</td>
                            <td class="memo-user">{{ $record->issuedBy?->name ?? '—' }}</td>
                            <td class="text-end"><a class="memo-download" href="{{ route('memos.document', $record) }}"><i class="fas fa-download me-1"></i>PDF</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="memo-empty"><i class="far fa-file-alt"></i><strong>No memos issued yet</strong><span>Issued memos will appear here for this company.</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())<div class="memo-pagination">{{ $records->links() }}</div>@endif
    </div>
</div>
@endsection

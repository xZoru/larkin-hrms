@php
    $companyId = auth()->user()->getCurrentCompanyId();
    $employees = App\Models\Employee::where('company_id', $companyId)->where('employee_type', 'Expatriate')->where('status', 'Active')->get();
    $expiringDocs = [];
    $today = \Carbon\Carbon::today();
    foreach ($employees as $employee) {
        foreach (['Visa' => 'visa_expiry', 'Passport' => 'passport_expiry'] as $label => $field) {
            if (!$employee->$field) continue;
            $expiry = \Carbon\Carbon::parse($employee->$field)->startOfDay();
            $days = (int) $today->diffInDays($expiry, false);
            if ($days >= 0 && $days < 60) $expiringDocs[] = (object)['employee'=>$employee, 'document'=>$label, 'expiry_date'=>$expiry, 'days'=>$days];
        }
    }
    usort($expiringDocs, fn($a,$b) => $a->days <=> $b->days);
    $birthdays = $employees->filter(fn($e) => $e->date_of_birth)->map(function($e) use ($today) {
        $next = $e->date_of_birth->copy()->year($today->year);
        if ($next->lt($today)) $next->addYear();
        $e->next_birthday = $next;
        $e->birthday_days = $today->diffInDays($next, false);
        return $e;
    })->sortBy('birthday_days');
    $todaysBirthdays = $birthdays->where('birthday_days', 0);
    $upcomingBirthdays = $birthdays->where('birthday_days', '>', 0)->take(8)->groupBy(fn($e) => $e->next_birthday->toDateString());
@endphp
<div class="expiry-layout mb-6">
  <section class="widget-box expiry-tracker">
    <div class="widget-header expiry-header"><div><i class="fas fa-passport text-indigo-500 mr-2"></i>Visa &amp; Passport Expiry Tracker <span class="text-xs text-gray-500 font-normal">{{ count($expiringDocs) }} within 60 days</span></div><button type="button" class="dashboard-button" id="export-expiries"><i class="fas fa-download mr-1"></i>Export</button></div>
    <div class="expiry-tools"><div class="expiry-tabs" role="tablist"><button class="expiry-tab active" data-filter="all">All Expiries</button><button class="expiry-tab" data-filter="Visa">Visas</button><button class="expiry-tab" data-filter="Passport">Passports</button></div><input id="expiry-search" type="search" class="expiry-search" placeholder="Search employee or document..." aria-label="Search expiries"></div>
    <div class="expiry-table-wrap"><table class="expiry-table"><thead><tr><th>Employee</th><th>Document</th><th>Expiry Date</th><th>Days Remaining</th><th>Actions</th></tr></thead><tbody id="expiry-rows">
    @forelse($expiringDocs as $doc) @php($employee = $doc->employee)
      <tr data-type="{{ $doc->document }}" data-search="{{ strtolower($employee->full_name.' '.$employee->employee_number.' '.$doc->document) }}"><td><div class="employee-cell">@if($employee->photo_url)<img src="{{ $employee->photo_url }}" class="employee-avatar" alt="">@else<div class="employee-avatar avatar-fallback">{{ strtoupper(substr($employee->full_name,0,1)) }}</div>@endif<div><a class="employee-name" href="{{ route('employees.show',$employee->id) }}">{{ $employee->full_name }}</a><div class="employee-number">{{ $employee->employee_number }}</div></div></div></td><td><span class="doc-pill">{{ $doc->document }}</span></td><td>{{ $doc->expiry_date->format('d M Y') }}</td><td><span class="days-badge {{ $doc->days < 30 ? 'badge-urgent' : 'badge-warning' }}">{{ $doc->days === 0 ? 'Today' : $doc->days.' days' }}</span></td><td><div class="action-cell"><a class="action-link" href="mailto:{{ $employee->email }}?subject={{ rawurlencode($doc->document.' expiry reminder') }}" @if(!$employee->email) aria-disabled="true" onclick="return false" @endif>Send Reminder</a><a class="action-link" href="{{ route('employees.show',$employee->id) }}">View Profile</a></div></td></tr>
    @empty <tr id="expiry-empty"><td colspan="5" class="empty-state">No visa or passport expiries within 60 days.</td></tr> @endforelse
    </tbody></table></div><div id="expiry-no-match" class="empty-state hidden">No matching documents.</div>
  </section>
  <section class="widget-box birthday-panel"><div class="widget-header"><i class="fas fa-birthday-cake text-pink-500 mr-2"></i>Expatriate Birthdays</div>
    <div class="birthday-today"><div class="birthday-subhead"><span>Today’s Birthdays</span><span class="celebration-badge">🎉 Celebrate</span></div>
    @forelse($todaysBirthdays as $employee)<div class="birthday-item today-item"><div><div class="employee-name">{{ $employee->full_name }}</div><div class="employee-number">{{ $employee->position_name ?? 'Expatriate' }}</div></div><a class="dashboard-button wish-button" href="mailto:{{ $employee->email }}?subject={{ rawurlencode('Happy Birthday!') }}" @if(!$employee->email) aria-disabled="true" onclick="return false" @endif>Send Wish</a></div>
    @empty<div class="birthday-empty">No birthdays today</div>@endforelse</div>
    <div class="birthday-upcoming"><div class="birthday-subhead">Upcoming Birthdays</div>
    @forelse($upcomingBirthdays as $date => $group)<div class="birthday-date-group"><div class="birthday-group-date">{{ \Carbon\Carbon::parse($date)->format('l, d M') }}</div>@foreach($group as $employee)<div class="birthday-item"><div><div class="employee-name">{{ $employee->full_name }}</div><div class="employee-number">{{ $employee->position_name ?? 'Expatriate' }}</div></div><span class="country-code">{{ $employee->country_code ?? $employee->nationality ?? 'EXP' }}</span></div>@endforeach</div>
    @empty<div class="birthday-empty">No upcoming birthdays</div>@endforelse</div>
  </section>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){const rows=[...document.querySelectorAll('#expiry-rows tr[data-type]')], tabs=[...document.querySelectorAll('.expiry-tab')], search=document.getElementById('expiry-search'), noMatch=document.getElementById('expiry-no-match');let filter='all';function apply(){let shown=0;rows.forEach(r=>{const okType=filter==='all'||r.dataset.type===filter, okText=r.dataset.search.includes(search.value.toLowerCase());r.classList.toggle('hidden',!(okType&&okText));if(okType&&okText)shown++});noMatch.classList.toggle('hidden',shown>0||rows.length===0)}tabs.forEach(t=>t.addEventListener('click',()=>{tabs.forEach(x=>x.classList.remove('active'));t.classList.add('active');filter=t.dataset.filter;apply()}));search.addEventListener('input',apply);document.getElementById('export-expiries').addEventListener('click',()=>{const visible=rows.filter(r=>!r.classList.contains('hidden'));const csv=[['Employee','Employee No.','Document','Expiry Date','Days Remaining'],...visible.map(r=>[r.querySelector('.employee-name').textContent.trim(),r.querySelector('.employee-number').textContent.trim(),r.dataset.type,r.cells[2].textContent.trim(),r.cells[3].textContent.trim()])].map(row=>row.map(v=>'"'+v.replaceAll('"','""')+'"').join(',')).join('\r\n');const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));a.download='visa-passport-expiries.csv';a.click();URL.revokeObjectURL(a.href)})});
</script>

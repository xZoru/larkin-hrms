@extends('layouts.app')

@section('content')
<div class="py-6 max-w-6xl mx-auto px-4">
    <h1 class="text-2xl font-semibold text-gray-900 mb-5">Generate Final Pay</h1>
    <form method="POST" action="{{ route('settlements.final-pay.store') }}" class="bg-white rounded-lg shadow border border-gray-200 p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        @csrf
        <div class="md:col-span-2"><label class="font-medium block mb-1">Employee</label><select id="employee_id" name="employee_id" class="w-full rounded border-gray-300" required><option value="">Select employee</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" data-start="{{ optional($employee->joining_date)->format('Y-m-d') }}" data-rate="{{ $employee->hourly_rate }}">{{ $employee->employee_number }} - {{ $employee->full_name }}</option>@endforeach</select></div>
        <div><label class="font-medium block mb-1">Date Commenced</label><input id="commenced_at" type="date" name="commenced_at" class="w-full rounded border-gray-300" required></div>
        <div><label class="font-medium block mb-1">End Date</label><input id="ended_at" type="date" name="ended_at" value="{{ now()->format('Y-m-d') }}" class="w-full rounded border-gray-300" required></div>
        <div><label class="font-medium block mb-1">Hours per day</label><input id="hours_per_day" type="number" step="0.01" min="0.01" name="hours_per_day" value="8" class="w-full rounded border-gray-300" required></div>
        <div><label class="font-medium block mb-1">Hourly rate</label><input id="hourly_rate" type="number" step="0.01" min="0" name="hourly_rate" class="w-full rounded border-gray-300" required></div>
        <div><label class="font-medium block mb-1">Months in service</label><input id="service_months" class="w-full rounded border-gray-200 bg-gray-50" readonly></div>
        <div><label class="font-medium block mb-1">Final pay</label><input id="amount" class="w-full rounded border-gray-200 bg-gray-50 font-semibold" readonly></div>
        <div><label class="font-medium block mb-1">Issuer name</label><input name="issuer_name" value="{{ old('issuer_name', $issuerName) }}" class="w-full rounded border-gray-300" required></div>
        <div><label class="font-medium block mb-1">Issuer position</label><input name="issuer_position" value="{{ old('issuer_position', $issuerPosition) }}" class="w-full rounded border-gray-300" required></div>
        <div class="md:col-span-2 flex justify-end"><button type="submit" style="background:#0891b2; color:#fff; padding:10px 20px; border:0; border-radius:6px; font-weight:600; cursor:pointer;">Generate PDF</button></div>
    </form>
</div>
<script>
const employee = document.getElementById('employee_id'), start = document.getElementById('commenced_at'), end = document.getElementById('ended_at'), rate = document.getElementById('hourly_rate'), hours = document.getElementById('hours_per_day');
function calculate(){ const days=(new Date(end.value)-new Date(start.value))/86400000, rawMonths=Math.max(0,days/30.333333), months=Math.floor(rawMonths*100)/100, amount=1.5*(+hours.value||0)*(+rate.value||0)*months; document.getElementById('service_months').value=months.toFixed(2); document.getElementById('amount').value='K '+amount.toFixed(2); }
employee.addEventListener('change',()=>{const option=employee.selectedOptions[0]; start.value=option.dataset.start||''; rate.value=option.dataset.rate||0; calculate();}); [start,end,rate,hours].forEach(el=>el.addEventListener('input',calculate));
</script>
@endsection

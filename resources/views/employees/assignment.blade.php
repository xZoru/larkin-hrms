@extends('layouts.app')

@section('content')
<div class="py-6"><div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-xl p-6">
        <h1 class="text-xl font-bold text-slate-800">Assign / Transfer Employee</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $employee->employee_number }} — {{ $employee->full_name }}</p>
        <form method="POST" action="{{ route('employees.assignments.store', $employee) }}" class="mt-6 space-y-4">@csrf
            <div><label class="block text-sm font-medium">Branch / Outstation</label><select name="branch_id" required class="mt-1 w-full rounded border-gray-300"><option value="">Select location</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->type }})</option>@endforeach</select></div>
            <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium">From date</label><input type="date" name="from_date" value="{{ old('from_date', now()->toDateString()) }}" required class="mt-1 w-full rounded border-gray-300"></div><div><label class="block text-sm font-medium">To date</label><input type="date" name="to_date" value="{{ old('to_date') }}" class="mt-1 w-full rounded border-gray-300"><p class="text-xs text-gray-500 mt-1">Leave empty for ongoing.</p></div></div>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_temporary" value="1"> <span class="text-sm">Temporary assignment</span></label>
            <div><label class="block text-sm font-medium">Notes</label><input name="notes" value="{{ old('notes') }}" class="mt-1 w-full rounded border-gray-300"></div>
            @if($errors->any())<div class="text-sm text-red-600">{{ $errors->first() }}</div>@endif
            <div class="flex gap-3"><button class="bg-indigo-600 text-white rounded px-4 py-2">Save assignment</button><a href="{{ route('employees.show', $employee) }}" class="px-4 py-2 text-gray-600">Cancel</a></div>
        </form>
    </div>
</div></div>
@endsection

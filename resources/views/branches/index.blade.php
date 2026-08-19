@extends('layouts.app')

@section('content')
<style>
    .branches-page-header {
        background: linear-gradient(135deg, #172554 0%, #312e81 100%);
        color: #ffffff;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 8px 20px rgba(30, 41, 59, 0.18);
    }
    .branches-page-header h1 {
        color: #ffffff;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.25;
        margin: 0;
    }
    .branches-page-header p {
        color: #dbeafe;
        font-size: 14px;
        margin: 8px 0 0;
    }
</style>
<div class="py-6"><div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="branches-page-header">
        <h1><i class="fas fa-map-marker-alt mr-2"></i>Branches & Outstations</h1>
        <p>Set up the locations for the current company. Employees can then be assigned or transferred with dated history.</p>
    </div>
    @if(session('success')) <div class="mb-4 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="mb-4 p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div> @endif
    <div class="bg-white rounded-xl shadow border p-5 mb-6">
        <h2 class="font-semibold mb-4">Add location</h2>
        <form method="POST" action="{{ route('branches.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">@csrf
            <input name="name" value="{{ old('name') }}" required placeholder="e.g. Kutubu Site" class="rounded border-gray-300">
            <select name="type" class="rounded border-gray-300"><option>Branch</option><option>Outstation</option><option>Project Site</option></select>
            <input name="code" value="{{ old('code') }}" placeholder="Optional code" class="rounded border-gray-300">
            <button class="rounded bg-indigo-600 text-white font-semibold px-4 py-2">Add location</button>
        </form>
        @error('name')<p class="text-red-600 text-sm mt-2">{{ $message }}</p>@enderror
    </div>
    <div class="bg-white rounded-xl shadow border overflow-hidden"><table class="w-full text-sm">
        <thead class="bg-slate-50 text-left"><tr><th class="p-3">Location</th><th class="p-3">Type</th><th class="p-3">Code</th><th class="p-3">Assignment records</th><th class="p-3"></th></tr></thead>
        <tbody>@forelse($branches as $branch)<tr class="border-t"><td class="p-3 font-medium">{{ $branch->name }}</td><td class="p-3">{{ $branch->type }}</td><td class="p-3">{{ $branch->code ?: '—' }}</td><td class="p-3">{{ $branch->assignments_count }}</td><td class="p-3 text-right">@if(!$branch->assignments_count)<form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Delete this location?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>@endif</td></tr>@empty<tr><td colspan="5" class="p-8 text-center text-gray-500">No branches or outstations have been added for this company.</td></tr>@endforelse</tbody>
    </table></div>
</div></div>
@endsection

@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tax Tables
        </h2>
        <a href="{{ route('tax-tables.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Add Tax Table
        </a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @php
            $nationalTables = $taxTables->where('employee_type', 'National')->sortBy('min_amount');
            $expatTables = $taxTables->where('employee_type', 'Expatriate')->sortBy('min_amount');
        @endphp

        <!-- National Tax Tables -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
            <div class="bg-blue-50 px-4 py-3 border-b border-blue-200">
                <h3 class="text-lg font-semibold text-blue-800">
                     National Employees Tax Tables
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Min Amount (K)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Max Amount (K)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tax Rate (%)</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Effective Date</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($nationalTables as $tax)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $tax->name ?? 'National Bracket' }}</td>
                            <td class="px-4 py-3 text-right">K {{ number_format($tax->min_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right">{{ $tax->max_amount ? 'K ' . number_format($tax->max_amount, 2) : '∞' }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($tax->tax_rate, 2) }}%</td>
                            <td class="px-4 py-3 text-center text-sm">{{ $tax->effective_date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $tax->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $tax->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <form method="POST" action="{{ route('tax-tables.toggle', $tax) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sm {{ $tax->is_active ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800' }}">
                                            {{ $tax->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('tax-tables.edit', $tax) }}" class="text-blue-600 hover:text-blue-800 text-sm">Edit</a>
                                    <form method="POST" action="{{ route('tax-tables.destroy', $tax) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Delete this tax table?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                No National tax tables found. <a href="{{ route('tax-tables.create') }}?type=National" class="text-blue-600 hover:text-blue-800">Create one</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">National Tax Brackets</div>
                <div class="text-2xl font-bold text-blue-700">{{ $nationalTables->count() }}</div>
                <div class="text-xs text-gray-400">Active: {{ $nationalTables->where('is_active', true)->count() }}</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">Expatriate Tax Brackets</div>
                <div class="text-2xl font-bold text-purple-700">{{ $expatTables->count() }}</div>
                <div class="text-xs text-gray-400">Active: {{ $expatTables->where('is_active', true)->count() }}</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">Total Active</div>
                <div class="text-2xl font-bold text-green-700">{{ $taxTables->where('is_active', true)->count() }}</div>
                <div class="text-xs text-gray-400">Total: {{ $taxTables->count() }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">Tax Formula</div>
                <div class="text-sm font-medium text-gray-700">Tax = (Income × Rate%) - Offset</div>
                <div class="text-xs text-gray-400">PNG IRC Standard</div>
            </div>
        </div>
    </div>
</div>
@endsection
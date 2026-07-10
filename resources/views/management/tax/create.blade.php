@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Add Tax Table
    </h2>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-6">

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <strong class="font-bold">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('tax-tables.store') }}">
                @csrf

                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-label">Tax Table Name</label>
                        <input type="text" name="name" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                               value="{{ old('name') }}" 
                               placeholder="e.g., National Tax 2024">
                    </div>

                    <!-- Employee Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-label">Employee Type</label>
                        <select name="employee_type" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Select Type</option>
                            <option value="National" {{ old('employee_type') == 'National' ? 'selected' : '' }}>National</option>
                            <option value="Expatriate" {{ old('employee_type') == 'Expatriate' ? 'selected' : '' }}>Expatriate</option>
                        </select>
                    </div>

                    <!-- Income Range -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 required-label">Min Amount (K)</label>
                            <input type="number" step="0.01" name="min_amount" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('min_amount', 0) }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Max Amount (K)</label>
                            <input type="number" step="0.01" name="max_amount" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('max_amount') }}" 
                                   placeholder="Leave blank for unlimited">
                        </div>
                    </div>

                    <!-- Tax Rate -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-label">Tax Rate (%)</label>
                        <input type="number" step="0.01" name="tax_rate" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                               value="{{ old('tax_rate', 0) }}">
                    </div>

                    <!-- Fixed Tax -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fixed Tax (K)</label>
                        <input type="number" step="0.01" name="fixed_tax" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                               value="{{ old('fixed_tax', 0) }}">
                        <p class="mt-1 text-xs text-gray-500">Formula: Tax = (Income × Rate%) - Fixed Tax</p>
                    </div>

                    <!-- Effective Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-label">Effective Date</label>
                        <input type="date" name="effective_date" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                               value="{{ old('effective_date', date('Y-m-d')) }}">
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date (Optional)</label>
                        <input type="date" name="end_date" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                               value="{{ old('end_date') }}">
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} 
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-end space-x-3">
                    <a href="{{ route('tax-tables.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                        Save Tax Table
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
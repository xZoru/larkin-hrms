@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settlement Payments</h2>
        <div class="text-sm text-gray-500">Payroll / Settlement Payments</div>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <form method="GET" action="{{ route('settlements.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label for="type" class="block text-xs font-medium text-gray-600 mb-1">Payment type</label>
                    <select id="type" name="type" class="w-full rounded-md border-gray-300 text-sm">
                        <option value="">All payments</option>
                        <option value="final_pay" @selected(request('type') === 'final_pay')>Final Pay</option>
                        <option value="annual_leave_pay" @selected(request('type') === 'annual_leave_pay')>Annual Leave Pay</option>
                    </select>
                </div>
                <div>
                    <label for="search" class="block text-xs font-medium text-gray-600 mb-1">Employee</label>
                    <input id="search" name="search" value="{{ request('search') }}" placeholder="Name or employee number" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium">Filter</button>
                    <a href="{{ route('settlements.index') }}" class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-md text-sm font-medium">Clear</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Employee</th>
                            <th class="px-4 py-3 text-left">Payment Type</th>
                            <th class="px-4 py-3 text-left">Service Period</th>
                            <th class="px-4 py-3 text-right">Amount</th>
                            <th class="px-4 py-3 text-left">Created By</th>
                            <th class="px-4 py-3 text-left">Created</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $payment->employee->full_name ?? 'Deleted employee' }}</div>
                                    <div class="text-xs text-gray-500">{{ $payment->employee->employee_number ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if($payment->type === 'final_pay')
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">Final Pay</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">Annual Leave Pay</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $payment->commenced_at->format('d M Y') }} – {{ $payment->ended_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap">K {{ number_format($payment->amount, 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $payment->createdBy->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('settlements.download', $payment) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">
                                        <i class="fas fa-download mr-1"></i> PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                    No settlement payment records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($payments->hasPages())
            <div class="mt-4">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection

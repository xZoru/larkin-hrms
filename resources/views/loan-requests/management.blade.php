@extends('layouts.app')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Loan Management</h2>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <form method="GET" action="{{ route('loan-management.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select id="employee_id" name="employee_id" class="w-full rounded-md border-gray-300" onchange="this.form.submit()">
                        <option value="">Select an employee</option>
                        @foreach($employees as $option)
                            <option value="{{ $option->id }}" @selected($employee?->id === $option->id)>
                                {{ $option->employee_number }} — {{ $option->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">View history</button>
                <a href="{{ route('loan-requests.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-center text-gray-700">Manage requests</a>
            </form>
        </div>

        @if($employee)
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-5 flex flex-wrap gap-8">
                <div><div class="text-xs uppercase text-indigo-600">Employee</div><div class="font-semibold text-gray-900">{{ $employee->full_name }}</div><div class="text-sm text-gray-600">{{ $employee->employee_number }}</div></div>
                <div><div class="text-xs uppercase text-indigo-600">Open balance</div><div class="font-semibold text-gray-900">K {{ number_format($loans->whereIn('status', ['Released', 'Approved', 'On-Hold'])->sum('remaining_balance'), 2) }}</div></div>
                <div><div class="text-xs uppercase text-indigo-600">Released loans</div><div class="font-semibold text-gray-900">{{ $loans->where('status', 'Released')->count() }}</div></div>
                <div><div class="text-xs uppercase text-indigo-600">Payroll deductions</div><div class="font-semibold text-gray-900">K {{ number_format($payrollDeductions->sum('loan_deduction'), 2) }}</div></div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b font-semibold text-gray-800">Loan History</div>
                <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-6 py-3">Date</th><th class="px-6 py-3">Type</th><th class="px-6 py-3 text-right">Amount</th><th class="px-6 py-3 text-right">Per payroll</th><th class="px-6 py-3 text-right">Paid</th><th class="px-6 py-3 text-right">Balance</th><th class="px-6 py-3">Status</th></tr></thead><tbody class="divide-y">
                    @forelse($loans as $loan)
                        <tr><td class="px-6 py-3">{{ $loan->created_at->format('d M Y') }}</td><td class="px-6 py-3">{{ $loan->loan_type }}</td><td class="px-6 py-3 text-right">K {{ number_format($loan->amount, 2) }}</td><td class="px-6 py-3 text-right">K {{ number_format($loan->deduction_per_cutoff, 2) }}</td><td class="px-6 py-3 text-right">K {{ number_format($loan->total_paid, 2) }}</td><td class="px-6 py-3 text-right">K {{ number_format($loan->remaining_balance, 2) }}</td><td class="px-6 py-3">{{ $loan->status }}</td></tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No loan history for this employee.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b font-semibold text-gray-800">Deduction History</div>
                <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-gray-50 text-left text-gray-600"><tr><th class="px-6 py-3">Processed</th><th class="px-6 py-3">Loan</th><th class="px-6 py-3">Payroll</th><th class="px-6 py-3">Type</th><th class="px-6 py-3 text-right">Amount</th><th class="px-6 py-3 text-right">Balance after</th></tr></thead><tbody class="divide-y">
                    @forelse($payments as $payment)
                        <tr><td class="px-6 py-3">{{ $payment->created_at->format('d M Y') }}</td><td class="px-6 py-3">{{ $payment->loan->loan_type }} #{{ $payment->loan_id }}</td><td class="px-6 py-3">{{ $payment->payroll ? 'FN ' . $payment->payroll->fortnight_number : 'Manual payment' }}</td><td class="px-6 py-3">{{ ucfirst($payment->payment_type) }}</td><td class="px-6 py-3 text-right">K {{ number_format($payment->amount, 2) }}</td><td class="px-6 py-3 text-right">K {{ number_format($payment->balance_after, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No deductions or manual payments yet.</td></tr>
                    @endforelse
                </tbody></table></div>
            </div>
        @else
            <div class="bg-white shadow-sm rounded-lg p-10 text-center text-gray-500">Choose an employee to view their loans and payroll deductions.</div>
        @endif
    </div>
</div>
@endsection

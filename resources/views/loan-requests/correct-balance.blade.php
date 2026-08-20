@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-semibold text-gray-900">Correct Loan Balance</h1>
        <p class="mt-2 text-sm text-gray-600">
            Use this only to correct a historical discrepancy, such as a deduction left behind after a deleted payroll.
            The correction is saved in the payment history for traceability.
        </p>

        <dl class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Employee</dt><dd class="font-medium">{{ $loanRequest->employee->full_name }}</dd></div>
            <div><dt class="text-gray-500">Original loan amount</dt><dd class="font-medium">K {{ number_format($loanRequest->amount, 2) }}</dd></div>
            <div><dt class="text-gray-500">Current remaining balance</dt><dd class="font-medium">K {{ number_format($loanRequest->remaining_balance, 2) }}</dd></div>
            <div><dt class="text-gray-500">Current total paid</dt><dd class="font-medium">K {{ number_format($loanRequest->total_paid, 2) }}</dd></div>
        </dl>

        <form method="POST" action="{{ route('loan-requests.correct-balance', $loanRequest) }}" class="mt-6 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="remaining_balance" class="block text-sm font-medium text-gray-700">Correct remaining balance</label>
                <input id="remaining_balance" name="remaining_balance" type="number" min="0" max="{{ $loanRequest->amount }}" step="0.01" value="{{ old('remaining_balance', $loanRequest->remaining_balance) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('remaining_balance')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700">Reason for correction</label>
                <textarea id="reason" name="reason" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Example: Reversed deduction from deleted payroll FN 12">{{ old('reason') }}</textarea>
                @error('reason')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save correction</button>
                <a href="{{ route('loan-requests.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

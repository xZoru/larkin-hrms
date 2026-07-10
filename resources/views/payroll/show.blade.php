@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Payroll {{ $payroll->fortnight_number }}
    </h2>
@endsection

@section('content')
<div class="py-6">

        <!-- Status Badge -->
        <div class="mb-4">
            <span class="px-3 py-1 rounded-full text-sm font-medium 
                {{ $payroll->status == 'Approved' ? 'bg-green-100 text-green-800' : 
                   ($payroll->status == 'Draft' ? 'bg-yellow-100 text-yellow-800' : 
                   'bg-gray-100 text-gray-800') }}">
                Status: {{ $payroll->status }}
            </span>
            <span class="ml-4 text-sm text-gray-500">
                Period: {{ $payroll->period_start->format('M d, Y') }} - {{ $payroll->period_end->format('M d, Y') }}
            </span>
        </div>

        <!-- Payroll Items -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Hourly Rate</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Regular</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Basic Pay</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Overtime</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Sunday</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Holiday</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Allowance</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Gross</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Tax</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">NASFUND</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Loan</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Net</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Method</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($payroll->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <div class="font-medium">{{ $item->employee->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->employee->employee_number }}</div>
                            </td>
                            <td class="px-3 py-2 text-right">{{ number_format($item->hourly_rate, 2) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($item->regular_hours, 1) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($item->regular_pay, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($item->overtime_pay, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($item->sunday_pay, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($item->holiday_pay, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($item->allowance, 2) }}</td>
                            <td class="px-3 py-2 text-right font-medium">K {{ number_format($item->gross_wage, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($item->tax, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($item->nasfund_ee, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($item->loan_deduction, 2) }}</td>
                            <td class="px-3 py-2 text-right font-bold">K {{ number_format($item->net_pay, 2) }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="text-xs px-2 py-1 rounded-full {{ $item->payment_method == 'Bank Transfer' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $item->payment_method }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="px-3 py-2" colspan="8">TOTAL</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($payroll->total_gross, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($payroll->total_tax, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($payroll->total_nasfund_ee, 2) }}</td>
                            <td class="px-3 py-2 text-right">K {{ number_format($payroll->total_loan_deductions, 2) }}</td>
                            <td class="px-3 py-2 text-right font-bold">K {{ number_format($payroll->total_net, 2) }}</td>
                            <td class="px-3 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('payroll.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">
                Back
            </a>
            @if($payroll->status == 'Draft')
            <form method="POST" action="{{ route('payroll.approve', $payroll) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">
                    Approve Payroll
                </button>
            </form>
            @endif
            <a href="{{ route('payroll.export-aba', $payroll) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                Export ABA
            </a>
        </div>
    </div>
</div>
@endsection
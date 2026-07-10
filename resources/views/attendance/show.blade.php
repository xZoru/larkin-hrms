@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Attendance for {{ $employee->full_name }}
    </h2>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">

                <!-- Back Button -->
                <a href="{{ route('attendance.index') }}" class="text-blue-600 hover:text-blue-800 text-sm inline-flex items-center">
                    ← Back to Attendance
                </a>

                <!-- Employee Info -->
                <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Employee</span>
                            <div class="font-medium">{{ $employee->full_name }}</div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Employee No</span>
                            <div class="font-medium">{{ $employee->employee_number }}</div>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Fortnight</span>
                            <div class="font-medium">{{ $fortnight }}</div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Attendance Records</h3>
                    
                    @if($logs->count() > 0)
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Time In</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Time Out</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hours</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Break</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Type</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($logs as $log)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium">{{ $log->date->format('M d, Y') }}</td>
                                        <td class="px-4 py-3 text-center">{{ $log->time_in ? \Carbon\Carbon::parse($log->time_in)->format('H:i') : '-' }}</td>
                                        <td class="px-4 py-3 text-center">{{ $log->time_out ? \Carbon\Carbon::parse($log->time_out)->format('H:i') : '-' }}</td>
                                        <td class="px-4 py-3 text-center font-semibold">{{ number_format($log->hours_worked, 1) }}</td>
                                        <td class="px-4 py-3 text-center">{{ $log->has_break ? '✅' : '❌' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($log->is_holiday)
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Holiday</span>
                                            @elseif($log->is_sunday)
                                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">Sunday</span>
                                            @else
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Regular</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 font-semibold">
                                    <tr>
                                        <td class="px-4 py-3 text-right" colspan="3">TOTAL</td>
                                        <td class="px-4 py-3 text-center text-lg">{{ number_format($summary->total_hours ?? 0, 1) }}</td>
                                        <td class="px-4 py-3 text-center" colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>No attendance records found for this fortnight.</p>
                        </div>
                    @endif
                </div>

                <!-- Navigation -->
                <div class="mt-6 flex items-center justify-between">
                    <a href="{{ route('attendance.index', ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d'), 'fortnight' => $fortnight]) }}" 
                       class="text-gray-600 hover:text-gray-800 text-sm">
                        ← Previous Day
                    </a>
                    <a href="{{ route('attendance.index', ['date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d'), 'fortnight' => $fortnight]) }}" 
                       class="text-gray-600 hover:text-gray-800 text-sm">
                        Next Day →
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
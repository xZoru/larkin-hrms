@php
    $companyId = auth()->user()->company_id;
    
    // Get all expatriate employees with expiry dates
    $employees = App\Models\Employee::where('company_id', $companyId)
        ->where('employee_type', 'Expatriate')
        ->where('status', 'Active')
        ->get();
    
    $expiringDocs = [];
    $now = \Carbon\Carbon::now();
    $warningDays = 90;
    $urgentDays = 30;
    
    foreach ($employees as $employee) {
        // Check Passport
        if ($employee->passport_expiry) {
            $days = $now->diffInDays(\Carbon\Carbon::parse($employee->passport_expiry), false);
            if ($days >= 0 && $days <= $warningDays) {
                $expiringDocs[] = (object) [
                    'employee' => $employee,
                    'document' => 'Passport',
                    'expiry_date' => $employee->passport_expiry,
                    'days' => $days,
                    'is_urgent' => $days <= $urgentDays,
                    'type' => 'passport'
                ];
            }
        }
        
        // Check Visa
        if ($employee->visa_expiry) {
            $days = $now->diffInDays(\Carbon\Carbon::parse($employee->visa_expiry), false);
            if ($days >= 0 && $days <= $warningDays) {
                $expiringDocs[] = (object) [
                    'employee' => $employee,
                    'document' => 'Visa',
                    'expiry_date' => $employee->visa_expiry,
                    'days' => $days,
                    'is_urgent' => $days <= $urgentDays,
                    'type' => 'visa'
                ];
            }
        }
        
        // Check Work Permit
        if ($employee->work_permit_expiry) {
            $days = $now->diffInDays(\Carbon\Carbon::parse($employee->work_permit_expiry), false);
            if ($days >= 0 && $days <= $warningDays) {
                $expiringDocs[] = (object) [
                    'employee' => $employee,
                    'document' => 'Work Permit',
                    'expiry_date' => $employee->work_permit_expiry,
                    'days' => $days,
                    'is_urgent' => $days <= $urgentDays,
                    'type' => 'work_permit'
                ];
            }
        }
    }
    
    // Sort by days remaining (urgent first)
    usort($expiringDocs, function($a, $b) {
        return $a->days - $b->days;
    });
    
    $totalCount = count($expiringDocs);
    $urgentCount = count(array_filter($expiringDocs, function($doc) {
        return $doc->is_urgent;
    }));
@endphp

<!-- Expiry Widget -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-clock text-indigo-500"></i>
                Document Expiry Alerts
                @if($totalCount > 0)
                    <span class="text-sm font-normal text-gray-500">({{ $totalCount }} expiring)</span>
                    @if($urgentCount > 0)
                        <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $urgentCount }} urgent</span>
                    @endif
                @endif
            </h3>
            @if($totalCount == 0)
                <span class="text-xs text-green-600">
                    <i class="fas fa-check-circle"></i> All documents up to date
                </span>
            @endif
        </div>
    </div>

    <div class="p-4">
        @if($totalCount > 0)
            <div class="space-y-2 max-h-80 overflow-y-auto">
                @foreach($expiringDocs as $doc)
                    @php
                        $employee = $doc->employee;
                        $days = $doc->days;
                        $isUrgent = $doc->is_urgent;
                        $date = \Carbon\Carbon::parse($doc->expiry_date);
                    @endphp
                    <div class="flex items-start justify-between p-3 rounded-lg hover:bg-gray-50 transition border {{ $isUrgent ? 'border-red-200 bg-red-50' : 'border-gray-100' }}">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('employees.show', $employee->id) }}" class="hover:text-indigo-600">
                                        {{ $employee->full_name }}
                                    </a>
                                </span>
                                <span class="text-xs text-gray-500">({{ $employee->employee_number }})</span>
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $doc->document }}</span>
                            </div>
                            <div class="text-sm mt-1">
                                @if($days == 0)
                                    <span class="font-semibold text-red-600">EXPIRES TODAY!</span>
                                @else
                                    Expires in 
                                    <span class="font-semibold {{ $isUrgent ? 'text-red-600' : 'text-yellow-600' }}">
                                        {{ $days }} days
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">
                                    ({{ $date->format('d M Y') }})
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-3 flex-shrink-0">
                            <a href="{{ route('employees.show', $employee->id) }}" 
                               class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-gray-500">
                <i class="fas fa-check-circle text-green-500 text-2xl mb-2 block"></i>
                <p class="text-sm">All employee documents are up to date.</p>
                <p class="text-xs text-gray-400">No documents expiring within 90 days.</p>
            </div>
        @endif
    </div>
</div>
@php
    $companyId = auth()->user()->company_id;
    $notificationService = app(App\Services\NotificationService::class);
    $notifications = $notificationService->getActiveNotifications($companyId);
    $urgentCount = $notificationService->getUrgentNotifications($companyId)->count();
    $totalCount = $notifications->count();
@endphp

@if($totalCount > 0)
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-bell text-indigo-500"></i>
                Document Expiry Notifications
                @if($urgentCount > 0)
                    <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $urgentCount }} urgent</span>
                @endif
            </h3>
            @if($totalCount > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-indigo-600 transition">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="p-4 space-y-2 max-h-80 overflow-y-auto">
        @foreach($notifications as $notification)
            @php
                $isUrgent = $notification->days_before <= 30;
                $employee = $notification->employee;
                $days = $notification->days_before;
                $documentLabel = ucwords(str_replace('_', ' ', $notification->type));
            @endphp
            <div class="flex items-start justify-between p-3 rounded-lg hover:bg-gray-50 transition border border-gray-100 {{ $isUrgent ? 'border-red-200 bg-red-50' : '' }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900">
                            <a href="{{ route('employees.show', $employee->id) }}" class="hover:text-indigo-600">
                                {{ $employee->full_name }}
                            </a>
                        </span>
                        <span class="text-xs text-gray-500">({{ $employee->employee_number }})</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ $documentLabel }} expires in <span class="font-semibold {{ $isUrgent ? 'text-red-600' : 'text-yellow-600' }}">{{ $days }}</span> days
                        <span class="text-xs text-gray-400">
                            ({{ \Carbon\Carbon::parse($notification->expiry_date)->format('d M Y') }})
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-3 flex-shrink-0">
                    <a href="{{ route('employees.show', $employee->id) }}" 
                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                        View
                    </a>
                    <form action="{{ route('notifications.mark-read', $notification) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-gray-400 hover:text-gray-600" title="Mark as read">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
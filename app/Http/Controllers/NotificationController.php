<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notification count for the current user's company
     */
    public function count()
    {
        $companyId = auth()->user()->company_id;
        
        $total = $this->notificationService->getNotificationCount($companyId);
        $urgent = $this->notificationService->getUrgentNotifications($companyId)->count();
        
        return response()->json([
            'total' => $total,
            'urgent' => $urgent,
            'has_notifications' => $total > 0,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Notification $notification)
    {
        $this->authorizeNotification($notification);
        
        $notification->markAsRead();
        
        return redirect()->back()
            ->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read for the current user's company
     */
    public function markAllAsRead()
    {
        $companyId = auth()->user()->company_id;
        
        $employeeIds = \App\Models\Employee::where('company_id', $companyId)->pluck('id');
        
        Notification::whereIn('employee_id', $employeeIds)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        
        return redirect()->back()
            ->with('success', 'All notifications marked as read.');
    }

    /**
     * Get notifications for the dashboard widget (HTML)
     */
    public function widget()
    {
        $companyId = auth()->user()->company_id;
        
        $notifications = $this->notificationService->getActiveNotifications($companyId);
        
        return view('partials.notification-widget', compact('notifications'));
    }

    /**
     * Dismiss a notification
     */
    public function dismiss(Notification $notification)
    {
        $this->authorizeNotification($notification);
        
        $notification->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Check if user can access this notification
     */
    private function authorizeNotification($notification)
    {
        $user = auth()->user();
        
        // Check if notification belongs to an employee in the user's company
        if ($notification->employee && $notification->employee->company_id !== $user->company_id) {
            abort(403, 'Unauthorized access to this notification.');
        }
    }
}
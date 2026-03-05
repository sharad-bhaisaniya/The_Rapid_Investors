<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterNotification;
use App\Models\MasterNotificationRead;
use Illuminate\Support\Facades\Auth;

class NotificationApiController extends Controller
{
    /**
     * Fetch Unseen Notifications for Header Bell Icon
     * GET /api/notifications/unseen
     */
    public function fetchUnseen()
    {
        $userId = Auth::id();

        // 🔍 Get unseen notifications (global + user specific)
        $notifications = MasterNotification::where(function ($q) use ($userId) {
                // global notifications
                $q->where('is_global', true)
                // OR personal notifications
                ->orWhere('user_id', $userId);
            })
            // remove already seen by this user
            ->whereDoesntHave('reads', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->latest()
            ->take(15)
            ->get();

        return response()->json([
            'success' => true,
            'count'   => $notifications->count(),
            'notifications' => $notifications->map(function ($n) {
                return [
                    'tracking_id' => $n->id,
                    'type'        => $n->type, // announcement, chat, tip, ticket, etc.
                    'title'       => $n->title,
                    'message'     => $n->message,
                    'severity'    => $n->severity ?? 'info',
                    
                    // API friendly URL paths (no route() helper to avoid web session issues)
                    'url' => match ($n->type) {
                        'announcement' => '/announcements',
                        'chat'         => '/chat',
                        'tip'          => '/market-calls',
                        'ticket'       => '/tickets',
                        default        => '/notifications',
                    },

                    'created_at' => $n->created_at->diffForHumans(),
                    'full_date'  => $n->created_at->toDateTimeString(),
                    'read_at'    => null,
                ];
            })
        ]);
    }

    /**
     * Mark a single notification as read from the bell icon click
     * POST /api/notifications/mark-read/{id}
     */
    public function markAsRead($id)
    {
        $userId = Auth::id();

        MasterNotificationRead::updateOrCreate(
            [
                'master_notification_id' => $id,
                'user_id' => $userId
            ],
            [
                'read_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterNotification;
use App\Models\MasterNotificationRead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class AllNotificationApi extends Controller
{
    

public function index()
{
    try {
        $user = Auth::user();
        $userId = $user->id;

        Log::info("Fetching unread notifications", ['user_id' => $userId]);

        // 1. Hidden notifications
        $hiddenIds = MasterNotificationRead::where('user_id', $userId)
                        ->whereNotNull('deleted_at')
                        ->pluck('master_notification_id');

        // 2. Already read notifications
        $readIds = MasterNotificationRead::where('user_id', $userId)
                        ->whereNull('deleted_at')
                        ->pluck('master_notification_id');

        Log::debug("Notification exclusion lists", [
            'hidden_count' => $hiddenIds->count(),
            'read_count' => $readIds->count()
        ]);

        // 3. Main notification query
        $query = MasterNotification::where(function ($q) use ($userId) {
                $q->where('is_global', true)
                  ->orWhere('user_id', $userId);
            })
            ->whereNotIn('id', $hiddenIds)
            ->whereNotIn('id', $readIds);

        // 4. Type based filtering (chat & ticket logic)
        $query->where(function ($q) use ($user, $userId) {
            $q->whereNotIn('type', ['chat','ticket'])
            ->orWhere(function ($chatQuery) use ($user, $userId) {
                $chatQuery->where('type', 'chat');

                if ($userId == 1 || $user->hasRole('admin')) {
                    $chatQuery->where('user_id', 1)
                              ->whereNotNull('data->from_user_id');
                } else {
                    $chatQuery->where('user_id', $userId)
                              ->where('data->role', 'admin');
                }
            })
            ->orWhere(function ($ticketQuery) use ($user, $userId) {
                $ticketQuery->where('type', 'ticket');

                if ($userId == 1 || $user->hasRole('admin')) {
                    $ticketQuery->where('user_id', 1);
                } else {
                    $ticketQuery->where('user_id', $userId);
                }
            });
        });

        $notifications = $query->orderByDesc('created_at')->get();

        Log::info("Notifications found", ['count' => $notifications->count()]);

        $formattedNotifications = $notifications->map(function ($n) {
            $n->is_read = false; // unread only
            return $n;
        });

        return response()->json([
            'status' => true,
            'data' => $formattedNotifications
        ]);

    } catch (\Exception $e) {

        Log::error("Error in notification index", [
            'user_id' => $userId ?? 'unknown',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'status' => false,
            'error' => 'Failed to load notifications'
        ], 500);
    }
}




    /**
     * Mark single notification read
     */
    public function markRead(Request $request)
{
    $request->validate([
        'notification_id' => 'required|exists:master_notifications,id'
    ]);

    MasterNotificationRead::updateOrCreate(
        [
            'master_notification_id' => $request->notification_id,
            'user_id' => Auth::id(),
        ],
        [
             'read_at'   => now(),
            'deleted_at' => null
        ]
    );

    return response()->json(['status' => true]);
}


    /**
     * Mark all as read
     */
    public function markAllRead()
    {
        $userId = Auth::id();

        $ids = MasterNotification::pluck('id');

        foreach ($ids as $id) {
            MasterNotificationRead::updateOrCreate(
                [
                    'master_notification_id' => $id,
                    'user_id' => $userId,
                ],
                [
                    'read_at'   => now(),
                    'deleted_at' => null
                ]
            );
        }

        return response()->json([
            'status' => true
        ]);
    }

    /**
     * Soft delete for user only
     */
   public function delete(Request $request)
{
    $request->validate([
        'notification_id' => 'required|exists:master_notifications,id'
    ]);

    MasterNotificationRead::updateOrCreate(
        [
            'master_notification_id' => $request->notification_id,
            'user_id' => Auth::id(),
        ],
        [
            'read_at'    => now(),
            'deleted_at' => now()
        ]
    );

    return response()->json(['status' => true]);
}


    /**
     * Unread count
     */
       public function unreadCount()
    {
        $userId = Auth::id();

        $count = MasterNotification::whereNotIn('id', function ($q) use ($userId) {
                $q->select('master_notification_id')
                  ->from('master_notification_reads')
                  ->where('user_id', $userId);
            })
            ->count();

        return response()->json(['count' => $count]);
    }

}

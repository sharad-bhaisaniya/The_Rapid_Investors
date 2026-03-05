<?php

namespace App\Http\Controllers\UserDashboardController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\MasterNotification;
use App\Models\MasterNotificationRead;


class NotificationController extends Controller
{
    /**
     * Show all notifications for logged user
     */

// public function index()
// {
//     $user = Auth::user();
//     $userId = $user->id;

//     $hiddenIds = MasterNotificationRead::where('user_id', $userId)
//                     ->whereNotNull('deleted_at')
//                     ->pluck('master_notification_id');

//     $readIds = MasterNotificationRead::where('user_id', $userId)
//                     ->whereNull('deleted_at')
//                     ->pluck('master_notification_id');

//     $query = MasterNotification::whereNotIn('id', $hiddenIds);

//     $query->where(function ($q) use ($user, $userId) {
//         $q->where('type', '!=', 'chat')
//           ->orWhere(function ($chatQuery) use ($user, $userId) {
//               $chatQuery->where('type', 'chat');

//               if ($userId == 1 || $user->hasRole('admin')) {
//                   $chatQuery->where('user_id', 1)
//                             ->whereNotNull('data->from_user_id');
//               } else {
//                   $chatQuery->where('user_id', $userId)
//                             ->where('data->role', 'admin');
//               }
//           });
//     });

//     $notifications = $query->orderByDesc('created_at')
//         ->get()
//         ->map(function ($n) use ($readIds) {
//             $n->is_read = $readIds->contains($n->id);
//             return $n;
//         });

//     return response()->json([
//         'status' => true,
//         'data' => $notifications
//     ]);
// }

// public function index()
// {
//     $user = Auth::user();
//     $userId = $user->id;

//     $hiddenIds = MasterNotificationRead::where('user_id', $userId)
//                     ->whereNotNull('deleted_at')
//                     ->pluck('master_notification_id');

//     $readIds = MasterNotificationRead::where('user_id', $userId)
//                     ->whereNull('deleted_at')
//                     ->pluck('master_notification_id');

//     $query = MasterNotification::whereNotIn('id', $hiddenIds);

//     $query->where(function ($q) use ($user, $userId) {

//         /*
//         |--------------------------------------------------
//         | NON CHAT & NON TICKET → always visible
//         |--------------------------------------------------
//         */
//         $q->whereNotIn('type', ['chat','ticket'])

//         /*
//         |--------------------------------------------------
//         | CHAT (UNCHANGED — YOUR ORIGINAL LOGIC)
//         |--------------------------------------------------
//         */
//         ->orWhere(function ($chatQuery) use ($user, $userId) {
//             $chatQuery->where('type', 'chat');

//             if ($userId == 1 || $user->hasRole('admin')) {
//                 $chatQuery->where('user_id', 1)
//                           ->whereNotNull('data->from_user_id');
//             } else {
//                 $chatQuery->where('user_id', $userId)
//                           ->where('data->role', 'admin');
//             }
//         })

//         /*
//         |--------------------------------------------------
//         | TICKET (NEW — SAME IDEA AS CHAT)
//         |--------------------------------------------------
//         */
//         ->orWhere(function ($ticketQuery) use ($user, $userId) {
//             $ticketQuery->where('type', 'ticket');

//             if ($userId == 1 || $user->hasRole('admin')) {
//                 // Admin sees all user ticket notifications
//                 $ticketQuery->where('user_id',1);
//             } else {
//                 // User sees only their ticket notifications
//                 $ticketQuery->where('user_id', $userId);
//             }
//         });

//     });

//     $notifications = $query->orderByDesc('created_at')
//         ->get()
//         ->map(function ($n) use ($readIds) {
//             $n->is_read = $readIds->contains($n->id);
//             return $n;
//         });

//     return response()->json([
//         'status' => true,
//         'data' => $notifications
//     ]);
// }


public function index()
{
    try {
        $user = Auth::user();
        $userId = $user->id;

        Log::info("Fetching unread notifications", ['user_id' => $userId]);

        // 1. Get IDs of notifications the user has deleted/hidden
        $hiddenIds = MasterNotificationRead::where('user_id', $userId)
                        ->whereNotNull('deleted_at')
                        ->pluck('master_notification_id');

        // 2. Get IDs of notifications the user has already read
        $readIds = MasterNotificationRead::where('user_id', $userId)
                        ->whereNull('deleted_at')
                        ->pluck('master_notification_id');

        Log::debug("Notification exclusion lists", [
            'hidden_count' => $hiddenIds->count(),
            'read_count' => $readIds->count()
        ]);

        // 3. Start query excluding both hidden AND already read notifications
        $query = MasterNotification::where(function ($q) use ($userId) {
            $q->where('is_global', true)
              ->orWhere('user_id', $userId);
        })
            ->whereNotIn('id', $hiddenIds)
            ->whereNotIn('id', $readIds);

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
            // Hardcoding to false because we excluded $readIds in the query
            $n->is_read = false; 
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
    $userId = Auth::id();
    
    $request->validate([
        'notification_id' => 'required|exists:master_notifications,id'
    ]);

    Log::info("Attempting to mark notification as read", [
        'user_id' => $userId,
        'notification_id' => $request->notification_id
    ]);

    try {
        MasterNotificationRead::updateOrCreate(
            [
                'master_notification_id' => $request->notification_id,
                'user_id' => $userId,
            ],
            [
                'read_at'   => now(),
                'deleted_at' => null
            ]
        );
        
        Log::info("Successfully marked notification as read", ['notification_id' => $request->notification_id]);
    } catch (\Exception $e) {
        Log::error("Error marking notification as read", [
            'error' => $e->getMessage(),
            'notification_id' => $request->notification_id
        ]);
    }

    return response()->json(['status' => true]);
}


/**
 * Mark all as read
 */
public function markAllRead()
{
    $userId = Auth::id();
    Log::info("Start markAllRead process for user", ['user_id' => $userId]);

    // Get the IDs that will be processed
    $ids = MasterNotification::pluck('id');
    
    Log::debug("IDs found for processing", [
        'count' => $ids->count(),
        'ids' => $ids->toArray()
    ]);

    try {
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
        Log::info("Finished markAllRead process successfully", ['user_id' => $userId]);
    } catch (\Exception $e) {
        Log::error("Error during markAllRead loop", [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);
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
                  ->where('user_id', $userId)
                  ->whereNull('deleted_at');
            })
            ->count();

        return response()->json(['count' => $count]);
    }
}

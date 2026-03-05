<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\MasterNotification;
use App\Models\MasterNotificationRead;
use App\Events\UserChatMessageSent;
use App\Events\MasterNotificationBroadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserChatApiController extends Controller
{
    /**
     * Fetch Chat History & Mark Chat Notifications as Read
     * GET /api/chat/history
     */
    public function history()
    {
        $userId = auth()->id();
        $adminId = 1;

        // 1. Mark 'chat' type notifications as read for this user
        $chatNotificationIds = MasterNotification::where('type', 'chat')
            ->where('user_id', $userId)
            ->pluck('id');

        if ($chatNotificationIds->isNotEmpty()) {
            $this->bulkMarkAsRead($chatNotificationIds, $userId);
        }

        // 2. Fetch History (User <-> Admin)
        $messages = ChatMessage::where(function ($q) use ($userId, $adminId) {
                $q->where('from_user_id', $userId)->where('to_user_id', $adminId);
            })
            ->orWhere(function ($q) use ($userId, $adminId) {
                $q->where('from_user_id', $adminId)->where('to_user_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get(['id', 'message', 'from_role', 'created_at']);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * User → Admin message send
     * POST /api/chat/send
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
            ]);

            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $adminId = 1;

            // 1️⃣ SAVE CHAT MESSAGE
            $chat = ChatMessage::create([
                'from_user_id' => $user->id,
                'to_user_id'   => $adminId,
                'message'      => $request->message,
                'from_role'    => 'user',
                'is_read'      => 0,
            ]);

            // 2️⃣ MASTER NOTIFICATION (CHAT)
            $notification = MasterNotification::create([
                'type'     => 'chat',
                'severity' => 'info',
                'title'    => 'New Support Message',
                'message'  => $request->message,
                'data'     => [
                    'from_user_id'   => $user->id,
                    'from_user_name' => $user->name ?? 'User #' . $user->id,
                    'chat_id'        => $chat->id,
                    'url'            => '/admin/chat?user=' . $user->id,
                ],
                'is_global' => false,
                'user_id'   => $adminId,
                'channel'   => 'both',
            ]);

            // 3️⃣ REALTIME BROADCASTS
            broadcast(new UserChatMessageSent($user->id, $adminId, $request->message));
            broadcast(new MasterNotificationBroadcast($notification));

            Log::info('💬 API Chat + MasterNotification sent', [
                'chat_id' => $chat->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'data' => $chat
            ]);

        } catch (\Throwable $e) {
            Log::error('❌ API Chat Send Failed', [
                'msg' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark single notification as read
     * POST /api/chat/notifications/read/{id}
     */
    public function markNotificationRead($id)
    {
        $userId = auth()->id();

        MasterNotificationRead::updateOrCreate(
            ['master_notification_id' => $id, 'user_id' => $userId],
            ['read_at' => now()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark All Notifications (all types) as read
     * POST /api/chat/notifications/read-all
     */
    public function markAllNotificationsRead()
    {
        $userId = auth()->id();
        $notificationIds = MasterNotification::pluck('id');

        if ($notificationIds->isNotEmpty()) {
            $this->bulkMarkAsRead($notificationIds, $userId);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Internal Helper for Bulk Mark-as-Read (Optimized performance)
     */
    private function bulkMarkAsRead($ids, $userId)
    {
        $now = now();
        $data = $ids->map(function ($id) use ($userId, $now) {
            return [
                'master_notification_id' => $id,
                'user_id' => $userId,
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // Using upsert prevents duplicate entries and is 100x faster than a loop
        MasterNotificationRead::upsert($data, ['master_notification_id', 'user_id'], ['read_at', 'updated_at']);
    }
}
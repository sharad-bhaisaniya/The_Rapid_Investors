<?php

namespace App\Http\Controllers\Api\Popups; // Updated namespace

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterNotification;
use App\Models\MasterNotificationRead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CampaignApiController extends Controller
{
    /**
     * Fetch unread campaigns for the authenticated user
     */
    public function getActiveCampaigns()
    {
        try {
            $userId = Auth::id();

            $campaigns = MasterNotification::where('type', 'campaign')
                // 🌍 global OR personal
                ->where(function ($q) use ($userId) {
                    $q->where('is_global', true)->orWhere('user_id', $userId);
                })
                // 👁️ hide already read for this user
                ->whereDoesntHave('reads', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->map(function ($c) {
                    return [
                        'id'          => $c->id,
                        'title'       => $c->title,
                        'message'     => $c->message,
                        'description' => $c->data['detail'] ?? '',
                        'image'       => $c->data['image'] ?? null,
                        'created_at'  => $c->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'success' => true,
                'campaigns' => $campaigns
            ]);

        } catch (\Throwable $e) {
            Log::error('API Campaign Fetch Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load campaigns'], 500);
        }
    }

    /**
     * Mark a specific campaign as read via API
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|exists:master_notifications,id'
        ]);

        try {
            $userId = Auth::id();

            $alreadyRead = MasterNotificationRead::where('master_notification_id', $request->campaign_id)
                ->where('user_id', $userId)
                ->exists();

            if ($alreadyRead) {
                return response()->json(['success' => true, 'message' => 'Already marked as read']);
            }

            MasterNotificationRead::create([
                'master_notification_id' => $request->campaign_id,
                'user_id' => $userId,
                'read_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Campaign marked as read']);

        } catch (\Throwable $e) {
            Log::error('API Campaign Mark Read Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}
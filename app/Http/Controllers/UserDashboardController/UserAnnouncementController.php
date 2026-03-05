<?php

namespace App\Http\Controllers\UserDashboardController;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AnnouncementNotification;
use App\Models\AnnouncementNotificationUser;
use App\Models\MasterNotification;
use App\Models\MasterNotificationRead;


class UserAnnouncementController extends Controller
{

   


    public function index()
    {
        $userId = auth()->id();

        /* ======================================
        ✅ Mark all announcements as read
        ====================================== */

        $announcementIds = MasterNotification::where('type', 'announcement')
            ->pluck('id');

        foreach ($announcementIds as $id) {
            MasterNotificationRead::updateOrCreate(
                [
                    'master_notification_id' => $id,
                    'user_id' => $userId,
                ],
                [
                    'read_at' => now(),
                ]
            );
        }

        /* ======================================
        📢 Fetch announcements
        ====================================== */

        $announcements = MasterNotification::where('type', 'announcement')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->data['type'] ?? 'General',
                    'content' => $item->message,
                    'detail' => $item->data['detail'] ?? '',

                    'date_human' => $item->created_at->diffForHumans(),
                    'date_formatted' => $item->created_at->format('d M Y'),

                    'is_new' => $item->created_at > now()->subDays(3),
                ];
            });

        return view('UserDashboard.announcement.announcement', compact('announcements'));
    }



    // public function fetchUnseen()
    // {
    //     $userId = auth()->id();

    //     // 🔍 Get unseen notifications (global + user specific)
    //     $notifications = MasterNotification::where(function ($q) use ($userId) {

    //             // global notifications
    //             $q->where('is_global', true)

    //             // OR personal notifications
    //             ->orWhere('user_id', $userId);

    //         })

    //         // remove already seen by this user
    //         ->whereDoesntHave('reads', function ($q) use ($userId) {
    //             $q->where('user_id', $userId);
    //         })

    //         ->latest()
    //         ->take(15)
    //         ->get();


    //     return response()->json([
    //         'count' => $notifications->count(),

    //         'notifications' => $notifications->map(function ($n) {

    //             return [
    //                 'tracking_id' => $n->id,
    //                 'type'        => $n->type,
    //                 'title'       => $n->title,
    //                 'message'     => $n->message,

    //                 // optional routing per type
    //                 'url' => match ($n->type) {
    //                     'announcement' => route('user.announcement.index'),
    //                     'chat'         => route('user.chat.index'),
    //                     'tip'          => route('marketCall.index'),
    //                     'ticket' => auth()->id() === 1
    //                     ? url('/admin/tickets')
    //                     : url('/help-center'),
    //                     default        => '/all-notifications/',
    //                 },

    //                 'created_at' => $n->created_at,
    //                 'read_at'    => null,
    //             ];
    //         })
    //     ]);
    // }

    public function fetchUnseen()
{
    $userId = auth()->id();

    $notifications = MasterNotification::where(function ($q) use ($userId) {
            $q->where('is_global', true)
              ->orWhere('user_id', $userId);
        })
        ->whereDoesntHave('reads', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->latest() // This keeps Newest at index 0
        ->take(15)
        ->get();

    return response()->json([
        'count' => $notifications->count(),
        'notifications' => $notifications->map(function ($n) {
            return [
                'tracking_id' => $n->id,
                'type'        => $n->type,
                'title'       => $n->title,
                'message'     => $n->message,
                // Added time_ago to help you verify "Latest" visually
                'time_ago'    => $n->created_at->diffForHumans(), 
                'url' => match ($n->type) {
                    'announcement' => route('user.announcement.index'),
                    'chat'         => auth()->id() === 1 ? url('/admin/chat') : route('user.chat.index'),
                    'tip'          => route('marketCall.index'),
                    'ticket'       => auth()->id() === 1 ? url('/admin/tickets') : url('/help-center'),
                    default        => '/all-notifications/',
                },
                'created_at' => $n->created_at,
            ];
        })
    ]);
}


    public function markSeen($id)
    {
        AnnouncementNotificationUser::updateOrCreate(
            [
                'announcement_notification_id' => $id,
                'user_id' => auth()->id(),
            ],
            [
                'is_seen' => true,
                'read_at' => now(),
            ]
        );

        return response()->json(['status' => true]);
    }
}
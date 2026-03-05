<?php

namespace App\Http\Controllers\Admin\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\MasterNotification;
use App\Models\MasterNotificationRead;
use Illuminate\Support\Facades\Log;
use App\Events\MasterNotificationBroadcast;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class AllTicketsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view tickets', only: ['index', 'show']),
            new Middleware('permission:edit tickets', only: ['open', 'resolve']),
        ];
    }

 public function index()
{
    $adminId = auth()->id();

    Log::info('🎫 Admin opened tickets page', ['admin_id' => $adminId]);

    /* ===============================
       ✅ MARK ALL TICKET NOTIFICATIONS READ
    =============================== */

    $ticketNotificationIds = MasterNotification::where('type', 'ticket')
        ->where('user_id', $adminId)
        ->pluck('id');

    foreach ($ticketNotificationIds as $nid) {
        MasterNotificationRead::updateOrCreate(
            [
                'master_notification_id' => $nid,
                'user_id' => $adminId
            ],
            [
                'read_at' => now()
            ]
        );
    }

    Log::info('✅ Ticket notifications marked as read', [
        'count' => $ticketNotificationIds->count()
    ]);

    /* ===============================
       🎫 LOAD TICKETS
    =============================== */

    $tickets = Ticket::latest()->get();

    return view('admin.tickets.all', compact('tickets'));
}

public function open($id)
{
    $ticket = Ticket::findOrFail($id);

    // Sirf tabhi update karein agar status 'In Progress' hai
    // Agar status 'Open' ya 'Resolved' hai, toh skip karein
    if ($ticket->status === 'In Progress') {
        $ticket->status = 'Open';
        $ticket->save();
        
        Log::info('Ticket status updated to Open', ['ticket_id' => $id]);
        
        return response()->json([
            'success' => true,
            'updated' => true,
            'ticket' => $ticket
        ]);
    }

    // Agar update nahi hua toh purana data hi bhej dein success ke saath
    return response()->json([
        'success' => true,
        'updated' => false,
        'ticket' => $ticket
    ]);
}

// public function resolve(Request $request, $id)
// {
//     $ticket = Ticket::findOrFail($id);
//     $ticket->status = 'Resolved';
//     $ticket->admin_note = $request->admin_note;
//     $ticket->save();

//     return response()->json([
//         'success' => true,
//         'ticket' => $ticket
//     ]);
// }

public function resolve(Request $request, $id)
{
    Log::info('Ticket resolve started', [
        'ticket_id' => $id,
        'admin_id' => auth()->id(),
        'admin_note' => $request->admin_note
    ]);

    try {
        $ticket = Ticket::with('user')->findOrFail($id);

        Log::info('Ticket loaded', [
            'ticket_id' => $ticket->id,
            'user_id' => $ticket->user_id,
            'status_before' => $ticket->status
        ]);

        // Resolve ticket
        $ticket->markResolved();
        $ticket->admin_note = $request->admin_note;
        $ticket->save();

        Log::info('Ticket resolved', [
            'ticket_id' => $ticket->id,
            'status_after' => $ticket->status,
            'resolved_at' => $ticket->resolved_at
        ]);

        $user = $ticket->user;
        $adminId = auth()->id();

        // Create notification
        $notification = MasterNotification::create([
            'type' => 'ticket',
            'severity' => 'success',
            'title' => 'Ticket Resolved',
            'message' => 'Your support ticket has been resolved: '.$ticket->subject,
            'data' => [
                'ticket_id' => $ticket->id,
                'status' => $ticket->status,
            ],
            'user_id' => $user->id,
            'is_global' => false,
            'channel' => 'in_app',
        ]);

        Log::info('MasterNotification created', [
            'notification_id' => $notification->id,
            'user_id' => $user->id
        ]);

        // 🔔 Broadcast master notification
        broadcast(new MasterNotificationBroadcast($notification))->toOthers();

        Log::info('MasterNotification broadcasted', [
            'notification_id' => $notification->id,
            'channel' => $notification->is_global ? 'public-notifications' : 'user.'.$user->id
        ]);

      

        return response()->json([
            'success' => true,
            'ticket' => $ticket
        ]);

    } catch (\Exception $e) {

        Log::error('Ticket resolve failed', [
            'ticket_id' => $id,
            'admin_id' => auth()->id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to resolve ticket'
        ], 500);
    }
}
}
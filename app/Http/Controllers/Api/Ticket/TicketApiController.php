<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\MasterNotification;
use App\Events\MasterNotificationBroadcast;
use Illuminate\Support\Str;

class TicketApiController extends Controller
{
    /**
     * List tickets for the authenticated user (API)
     */
    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::id())
                         ->orderBy('created_at', 'desc')
                         ->get();

        // Adding media URLs to each ticket for the API response
        $tickets->each(function($ticket) {
            $ticket->image_url = $ticket->getFirstMediaUrl('tickets');
        });

        return response()->json([
            'success' => true,
            'tickets' => $tickets
        ]);
    }

    /**
     * Create a new ticket (API)
     */
    public function store(Request $request)
    {
        try {
            Log::info('🎫 API Ticket submission started', [
                'user_id' => Auth::id(),
                'data' => $request->all()
            ]);

            $request->validate([
                'subject' => 'required|string|max:255',
                'issue' => 'required|string',
                'other_issue' => 'required_if:issue,other',
                'description' => 'required|string',
                'priority' => 'required|in:Low,Medium,High',
            ]);

            Log::info('✅ API Ticket validation passed');

            $finalIssue = $request->issue === 'other'
                ? $request->other_issue
                : $request->issue;

            $ticket = Ticket::create([
                'user_id' => Auth::id(),
                'subject' => $request->subject,
                'issue' => $finalIssue,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => 'In Progress',
            ]);

            // Handle Media Attachment
            if ($request->hasFile('attachment')) {
                $ticket->addMediaFromRequest('attachment')
                       ->toMediaCollection('tickets');
            }

            $ticket->refresh();
            $imageUrl = $ticket->getFirstMediaUrl('tickets');

            // ===============================
            // 🔔 NOTIFICATION TO ADMIN
            // ===============================
            $adminId = 1; 

            $notification = MasterNotification::create([
                'type'    => 'ticket',
                'title'   => 'New Support Ticket 🎫',
                'message' => Str::limit($request->subject, 80),
                'data' => [
                    'ticket_id' => $ticket->id,
                    'user_id' => Auth::id(),
                    'priority' => $request->priority,
                    'issue' => $finalIssue,
                ],
                'user_id'   => $adminId,
                'is_global' => false,
                'channel'   => 'both',
            ]);

            broadcast(new MasterNotificationBroadcast($notification));

            return response()->json([
                'success' => true,
                'message' => 'Ticket raised successfully!',
                'ticket' => $ticket,
                'image_url' => $imageUrl
            ], 201);

        } catch (\Throwable $e) {
            Log::error('❌ API Ticket creation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
{
    $ticket = Ticket::where('id', $id)
                    ->where('user_id', Auth::id())                
                    ->first();

    if (!$ticket) {
        return response()->json([
            'success' => false,
            'message' => 'Ticket not found'
        ], 404);
    }

    // Attach media URL (same like index)
    $ticket->image_url = $ticket->getFirstMediaUrl('tickets');

    return response()->json([
        'success' => true,
        'ticket'  => $ticket
    ]);
}

}
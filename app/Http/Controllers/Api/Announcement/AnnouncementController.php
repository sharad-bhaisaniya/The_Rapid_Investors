<?php

namespace App\Http\Controllers\Api\Announcement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    /**
     * Get all announcements
     * Optional filters:
     * - ?active=1
     * - ?type=info|success|warning|danger
     */
    public function index(Request $request)
    {
        try {
            $announcements = Announcement::query()
                ->when($request->filled('active'), function ($q) use ($request) {
                    $q->where('is_active', $request->active);
                })
                ->when($request->filled('type'), function ($q) use ($request) {
                    $q->where('type', $request->type);
                })
                ->latest()
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Announcements fetched successfully',
                'data'    => $announcements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch announcements',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single announcement by ID
     */
    public function show($id)
    {
        try {
            $announcement = Announcement::where('is_active', true)
                ->findOrFail($id);

            return response()->json([
                'status'  => true,
                'message' => 'Announcement fetched successfully',
                'data'    => $announcement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Announcement not found'
            ], 404);
        }
    }

    /**
     * Get only active announcements (for users)
     */
    public function active()
    {
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Active announcements fetched',
            'data'    => $announcements
        ]);
    }
}

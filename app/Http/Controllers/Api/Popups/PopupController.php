<?php

namespace App\Http\Controllers\Api\Popups;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use Illuminate\Http\Request;

class PopupController extends Controller
{
    /**
     * Fetch the highest priority active popup for the app.
     */
    public function getActivePopup()
    {
        try {
            // Fetch highest priority active popup (Just like your Blade logic)
            $popup = Popup::where('status', 'active')
                ->orderBy('priority', 'desc')
                ->latest()
                ->first();

            if (!$popup) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active popup found',
                    'data' => null
                ], 200);
            }

            // Prepare Data for API
            return response()->json([
                'success' => true,
                'data' => [
                    'id'             => $popup->id,
                    'type'           => $popup->type, // e.g., 'offer', 'info'
                    'title'          => $popup->title,
                    'content'        => $popup->content, // HTML content
                    'image_url'      => $popup->image ? asset('storage/' . $popup->image) : null,
                    'button_text'    => $popup->button_text,
                    'button_url'     => $popup->button_url,
                    'is_dismissible' => (bool) $popup->is_dismissible,
                    'storage_key'    => 'popup_last_shown_' . $popup->id, // Mobile app storage ke liye useful
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching popup',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
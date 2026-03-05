<?php

namespace App\Http\Controllers\Api\Watchlist; // Namespace updated as per folder

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use App\Models\WatchlistScript;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    /**
     * Get all watchlists of the authenticated user.
     */
    public function index()
    {
        $watchlists = Auth::user()->watchlists()->with('scripts')->get();
        
        return response()->json([
            'success' => true,
            'data'    => $watchlists
        ], 200);
    }

    /**
     * Create a new watchlist.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50'
        ]);
        
        $watchlist = Auth::user()->watchlists()->create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Watchlist created successfully',
            'data'    => $watchlist->load('scripts')
        ], 201);
    }

    /**
     * Rename/Update a watchlist.
     */
    public function update(Request $request, Watchlist $watchlist)
    {
        if ($watchlist->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:50'
        ]);
        
        $watchlist->update([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Watchlist renamed successfully',
            'data'    => $watchlist
        ], 200);
    }

    /**
     * Delete a watchlist.
     */
    public function destroy(Watchlist $watchlist)
    {
        if ($watchlist->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        
        $watchlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Watchlist deleted successfully'
        ], 200);
    }

    /**
     * Add a script to a specific watchlist.
     */
    public function addScript(Request $request, Watchlist $watchlist)
    {
        if ($watchlist->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'symbol' => 'required|string'
        ]);

        // Prevents duplicate symbols in the same watchlist (Optional but recommended)
        $existing = $watchlist->scripts()->where('symbol', strtoupper($request->symbol))->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Script already exists in this watchlist'], 422);
        }

        $script = $watchlist->scripts()->create([
            'symbol'         => strtoupper($request->symbol),
            'trading_symbol' => strtoupper($request->symbol),
            'exchange'       => 'NSE'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Script added successfully',
            'data'    => $script
        ], 201);
    }

    /**
     * Remove a script from watchlist.
     */
    public function removeScript(WatchlistScript $script)
    {
        // Load the watchlist relation to check ownership
        if ($script->watchlist->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $script->delete();

        return response()->json([
            'success' => true,
            'message' => 'Script removed successfully'
        ], 200);
    }
}
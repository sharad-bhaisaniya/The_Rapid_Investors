<?php

namespace App\Http\Controllers\Api\Banners;

use App\Http\Controllers\Controller;
use App\Models\Marquee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarqueeApiController extends Controller
{
    /* ===================== LIST ===================== */
    public function index()
    {
        $marquees = Marquee::orderBy('display_order')->get();

        return response()->json([
            'success' => true,
            'has_marquee' => $marquees->count() > 0,
            'data' => $marquees
        ]);
    }

    /* ===================== SINGLE (EDIT) ===================== */
    public function show($id)
    {
        $marquee = Marquee::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $marquee
        ]);
    }

    /* ===================== CREATE OR UPDATE (SINGLE) ===================== */
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'nullable|string|max:255',
            'content'       => 'required|string',
            'start_at'      => 'nullable|date',
            'end_at'        => 'nullable|date|after_or_equal:start_at',
            'display_order' => 'nullable|integer',
            'is_active'     => 'nullable|boolean',
        ]);

        // 🔒 Always keep only ONE marquee
        $marquee = Marquee::updateOrCreate(
            ['id' => Marquee::first()?->id],   // if exists → update
            [
                'title'         => $request->title,
                'content'       => $request->content,
                'is_active'     => filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN),
                'start_at'      => $request->start_at,
                'end_at'        => $request->end_at,
                'display_order' => $request->display_order ?? 1,
                'updated_by'    => Auth::id(),
                'created_by'    => Marquee::first() ? null : Auth::id(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Marquee saved successfully',
            'data' => $marquee
        ]);
    }

    /* ===================== UPDATE ===================== */
    public function update(Request $request, $id)
    {
        $marquee = Marquee::findOrFail($id);

        $request->validate([
            'title'         => 'nullable|string|max:255',
            'content'       => 'required|string',
            'start_at'      => 'nullable|date',
            'end_at'        => 'nullable|date|after_or_equal:start_at',
            'display_order' => 'nullable|integer',
            'is_active'     => 'nullable|boolean',
        ]);

        $marquee->update([
            'title'         => $request->title,
            'content'       => $request->content,
            'is_active'     => filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN),
            'start_at'      => $request->start_at,
            'end_at'        => $request->end_at,
            'display_order' => $request->display_order ?? 1,
            'updated_by'    => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marquee updated successfully',
            'data' => $marquee
        ]);
    }

    /* ===================== DELETE ===================== */
    public function destroy($id)
    {
        $marquee = Marquee::findOrFail($id);
        $marquee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Marquee deleted successfully'
        ]);
    }

    /* ===================== TOGGLE ACTIVE ===================== */
    public function toggle($id)
    {
        $marquee = Marquee::findOrFail($id);

        $marquee->update([
            'is_active' => ! $marquee->is_active,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $marquee->is_active
        ]);
    }
}

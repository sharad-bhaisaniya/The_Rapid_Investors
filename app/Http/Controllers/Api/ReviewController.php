<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * GET: Approved Reviews (Public)
     */
    public function index()
    {
        $reviews = Review::approved()
            ->latest()
            ->get()
            ->map(fn($review) => $this->formatReview($review));

        return response()->json([
            'status' => true,
            'message' => 'Approved reviews fetched successfully',
            'data' => $reviews
        ]);
    }

    /**
     * POST: Store Review (Login Required)
     */
    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Please login first.'
            ], 401);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string',
            'country'=> 'nullable|string',
            'state'  => 'nullable|string',
            'city'   => 'nullable|string',
            'avatar' => 'nullable|image',
            'review_image' => 'nullable|image',
        ]);

        $review = Review::create([
            'user_id' => auth()->id(), // REQUIRED
            'name'    => auth()->user()->name,
            'email'   => auth()->user()->email,
            'rating'  => $request->rating,
            'review'  => $request->review,
            'country' => $request->country,
            'state'   => $request->state,
            'city'    => $request->city,
            'status'  => 0, // Pending
        ]);

        if ($request->hasFile('avatar')) {
            $review->addMediaFromRequest('avatar')
                ->toMediaCollection('avatar');
        }

        if ($request->hasFile('review_image')) {
            $review->addMediaFromRequest('review_image')
                ->toMediaCollection('review_images');
        }

        return response()->json([
            'status' => true,
            'message' => 'Review submitted successfully. Waiting for admin approval.',
            'data' => $this->formatReview($review)
        ]);
    }

    /**
     * PUT: Update Review
     */
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found'
            ], 404);
        }

        // Normal user can update only own review
        if (!auth()->user()->is_admin && $review->user_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to update this review'
            ], 403);
        }

        $review->update($request->only([
            'rating',
            'review',
            'country',
            'state',
            'city',
            'status',
            'is_featured'
        ]));

        // If admin approves
        if ($request->has('status') && $request->status == 1) {
            $review->approved_at = now();
            $review->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Review updated successfully',
            'data' => $this->formatReview($review)
        ]);
    }

    /**
     * DELETE: Delete Review
     */
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found'
            ], 404);
        }

        // Normal user can delete only own review
        if (!auth()->user()->is_admin && $review->user_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You are not allowed to delete this review'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'status' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Format Review
     */
    private function formatReview($review)
    {
        return [
            'id' => $review->id,
            'user_id' => $review->user_id,
            'name' => $review->name,
            'email' => $review->email,
            'rating' => $review->rating,
            'review' => $review->review,
            'country' => $review->country,
            'state' => $review->state,
            'city' => $review->city,
            'status' => $review->status,
            'is_featured' => $review->is_featured,
            'approved_at' => $review->approved_at,
            'avatar_url' => $review->getFirstMediaUrl('avatar'),
            'review_image_url' => $review->getFirstMediaUrl('review_images'),
            'created_at' => $review->created_at,
            'updated_at' => $review->updated_at,
        ];
    }
}
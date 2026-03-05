<?php 
// app/Http/Controllers/Admin/ReviewController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ReviewController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view reviews', only: ['index']),
            new Middleware('permission:edit reviews', only: ['updateStatus', 'toggleFeatured']),
            new Middleware('permission:delete reviews', only: ['destroy']),
        ];
    }

public function index(Request $request)
{
    $reviews = Review::with('user')
        ->when($request->search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        })
        ->when($request->rating, function ($query, $rating) {
            $query->where('rating', $rating);
        })
        ->when($request->user_type, function ($query, $type) {
            if ($type === 'registered') {
                $query->whereNotNull('user_id');
            } elseif ($type === 'guest') {
                $query->whereNull('user_id');
            }
        })
        ->latest()
        ->paginate(10)
        ->withQueryString(); // This keeps filters active when clicking page numbers

    return view('admin.reviews.index', compact('reviews'));
}

    public function updateStatus(Request $request, Review $review)
    {
        $request->validate([
            'status' => 'required|in:0,1,2',
        ]);

        $review->update([
            'status' => $request->status,
            'approved_at' => $request->status == 1 ? now() : null
        ]);

        return back()->with('success', 'Review status updated successfully.');
    }

    public function toggleFeatured(Review $review)
    {
        $review->update(['is_featured' => !$review->is_featured]);
        return back()->with('success', 'Featured status updated.');
    }

    public function destroy(Review $review)
{
    // If you are using Spatie Media Library and want to clear images too
    if ($review->hasMedia('avatar')) {
        $review->clearMediaCollection('avatar');
    }

    $review->delete();

    return back()->with('success', 'Review has been permanently removed.');
}
}
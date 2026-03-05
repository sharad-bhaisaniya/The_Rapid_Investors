<?php

namespace App\Http\Controllers\Api\News;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsApiController extends Controller
{
    /* ===================== LIST ===================== */
    public function index()
{
    // 1. Fetch news with category
    $news = News::with('category')->latest()->paginate(10);

    // 2. Transform the data to include media URLs
    $transformedData = collect($news->items())->map(function ($item) {
        // This gets the first media item from the 'images' collection
        $item->image_url = $item->getFirstMediaUrl('thumbnail'); 
        return $item;
    });

    return response()->json([
        'success' => true,
        'data' => $transformedData,
        'pagination' => [
            'page' => $news->currentPage(),
            'total' => $news->total(),
        ]
    ]);
}

    /* ===================== SINGLE (EDIT) ===================== */
    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => News::with('category')->findOrFail($id)
        ]);
    }

    /* ===================== CREATE ===================== */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'category_id' => 'required|exists:news_categories,id',
            'content'     => 'required',
            'status'      => 'required|in:published,draft,scheduled',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_trending'] = $request->boolean('is_trending');

        if ($request->status === 'published') {
            $data['published_at'] = now();
        }

        $news = News::create($data);

        if ($request->hasFile('thumbnail')) {
            $news->addMediaFromRequest('thumbnail')
                ->toMediaCollection('thumbnail');
        }

        return response()->json([
            'success' => true,
            'message' => 'News created successfully',
            'data' => $news
        ], 201);
    }

    /* ===================== UPDATE ===================== */
    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'category_id' => 'required|exists:news_categories,id',
            'content'     => 'required',
            'status'      => 'required|in:published,draft,scheduled',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->title);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_trending'] = $request->boolean('is_trending');

        if ($request->status === 'published' && ! $news->published_at) {
            $data['published_at'] = now();
        }

        $news->update($data);

        if ($request->hasFile('thumbnail')) {
            $news->clearMediaCollection('thumbnail');
            $news->addMediaFromRequest('thumbnail')
                ->toMediaCollection('thumbnail');
        }

        return response()->json([
            'success' => true,
            'message' => 'News updated successfully',
            'data' => $news
        ]);
    }

    /* ===================== DELETE ===================== */
    public function destroy($id)
    {
        $news = News::findOrFail($id);
        $news->clearMediaCollection('thumbnail');
        $news->delete();

        return response()->json([
            'success' => true,
            'message' => 'News deleted successfully'
        ]);
    }
}

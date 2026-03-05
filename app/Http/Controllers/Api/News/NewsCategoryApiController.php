<?php

namespace App\Http\Controllers\Api\News;

use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsCategoryApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => NewsCategory::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:news_categories,name',
        ]);

        $category = NewsCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color_code' => $request->color_code ?? '#4f46e5',
        ]);

        return response()->json([
            'success' => true,
            'data' => $category
        ], 201);
    }
    public function show($id)
{
    $category = NewsCategory::findOrFail($id);

    return response()->json([
        'success' => true,
        'data' => $category
    ]);
}


    public function update(Request $request, $id)
    {
        $category = NewsCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:news_categories,name,' . $id,
            'color_code' => 'required|string|max:7',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'color_code' => $request->color_code,
        ]);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    public function destroy($id)
    {
        $category = NewsCategory::findOrFail($id);

        if ($category->news()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with news'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true
        ]);
    }
}

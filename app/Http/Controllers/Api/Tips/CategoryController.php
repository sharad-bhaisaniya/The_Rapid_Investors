<?php

namespace App\Http\Controllers\Api\Tips;

use App\Http\Controllers\Controller;
use App\Models\TipCategory; //
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CategoryController extends Controller
{
    /**
     * Get all tip categories with their active tips.
     */
    public function index()
    {
        try {
            // Hum TipCategory model se data le rahe hain
            // 'tips' relationship ka use karke active tips fetch kar rahe hain
            $categories = TipCategory::with(['tips' => function ($query) {
                $query->where('status', 'active')
                      ->orderBy('created_at', 'desc')
                      ->with([
                              'media',      // ✅ tip attachments
                          ]);
                
                 
            }])
             // Sirf active categories ko fetch karna
            ->get();

            // Agar koi category nahi milti
            if ($categories->isEmpty()) {
                return response()->json([
                    'status'  => true,
                    'message' => 'No active categories found',
                    'data'    => []
                ], Response::HTTP_OK);
            }

              $categories->each(function ($category) {
                $category->tips->each(function ($tip) {

                    // Media formatting
                    $tip->media_files = $tip->getMedia('tip_charts')->map(function ($media) {
                        return [
                            'id'        => $media->id,
                            'url'       => $media->getFullUrl(),
                            'mime_type' => $media->mime_type,
                            'name'      => $media->name,
                        ];
                    });

                 
                    // Hide raw relations if needed
                    unset($tip->media);
                });
            });

            return response()->json([
                'status'  => true,
                'message' => 'Categories fetched successfully',
                'data'    => $categories
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PolicyMaster;

class PolicyController extends Controller
{
    /**
     * Get all enabled policies with active + all versions
     */
    public function index()
    {
        $policies = PolicyMaster::with(['activeContent', 'contents'])
            ->where('is_enabled', true)
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Policies fetched successfully',
            'data' => $policies->map(function ($policy) {

                return [
                    'id' => $policy->id,
                    'name' => $policy->name,
                    'slug' => $policy->slug,
                    'title' => $policy->title,
                    'description' => $policy->description,
                    'is_enabled' => (bool) $policy->is_enabled,
                    'created_at' => $policy->created_at,
                    'updated_at' => $policy->updated_at,

                    // Active Version
                    'active_content' => $policy->activeContent ? [
                        'id' => $policy->activeContent->id,
                        'content' => $policy->activeContent->content,
                        'updates_summary' => $policy->activeContent->updates_summary,
                        'version_number' => $policy->activeContent->version_number,
                        'is_active' => (bool) $policy->activeContent->is_active,
                        'created_at' => $policy->activeContent->created_at,
                        'updated_at' => $policy->activeContent->updated_at,
                    ] : null,

                    // All Versions
                    'all_versions' => $policy->contents->map(function ($content) {
                        return [
                            'id' => $content->id,
                            'content' => $content->content,
                            'updates_summary' => $content->updates_summary,
                            'version_number' => $content->version_number,
                            'is_active' => (bool) $content->is_active,
                            'created_at' => $content->created_at,
                            'updated_at' => $content->updated_at,
                        ];
                    }),
                ];
            })
        ]);
    }


    /**
     * Get single policy by slug
     */
    public function show($slug)
    {
        $policy = PolicyMaster::with(['activeContent', 'contents'])
            ->where('slug', $slug)
            ->where('is_enabled', true)
            ->first();

        if (!$policy) {
            return response()->json([
                'status' => false,
                'message' => 'Policy not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Policy fetched successfully',
            'data' => [
                'id' => $policy->id,
                'name' => $policy->name,
                'slug' => $policy->slug,
                'title' => $policy->title,
                'description' => $policy->description,
                'is_enabled' => (bool) $policy->is_enabled,
                'created_at' => $policy->created_at,
                'updated_at' => $policy->updated_at,

                'active_content' => $policy->activeContent,
                'all_versions' => $policy->contents
            ]
        ]);
    }
}
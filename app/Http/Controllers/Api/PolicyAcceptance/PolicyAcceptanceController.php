<?php

namespace App\Http\Controllers\Api\PolicyAcceptance;

use App\Http\Controllers\Controller;
use App\Models\PolicyAcceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PolicyAcceptanceController extends Controller
{
    /**
     * Show Active Policy (Only if user has NOT accepted)
     */
 public function show(Request $request)
{
    $user = Auth::user();

    // Safety check
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    $policy = PolicyAcceptance::where('status', 1)->first();

    if (!$policy) {
        return response()->json([
            'success' => true,
            'show_policy' => false,
            'message' => 'No active policy available.',
            'data' => null,
        ], 200);
    }

    if (!is_null($user->policy_dismissed_at)) {
        return response()->json([
            'success' => true,
            'show_policy' => false,
            'message' => 'Policy already accepted.',
            'data' => null,
        ], 200);
    }

    return response()->json([
        'success' => true,
        'show_policy' => true,
        'message' => 'Policy needs to be accepted.',
        'data' => [
            'id' => $policy->id,
            'title' => $policy->title,
            'description' => $policy->description,
            'content' => $policy->content,
        ]
    ], 200);
}
    /**
     * Accept Policy (Check & Continue Button)
     */
    public function accept(Request $request)
    {
        $user = Auth::user();

        if ($user->policy_dismissed_at !== null) {
            return response()->json([
                'status' => false,
                'message' => 'Policy already accepted.',
            ], 200);
        }

        $user->update([
            'policy_dismissed_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Policy accepted successfully.',
        ], 200);
    }
}
<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
class CustomerDetailsController extends Controller
{
    /**
     * Return authenticated customer full details with KYC & Subscription
     */
public function index(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'status'  => false,
            'message' => 'Unauthenticated',
        ], 401);
    }

    // Base user data
    $userData = $user->toArray();
    
    // ✅ Profile Image URL add yahan karein
    $userData['profile_image_url'] = $user->getFirstMediaUrl('profile_image');

    // KYC
    $userData['kyc'] = \App\Models\KycVerification::where('user_id', $user->id)->first();

    // Subscription
    $subscription = \App\Models\UserSubscription::where('user_id', $user->id)
        ->where('status', 'active')
        ->first();

    $userData['subscription'] = $subscription;

    // Plan
    $userData['plan'] = null;
    if ($subscription && $subscription->service_plan_id) {
        $userData['plan'] = \App\Models\ServicePlan::where(
            'id',
            $subscription->service_plan_id
        )->first();
    }

    // Tips
    $userData['tips'] = [];
    if ($subscription && $subscription->service_plan_id) {

        $tipIds = \App\Models\TipPlanAccess::where(
            'service_plan_id',
            $subscription->service_plan_id
        )->pluck('tip_id');

        // $userData['tips'] = \App\Models\Tip::whereIn('id', $tipIds)
        //     ->with('category')
        //     ->get();

        $userData['tips'] = \App\Models\Tip::whereIn('id', $tipIds)
    ->with('category','media')
    ->get()
    ->map(function ($tip) {

        // ✅ ADD ONLY TIP MEDIA
        $tip->media_files = $tip->getMedia('tip_charts')->map(function ($media) {
            return [
                'id'        => $media->id,
                'url'       => $media->getFullUrl(),
                'mime_type' => $media->mime_type,
                'name'      => $media->file_name,
            ];
        });

        return $tip;
    });
    }

    /**
     * ✅ INVOICES + COUPON DETAILS
     */

    $invoices = \App\Models\Invoice::where('user_id', $user->id)
        ->where('user_subscription_id', optional($subscription)->id)
        ->orderByDesc('id')
        ->get();

    foreach ($invoices as $invoice) {

        $couponUsage = \App\Models\CouponUsage::where('invoice_id', $invoice->id)->first();

        if ($couponUsage) {
            $invoice->coupon = \App\Models\Coupon::find($couponUsage->coupon_id);
        } else {
            $invoice->coupon = null;
        }
    }

    $userData['invoices'] = $invoices;

    return response()->json([
        'status'  => true,
        'message' => 'Customer profile fetched successfully',
        'data'    => [
            'user' => $userData
        ],
    ], 200);
}



}

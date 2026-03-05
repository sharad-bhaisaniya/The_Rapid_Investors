<?php

namespace App\Http\Controllers\Api\Subscription;

use App\Http\Controllers\Controller;
use App\Models\ServicePlan;
use App\Models\ServicePlanDuration;
use App\Models\UserSubscription;
use App\Models\Invoice;
use App\Models\KycVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use App\Models\Coupon;
class MobileSubscriptionController extends Controller
{
    /**
     * 1️⃣ GET PLANS FOR MOBILE
     */
    public function plans()
    {
        $user = Auth::user();

        $kyc = KycVerification::where('user_id', $user->id)
            ->latest()
            ->first();

        $plans = ServicePlan::with('durations.features')
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'kyc_status' => $kyc->status ?? 'none',
            'plans' => $plans
        ]);
    }



    /**
     *  COUPON APPLY 
     */


    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'duration_id' => 'required|exists:service_plan_durations,id',
        ]);

        $user = Auth::user();

        // 🔍 Fetch duration
        $duration = ServicePlanDuration::findOrFail($request->duration_id);

        // 🔍 Find coupon
        $coupon = Coupon::where('code', strtoupper($request->coupon_code))
            ->active()
            ->notExpired()
            ->first();

        // ❌ Invalid coupon
        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon code'
            ], 422);
        }

        // ❌ Global limit reached
        if ($coupon->isGlobalLimitReached()) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon has reached its usage limit'
            ], 422);
        }

        // ❌ Per user limit
        $used = \App\Models\CouponUsage::where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->sum('times_used');

        if ($used >= $coupon->per_user_limit) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this coupon maximum times'
            ], 422);
        }

        // ❌ Minimum amount
        if (!$coupon->isApplicableOn($duration->price)) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon applicable only above ₹' . $coupon->min_amount
            ], 422);
        }

        // ✅ Calculate discount
        $discount = $coupon->calculateDiscount($duration->price);
        $finalAmount = max(0, $duration->price - $discount);

        Log::info('Mobile Coupon Applied', [
            'user_id' => $user->id,
            'coupon' => $coupon->code,
            'original_price' => $duration->price,
            'discount' => $discount,
            'final_amount' => $finalAmount
        ]);

        return response()->json([
            'success' => true,
            'coupon' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
            'original_price' => number_format($duration->price, 2),
            'discount' => number_format($discount, 2),
            'final_price' => number_format($finalAmount, 2),
            'message' => 'Coupon applied successfully'
        ]);
    }




   

    public function initiateRazorpay(Request $request)
{
    $request->validate([
        'plan_id'     => 'required|exists:service_plans,id',
        'duration_id' => 'required|exists:service_plan_durations,id',
        'coupon_code' => 'nullable|string',
    ]);

    $user = Auth::user();

    $duration = ServicePlanDuration::where('id', $request->duration_id)
        ->where('service_plan_id', $request->plan_id)
        ->firstOrFail();

    // ✅ Base price
    $finalPrice = $duration->price;
    $coupon = null;

    // ✅ Coupon logic (same as web)
    if ($request->filled('coupon_code')) {
        $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))
            ->active()
            ->notExpired()
            ->first();

        if (!$coupon || $coupon->isGlobalLimitReached() || !$coupon->isApplicableOn($duration->price)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon'
            ], 422);
        }

        $discount = $coupon->calculateDiscount($duration->price);
        $finalPrice = max(0, $duration->price - $discount);
    }

    $amountInPaise = (int) round($finalPrice * 100);

    $api = new Api(
        config('services.razorpay.key'),
        config('services.razorpay.secret')
    );

    $order = $api->order->create([
        'receipt' => 'sub_' . uniqid(),
        'amount'  => $amountInPaise,
        'currency'=> 'INR',
    ]);

    return response()->json([
        'success' => true,
        'order_id'=> $order['id'],
        'amount'  => $amountInPaise,
        'key'     => config('services.razorpay.key'),
        'user'    => [
            'name'    => $user->name,
            'email'   => $user->email,
            'contact' => $user->phone ?? ''
        ]
    ]);
}


   
    public function verifyRazorpay(Request $request)
{
    $request->validate([
        'razorpay_order_id'   => 'required',
        'razorpay_payment_id'=> 'required',
        'razorpay_signature' => 'required',
        'plan_id'             => 'required|exists:service_plans,id',
        'duration_id'         => 'required|exists:service_plan_durations,id',
        'coupon_code'         => 'nullable|string',
    ]);

    $user = Auth::user();

    $api = new Api(
        config('services.razorpay.key'),
        config('services.razorpay.secret')
    );

    try {
        $api->utility->verifyPaymentSignature([
            'razorpay_order_id'   => $request->razorpay_order_id,
            'razorpay_payment_id'=> $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ]);
    } catch (SignatureVerificationError $e) {
        return response()->json([
            'success' => false,
            'message' => 'Payment verification failed'
        ], 422);
    }

    $duration = ServicePlanDuration::findOrFail($request->duration_id);

    // ✅ DATE CALCULATION (MONTH / DAY SAFE)
    $startDate = now();
    $endDate   = now();

    $value = (int) filter_var($duration->duration, FILTER_SANITIZE_NUMBER_INT);

    if (str_contains(strtolower($duration->duration), 'month')) {
        $endDate = $startDate->copy()->addMonths($value);
    } elseif (str_contains(strtolower($duration->duration), 'day')) {
        $endDate = $startDate->copy()->addDays($value);
    } else {
        $endDate = $startDate->copy()->addDays($duration->duration_days ?? 30);
    }

    // ✅ PRICE + COUPON
    $finalAmount = $duration->price;
    $coupon = null;

    if ($request->filled('coupon_code')) {
        $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))
            ->active()
            ->notExpired()
            ->first();

        if ($coupon && $coupon->isApplicableOn($duration->price)) {
            $discount = $coupon->calculateDiscount($duration->price);
            $finalAmount = max(0, $duration->price - $discount);
        }
    }

    DB::beginTransaction();

    try {

        // 🔴 Expire old
        UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update([
                'status' => 'expired',
                'end_date' => now()
            ]);

        // 🟢 New subscription
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'service_plan_id' => $request->plan_id,
            'service_plan_duration_id' => $request->duration_id,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'status' => 'active',
            'payment_status' => 'paid',
            'amount' => $finalAmount,
            'currency' => 'INR',
            'payment_gateway' => 'razorpay',
            'payment_reference' => $request->razorpay_payment_id,
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ]);

        // 🔢 Invoice
        $last = Invoice::lockForUpdate()->orderByDesc('id')->first();
        $num = $last ? ((int) substr($last->invoice_number, 3)) + 1 : 1;

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'user_subscription_id' => $subscription->id,
            'invoice_number' => 'INV' . str_pad($num, 6, '0', STR_PAD_LEFT),
            'amount' => $finalAmount,
            'currency' => 'INR',
            'payment_gateway' => 'razorpay',
            'payment_reference' => $request->razorpay_payment_id, // Fixed here
            'invoice_date' => now(),
            'service_start_date' => $startDate, // Fixed here
            'service_end_date' => $endDate,     // Fixed here
        ]);

        // 🎟 Coupon usage tracking
        if ($coupon) {
            $coupon->increment('used_global');

            \App\Models\CouponUsage::updateOrCreate(
                ['coupon_id' => $coupon->id, 'user_id' => $user->id],
                ['invoice_id' => $invoice->id, 'times_used' => DB::raw('times_6Cused + 1')]
            );
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'subscription_id' => $subscription->id,
            'invoice_number' => $invoice->invoice_number
        ]);

    } catch (\Exception $e) {

        DB::rollBack();
        Log::error('Mobile Verify Failed', ['error' => $e->getMessage()]);

        return response()->json([
            'success' => false,
            'message' => 'Subscription failed'
        ], 500);
    }
}


    /**
     * 4️⃣ CURRENT SUBSCRIPTION
     */
    public function currentSubscription()
    {
        $sub = UserSubscription::with('plan', 'duration')
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'subscription' => $sub
        ]);
    }

    /**
     * 5️⃣ INVOICE LIST
     */
    public function invoiceList()
    {
        try {
            $invoices = Invoice::with([
                    'subscription.plan',
                    'subscription.duration',
                    'coupon'
                ])
                ->where('user_id', Auth::id())
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Invoices fetched successfully',
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 6️⃣ DOWNLOAD INVOICE PDF (MOBILE WEBVIEW)
     */
    public function downloadInvoice($id)
    {
        $invoice = Invoice::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'download_url' => route('invoice.download', $invoice->id)
        ]);
    }



    // All Coupons Get (Full Response)
        public function allCoupons()
        {
            try {
                $coupons = Coupon::where('active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now());
                    })
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(function ($coupon) {
                        return [
                            'id'              => $coupon->id,
                            'code'            => $coupon->code,
                            'type'            => $coupon->type,          // flat | percent
                            'value'           => $coupon->value,
                            'min_amount'      => $coupon->min_amount,
                            'per_user_limit'  => $coupon->per_user_limit,
                            'global_limit'    => $coupon->global_limit,
                            'used_global'     => $coupon->used_global,
                            'remaining_global'=> max(0, $coupon->global_limit - $coupon->used_global),
                            'expires_at'      => $coupon->expires_at,
                            'is_expired'      => $coupon->expires_at 
                                                ? $coupon->expires_at->isPast() 
                                                : false,
                            'status'          => $coupon->active ? 'active' : 'inactive',
                            'created_at'      => $coupon->created_at,
                            'updated_at'      => $coupon->updated_at,
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'message' => 'Coupons fetched successfully',
                    'total'   => $coupons->count(),
                    'data'    => $coupons
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch coupons',
                    'error'   => $e->getMessage()
                ], 500);
            }
        }

}

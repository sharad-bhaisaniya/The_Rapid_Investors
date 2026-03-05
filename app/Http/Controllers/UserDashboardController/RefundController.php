<?php

namespace App\Http\Controllers\UserDashboardController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserSubscription;
use App\Models\Invoice;
use App\Models\RefundRequest;

class RefundController extends Controller
{
   public function requestRefund(Request $request)
{
    $request->validate([
        'subscription_id' => 'required|exists:user_subscriptions,id',
        'refund_reason'   => 'required|string|min:10',
    ]);

    $subscription = UserSubscription::where('id', $request->subscription_id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    /**
     * 🔒 BLOCK DEMO & UNPAID SUBSCRIPTIONS
     */
    if (
        $subscription->payment_status !== 'paid' ||
        $subscription->payment_gateway !== 'razorpay'
    ) {
        return back()->with('error', 'Refund is allowed only for paid subscriptions.');
    }

    // Find related invoice
    $invoice = Invoice::where('user_subscription_id', $subscription->id)->first();

    if (!$invoice) {
        return back()->with('error', 'Invoice not found for this subscription.');
    }

    /**
     * 🔒 PREVENT DUPLICATE REFUND REQUEST
     */
    $alreadyRequested = RefundRequest::where('invoice_id', $invoice->id)
        ->whereIn('status', ['requested', 'approved', 'refunded'])
        ->exists();

    if ($alreadyRequested) {
        return back()->with('error', 'Refund request already submitted.');
    }

    RefundRequest::create([
        'user_id'               => auth()->id(),
        'user_subscription_id'  => $subscription->id,
        'invoice_id'            => $invoice->id,
        'razorpay_payment_id'   => $subscription->razorpay_payment_id,
        'razorpay_order_id'     => $subscription->razorpay_order_id,
        'refund_amount'         => $invoice->amount,
        'refund_reason'         => $request->refund_reason,
        'status'                => 'requested',
    ]);

    return back()->with('success', 'Refund request submitted successfully.');
}
}
<?php

namespace App\Http\Controllers\Admin\Refund;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\UserSubscription;
use App\Models\Invoice;

class RefundController extends Controller
{
    public function index()
    {
        $refunds = RefundRequest::with(['user','subscription','invoice'])
            ->where('status', 'refunded')
            ->latest('refunded_at')
            ->paginate(10);

        return view('admin.refund.index', compact('refunds'));
    }

    public function show($id)
    {
        $refund = RefundRequest::with(['user','subscription','invoice','refundedBy'])
            ->where('status','refunded')
            ->findOrFail($id);

        return view('admin.refund.show', compact('refund'));
    }

    /**
     * OPTIONAL: manual refund entry
     */
        public function store(Request $request)
    {
        Log::info('Refund store request received', [
            'admin_id' => auth()->id(),
            'payload'  => $request->except(['refund_proof_image'])
        ]);

        try {

            // 1️⃣ Validate ONLY what admin sends
            $data = $request->validate([
                'user_id'               => 'required|integer',
                'user_subscription_id'  => 'required|integer',
                'transaction_id'        => 'required|string',
                'refund_amount'         => 'required|numeric',
                'refund_reason'         => 'required|string',
                'admin_note'            => 'nullable|string',
                'refund_proof_image'    => 'nullable|image|max:2048',
            ]);

            Log::debug('Refund validation passed', $data);

            // 2️⃣ Fetch subscription
            $subscription = UserSubscription::where('id', $data['user_subscription_id'])
                ->where('user_id', $data['user_id'])
                ->first();

            if (!$subscription) {
                Log::warning('Refund failed: subscription not found or mismatch', $data);

                return back()->with('error', 'Invalid subscription for this user.');
            }

            // 3️⃣ Fetch invoice USING subscription (single source of truth)
            $invoice = Invoice::where('user_subscription_id', $subscription->id)
                ->latest('id')
                ->first();

            if (!$invoice) {
                Log::warning('Refund failed: invoice not found for subscription', [
                    'subscription_id' => $subscription->id
                ]);

                return back()->with('error', 'No invoice found for this subscription.');
            }

            Log::info('Invoice matched with subscription', [
                'subscription_id' => $subscription->id,
                'invoice_id'      => $invoice->id
            ]);

            // 4️⃣ Handle proof image
            if ($request->hasFile('refund_proof_image')) {
                $data['refund_proof_image'] = $request->file('refund_proof_image')
                    ->store('refunds/proofs', 'public');

                Log::info('Refund proof image uploaded', [
                    'path' => $data['refund_proof_image']
                ]);
            }

            // 5️⃣ Create refund record (AUTO-INJECT invoice_id)
            $refund = RefundRequest::create([
                ...$data,
                'invoice_id'  => $invoice->id,
                'status'      => 'refunded',
                'refunded_by' => auth()->id(),
                'refunded_at' => now(),
            ]);

            Log::info('Refund recorded successfully', [
                'refund_id' => $refund->id,
                'user_id'   => $refund->user_id,
                'invoice_id'=> $invoice->id,
                'amount'    => $refund->refund_amount,
            ]);

            // 6️⃣ Cancel the subscription
            $subscription->update([
                'status'        => 'cancelled',
                'cancelled_at'  => now(), // optional but recommended
            ]);

            Log::info('Subscription cancelled due to refund', [
                'subscription_id' => $subscription->id,
                'user_id'         => $subscription->user_id,
            ]);

            return redirect()
                ->route('admin.refund.index')
                ->with('success', 'Refund recorded successfully');

        } catch (\Throwable $e) {

            Log::error('Refund creation failed', [
                'admin_id' => auth()->id(),
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Something went wrong while recording the refund.');
        }
    }
}
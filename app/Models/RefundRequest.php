<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    use HasFactory;

    protected $table = 'refund_requests';

    protected $fillable = [
        'user_id',
        'user_subscription_id',
        'invoice_id',

        // transaction
        'transaction_id',
        'razorpay_payment_id',
        'razorpay_order_id',
        'razorpay_refund_id',
        'payment_gateway',
       
         // refund
        'refund_amount',
        'refund_reason',
        'refund_proof_image',

         // admin
        'admin_note',
        'admin_id',
        'status',
        'refunded_by',
        'refunded_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'refunded_at'   => 'datetime',
    ];

    /* =========================
       Relationships
    ========================= */

    // Refund belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Refund belongs to Subscription (READ ONLY reference)
    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }

    // Refund belongs to Invoice
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Admin who approved/rejected
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
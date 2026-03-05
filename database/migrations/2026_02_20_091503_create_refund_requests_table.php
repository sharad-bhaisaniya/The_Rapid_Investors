<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('refund_requests', function (Blueprint $table) {

            $table->id();

            // Relations
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('user_subscription_id');
            $table->unsignedBigInteger('invoice_id');

            // Razorpay references
            $table->string('razorpay_payment_id', 100);
            $table->string('razorpay_order_id', 100)->nullable();
            $table->string('razorpay_refund_id', 100)->nullable();

            // Refund info
            $table->decimal('refund_amount', 10, 2);
            $table->text('refund_reason')->nullable();

            // Status lifecycle
            $table->enum('status', [
                'requested',
                'approved',
                'rejected',
                'refunded'
            ])->default('requested');

            // Admin action
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('admin_note')->nullable();

            // Timestamps
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            // Indexes (important)
            $table->index('user_id');
            $table->index('user_subscription_id');
            $table->index('invoice_id');
            $table->index('razorpay_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
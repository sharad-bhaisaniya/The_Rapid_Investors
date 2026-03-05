<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {

            // REMOVE REQUEST FLOW
            if (Schema::hasColumn('refund_requests', 'requested_at')) {
                $table->dropColumn('requested_at');
            }
            if (Schema::hasColumn('refund_requests', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            // TRANSACTION DETAILS
            $table->string('transaction_id')->after('invoice_id');
            $table->string('payment_gateway')->default('razorpay')->after('transaction_id');

            // PROOF IMAGE
            $table->string('refund_proof_image')->nullable()->after('refund_reason');

            // ADMIN META
            $table->unsignedBigInteger('refunded_by')->nullable()->after('status');

            // FORCE STATUS
            $table->string('status')->default('refunded')->change();
        });
    }

    public function down(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->dropColumn([
                'transaction_id',
                'payment_gateway',
                'refund_proof_image',
                'refunded_by',
                'refunded_at',
            ]);
        });
    }
};
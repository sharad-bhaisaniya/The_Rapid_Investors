<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_agreements', function (Blueprint $table) {
            $table->id();

            // ❌ NO FOREIGN KEYS — only normal columns
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('subscription_id')->nullable()->index();
            $table->unsignedBigInteger('invoice_id')->index();

            $table->string('invoice_number')->index();
            $table->string('agreement_number')->unique(); // AGR-2025-0001

            $table->timestamp('signed_at')->nullable();

            // Snapshot JSONs
            $table->json('user_snapshot');
            $table->json('kyc_snapshot');
            $table->json('subscription_snapshot');
            $table->json('invoice_snapshot');

            $table->boolean('is_signed')->default(false);
            $table->string('status')->default('generated'); // generated | signed | revoked

            $table->timestamps();

            // Logical uniqueness (without FK)
            $table->unique(['user_id', 'invoice_id']); // 1 invoice = 1 agreement
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_agreements');
    }
};

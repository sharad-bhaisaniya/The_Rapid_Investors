<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Adding 'suspended' to the ENUM list
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('active', 'expired', 'cancelled', 'suspended') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // Reverting back to original statuses
        // Note: Ensure no 'suspended' data exists before rolling back, or they will become empty strings
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active'");
    }
};
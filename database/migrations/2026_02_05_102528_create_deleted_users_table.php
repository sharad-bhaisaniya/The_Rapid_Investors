<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('deleted_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_user_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // Backup of all profile details
            $table->json('full_profile_data'); 
            
            $table->string('reason_for_deletion')->nullable();
            $table->timestamp('deleted_at_time')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deleted_users');
    }
};

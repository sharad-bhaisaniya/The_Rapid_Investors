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
    Schema::create('angel_scrips', function (Blueprint $table) {
        $table->id();
        $table->string('token'); 
        $table->string('symbol')->nullable();
        $table->string('name')->nullable();
        $table->string('expiry')->nullable(); // Using string because API returns "" (empty string)
        $table->decimal('strike', 16, 6)->nullable(); // Decimal handles "0.000000" string automatically
        $table->string('lotsize')->nullable();
        $table->string('instrumenttype')->nullable();
        $table->string('exch_seg'); 
        $table->decimal('tick_size', 16, 6)->nullable();
        $table->timestamps();

        // Unique Index for Upsert Logic
        $table->unique(['token', 'exch_seg']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('angel_scrips');
    }
};

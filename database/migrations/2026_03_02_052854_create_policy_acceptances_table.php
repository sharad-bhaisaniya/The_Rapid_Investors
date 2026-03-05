<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_acceptances', function (Blueprint $table) {
            $table->id();

            // Policy Title
            $table->string('title');

            // Short Description
            $table->text('description')->nullable();

            // Full Content (Rich Text / HTML from editor)
            $table->longText('content');

            // Status (1 = Active, 0 = Inactive)
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_acceptances');
    }
};
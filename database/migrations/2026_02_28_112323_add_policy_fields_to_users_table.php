<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('policy_dismissed_at')->nullable()->after('remember_token');
            $table->string('policy_version')->nullable()->after('policy_dismissed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['policy_dismissed_at', 'policy_version']);
        });
    }
};
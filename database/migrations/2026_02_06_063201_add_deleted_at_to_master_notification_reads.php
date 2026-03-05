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
    Schema::table('master_notification_reads', function (Blueprint $table) {
        $table->timestamp('deleted_at')->nullable()->after('user_id');
    });
}

public function down()
{
    Schema::table('master_notification_reads', function (Blueprint $table) {
        $table->dropColumn('deleted_at');
    });
}
};

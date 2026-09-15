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
        Schema::table('tbl_notif', function (Blueprint $table) {
            $table->string('template_name')->nullable()->after('pesan_notifikasi');
            $table->string('template_params')->nullable()->after('template_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_notif', function (Blueprint $table) {
            $table->dropColumn(['template_name', 'template_params']);
        });
    }
};

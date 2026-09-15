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
        Schema::table('tbl_waba_chat', function (Blueprint $table) {
            $table->text('media_url')->nullable()->after('pesan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_waba_chat', function (Blueprint $table) {
            $table->dropColumn('media_url');
        });
    }
};

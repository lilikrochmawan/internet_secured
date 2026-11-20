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
            if (!Schema::hasColumn('tbl_waba_chat', 'message_id')) {
                $table->string('message_id')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_waba_chat', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_waba_chat', 'message_id')) {
                $table->dropColumn('message_id');
            }
        });
    }
};

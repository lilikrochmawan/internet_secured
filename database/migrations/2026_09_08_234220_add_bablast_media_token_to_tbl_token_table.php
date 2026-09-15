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
        Schema::table('tbl_token', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_token', 'wa_gateway')) {
                $table->string('wa_gateway')->default('fonnte')->after('status');
            }
            if (!Schema::hasColumn('tbl_token', 'bablast_token')) {
                $table->string('bablast_token')->nullable()->after('wa_gateway');
            }
            if (!Schema::hasColumn('tbl_token', 'bablast_media_token')) {
                $table->string('bablast_media_token')->nullable()->after('bablast_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_token', function (Blueprint $table) {
            //
        });
    }
};

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
        if (Schema::hasTable('tbl_order_pemasangan')) {
            Schema::table('tbl_order_pemasangan', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_order_pemasangan', 'alasan_pending')) {
                    $table->text('alasan_pending')->nullable()->after('alasan_batal');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tbl_order_pemasangan')) {
            Schema::table('tbl_order_pemasangan', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_order_pemasangan', 'alasan_pending')) {
                    $table->dropColumn('alasan_pending');
                }
            });
        }
    }
};

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
                if (!Schema::hasColumn('tbl_order_pemasangan', 'alasan_ditolak')) {
                    $table->text('alasan_ditolak')->nullable()->after('foto_dokumentasi');
                }
                if (!Schema::hasColumn('tbl_order_pemasangan', 'alasan_batal')) {
                    $table->text('alasan_batal')->nullable()->after('alasan_ditolak');
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
                if (Schema::hasColumn('tbl_order_pemasangan', 'alasan_ditolak')) {
                    $table->dropColumn('alasan_ditolak');
                }
                if (Schema::hasColumn('tbl_order_pemasangan', 'alasan_batal')) {
                    $table->dropColumn('alasan_batal');
                }
            });
        }
    }
};

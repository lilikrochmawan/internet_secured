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
        if (!Schema::hasColumn('tbl_order_pemasangan', 'paket')) {
            Schema::table('tbl_order_pemasangan', function (Blueprint $table) {
                $table->integer('paket')->nullable()->after('no_telp');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tbl_order_pemasangan', 'paket')) {
            Schema::table('tbl_order_pemasangan', function (Blueprint $table) {
                $table->dropColumn('paket');
            });
        }
    }
};

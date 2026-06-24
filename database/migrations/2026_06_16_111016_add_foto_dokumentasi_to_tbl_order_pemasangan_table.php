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
        Schema::table('tbl_order_pemasangan', function (Blueprint $table) {
            $table->string('foto_dokumentasi', 255)->nullable()->after('foto_ktp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_order_pemasangan', function (Blueprint $table) {
            $table->dropColumn('foto_dokumentasi');
        });
    }
};

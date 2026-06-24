<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_profile', function (Blueprint $table) {
            $table->string('tipe_jatuh_tempo', 50)->default('tanggal_tetap')->after('nip_ktu');
            $table->integer('hari_jatuh_tempo')->default(10)->after('tipe_jatuh_tempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_profile', function (Blueprint $table) {
            $table->dropColumn(['tipe_jatuh_tempo', 'hari_jatuh_tempo']);
        });
    }
};

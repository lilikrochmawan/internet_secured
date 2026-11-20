<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan tabel tb_paket ada
        if (Schema::hasTable('tb_paket')) {
            $exists = DB::table('tb_paket')->where('nama_paket', 'Free')->orWhere('harga', 0)->exists();
            if (!$exists) {
                DB::table('tb_paket')->insert([
                    'nama_paket' => 'Free',
                    'harga' => 0,
                    'ppn' => 0,
                    'id_pmikrotik' => 'default',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tb_paket')) {
            DB::table('tb_paket')->where('nama_paket', 'Free')->where('harga', 0)->delete();
        }
    }
};

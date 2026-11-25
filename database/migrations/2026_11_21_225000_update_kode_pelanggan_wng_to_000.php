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
        // Ubah semua kode_pelanggan di tb_pelanggan dari awalan WNG menjadi 000
        DB::statement("UPDATE tb_pelanggan SET kode_pelanggan = REPLACE(kode_pelanggan, 'WNG', '000') WHERE kode_pelanggan LIKE 'WNG%'");

        // Perbarui juga catatan riwayat di tb_kas jika ada yang mengandung WNG
        DB::statement("UPDATE tb_kas SET keterangan = REPLACE(keterangan, 'WNG', '000') WHERE keterangan LIKE '%WNG%'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE tb_pelanggan SET kode_pelanggan = CONCAT('WNG', SUBSTRING(kode_pelanggan, 4)) WHERE kode_pelanggan LIKE '000%'");
        DB::statement("UPDATE tb_kas SET keterangan = REPLACE(keterangan, '00003100', 'WNG03100') WHERE keterangan LIKE '%00003100%'");
    }
};

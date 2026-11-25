<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tb_pelanggan') && Schema::hasColumn('tb_pelanggan', 'kode_pelanggan')) {
            DB::statement("UPDATE tb_pelanggan SET kode_pelanggan = CONCAT('000', SUBSTRING(TRIM(kode_pelanggan), 4)) WHERE UPPER(TRIM(kode_pelanggan)) LIKE 'WNG%'");
        }

        if (Schema::hasTable('tb_kas') && Schema::hasColumn('tb_kas', 'keterangan')) {
            DB::statement("UPDATE tb_kas SET keterangan = REPLACE(keterangan, 'WNG', '000') WHERE keterangan LIKE '%WNG%'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Idempotent data fix migration
    }
};

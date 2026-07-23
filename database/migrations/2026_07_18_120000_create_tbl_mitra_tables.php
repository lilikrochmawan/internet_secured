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
        if (!Schema::hasTable('tbl_mitra_config')) {
            Schema::create('tbl_mitra_config', function (Blueprint $table) {
                $table->id();
                $table->integer('id_user')->unique();
                $table->string('tipe_komisi', 20);
                $table->decimal('nilai_komisi', 15, 2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tbl_mitra_komisi_logs')) {
            Schema::create('tbl_mitra_komisi_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('id_user');
                $table->integer('id_tagihan');
                $table->integer('id_pelanggan');
                $table->decimal('jumlah_bayar', 15, 2);
                $table->string('tipe_komisi', 20);
                $table->decimal('nilai_komisi', 15, 2);
                $table->decimal('komisi_diterima', 15, 2);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('tbl_mitra_payouts')) {
            Schema::create('tbl_mitra_payouts', function (Blueprint $table) {
                $table->id();
                $table->integer('id_user');
                $table->integer('payout_month');
                $table->integer('payout_year');
                $table->decimal('jumlah', 15, 2);
                $table->date('tgl_payout');
                $table->text('catatan')->nullable();
                $table->string('bukti_transfer', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_mitra_config');
        Schema::dropIfExists('tbl_mitra_komisi_logs');
        Schema::dropIfExists('tbl_mitra_payouts');
    }
};

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
        Schema::create('tb_promo', function (Blueprint $table) {
            $table->id('id_promo');
            $table->string('nama_promo', 150);
            $table->integer('id_pelanggan');
            $table->integer('id_paket');
            $table->integer('mulai_bulan');
            $table->integer('mulai_tahun');
            $table->integer('selesai_bulan');
            $table->integer('selesai_tahun');
            $table->integer('nominal_tagihan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_promo');
    }
};

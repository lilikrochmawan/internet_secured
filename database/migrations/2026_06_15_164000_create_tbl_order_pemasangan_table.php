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
        Schema::create('tbl_order_pemasangan', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 50);
            $table->string('nama', 255);
            $table->text('alamat_ktp');
            $table->text('alamat_pemasangan');
            $table->string('koordinat_pemasangan', 100);
            $table->dateTime('jadwal_pemasangan')->nullable();
            $table->string('foto_ktp', 255)->nullable();
            $table->string('status', 30)->default('pending'); // pending, approved, installed
            $table->integer('id_sales')->nullable(); // sales who created it (references tb_user.id)
            $table->integer('id_teknisi')->nullable(); // technician assigned (references tb_user.id)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_order_pemasangan');
    }
};

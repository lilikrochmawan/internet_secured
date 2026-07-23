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
        if (!Schema::hasTable('tbl_deleted_pelanggan_history')) {
            Schema::create('tbl_deleted_pelanggan_history', function (Blueprint $table) {
                $table->id();
                $table->integer('id_pelanggan')->nullable();
                $table->string('nama_pelanggan', 255);
                $table->text('alamat')->nullable();
                $table->string('nik', 50)->nullable();
                $table->text('location')->nullable();
                $table->text('alasan_hapus');
                $table->integer('deleted_by');
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_deleted_pelanggan_history');
    }
};

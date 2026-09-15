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
        Schema::create('tbl_waba_chat', function (Blueprint $table) {
            $table->id();
            $table->string('no_telp')->index();
            $table->string('nama')->nullable();
            $table->text('pesan');
            $table->string('tipe')->default('incoming'); // incoming, outgoing
            $table->string('status')->nullable(); // read, delivered, etc
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_waba_chat');
    }
};

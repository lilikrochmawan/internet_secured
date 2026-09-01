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
        Schema::create('tb_olt', function (Blueprint $table) {
            $table->id('id_olt');
            $table->string('nama_olt', 100);
            $table->string('ip_address', 45);
            $table->integer('port')->default(22);
            $table->enum('protokol', ['ssh', 'telnet'])->default('ssh');
            $table->string('username', 100);
            $table->string('password', 255);
            $table->string('snmp_community', 100)->default('public');
            $table->enum('tipe_olt', ['zte', 'huawei', 'hsgq', 'vsol', 'cdata', 'global'])->default('zte');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_olt');
    }
};

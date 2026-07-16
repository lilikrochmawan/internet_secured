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
        Schema::table('tbl_keluhan', function (Blueprint $table) {
            $table->integer('id_pelanggan')->nullable()->change();
            $table->string('no_wa', 15)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_keluhan', function (Blueprint $table) {
            $table->integer('id_pelanggan')->nullable(false)->change();
            $table->string('no_wa', 15)->nullable(false)->change();
        });
    }
};

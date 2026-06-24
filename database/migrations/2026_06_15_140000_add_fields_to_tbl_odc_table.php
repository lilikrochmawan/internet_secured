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
        Schema::table('tbl_odc', function (Blueprint $table) {
            $table->string('redaman', 50)->nullable()->after('location');
            $table->string('tube', 50)->nullable()->after('redaman');
            $table->integer('core_number')->nullable()->after('tube');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_odc', function (Blueprint $table) {
            $table->dropColumn(['redaman', 'tube', 'core_number']);
        });
    }
};

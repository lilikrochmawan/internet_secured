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
            if (!Schema::hasColumn('tbl_odc', 'jenis_odc')) {
                $table->string('jenis_odc', 20)->default('utama');
            }
            if (!Schema::hasColumn('tbl_odc', 'parent_id')) {
                $table->integer('parent_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_odc', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_odc', 'jenis_odc')) {
                $table->dropColumn('jenis_odc');
            }
            if (Schema::hasColumn('tbl_odc', 'parent_id')) {
                $table->dropColumn('parent_id');
            }
        });
    }
};

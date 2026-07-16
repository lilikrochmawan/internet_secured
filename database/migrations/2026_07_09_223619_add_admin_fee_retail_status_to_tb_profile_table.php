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
        if (!Schema::hasColumn('tb_profile', 'admin_fee_retail_status')) {
            Schema::table('tb_profile', function (Blueprint $table) {
                $table->boolean('admin_fee_retail_status')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tb_profile', 'admin_fee_retail_status')) {
            Schema::table('tb_profile', function (Blueprint $table) {
                $table->dropColumn('admin_fee_retail_status');
            });
        }
    }
};

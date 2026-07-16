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
        Schema::table('tb_profile', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_profile', 'admin_fee_qris_status')) {
                $table->boolean('admin_fee_qris_status')->default(true);
            }
            if (!Schema::hasColumn('tb_profile', 'admin_fee_va_status')) {
                $table->boolean('admin_fee_va_status')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_profile', function (Blueprint $table) {
            if (Schema::hasColumn('tb_profile', 'admin_fee_qris_status')) {
                $table->dropColumn('admin_fee_qris_status');
            }
            if (Schema::hasColumn('tb_profile', 'admin_fee_va_status')) {
                $table->dropColumn('admin_fee_va_status');
            }
        });
    }
};

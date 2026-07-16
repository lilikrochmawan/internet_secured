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
            $table->string('admin_fee_type')->default('flat'); // 'flat', 'payment_method'
            $table->integer('admin_fee_flat')->default(2000);
            $table->string('admin_fee_qris_type')->default('percentage'); // 'percentage', 'flat'
            $table->decimal('admin_fee_qris_value', 8, 2)->default(0.70);
            $table->integer('admin_fee_va')->default(4000);
            $table->integer('admin_fee_retail')->default(3000);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_profile', function (Blueprint $table) {
            $table->dropColumn([
                'admin_fee_type',
                'admin_fee_flat',
                'admin_fee_qris_type',
                'admin_fee_qris_value',
                'admin_fee_va',
                'admin_fee_retail',
            ]);
        });
    }
};

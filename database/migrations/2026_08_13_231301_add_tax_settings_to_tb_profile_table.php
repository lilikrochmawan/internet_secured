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
            $table->string('tax_ppn_status', 10)->default('tidak');
            $table->decimal('tax_ppn_rate', 5, 2)->default(11.00);
            $table->string('tax_bhp_status', 10)->default('aktif');
            $table->decimal('tax_bhp_rate', 5, 2)->default(0.50);
            $table->string('tax_uso_status', 10)->default('aktif');
            $table->decimal('tax_uso_rate', 5, 2)->default(1.25);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_profile', function (Blueprint $table) {
            $table->dropColumn([
                'tax_ppn_status',
                'tax_ppn_rate',
                'tax_bhp_status',
                'tax_bhp_rate',
                'tax_uso_status',
                'tax_uso_rate'
            ]);
        });
    }
};

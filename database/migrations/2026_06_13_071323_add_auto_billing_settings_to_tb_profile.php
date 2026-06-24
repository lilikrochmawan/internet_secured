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
            $table->tinyInteger('auto_send_billing')->default(0)->after('sistem_billing');
            $table->integer('auto_send_date')->default(5)->after('auto_send_billing');
            $table->integer('auto_send_h_minus')->default(3)->after('auto_send_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_profile', function (Blueprint $table) {
            $table->dropColumn(['auto_send_billing', 'auto_send_date', 'auto_send_h_minus']);
        });
    }
};

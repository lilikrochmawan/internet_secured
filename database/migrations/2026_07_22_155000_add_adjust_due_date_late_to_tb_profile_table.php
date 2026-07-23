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
            if (!Schema::hasColumn('tb_profile', 'adjust_due_date_late')) {
                $table->tinyInteger('adjust_due_date_late')->default(0)->after('auto_send_h_minus');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_profile', function (Blueprint $table) {
            if (Schema::hasColumn('tb_profile', 'adjust_due_date_late')) {
                $table->dropColumn('adjust_due_date_late');
            }
        });
    }
};

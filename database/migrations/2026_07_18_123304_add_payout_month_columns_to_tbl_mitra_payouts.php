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
        Schema::table('tbl_mitra_payouts', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_mitra_payouts', 'payout_month')) {
                $table->integer('payout_month')->after('id_user');
            }
            if (!Schema::hasColumn('tbl_mitra_payouts', 'payout_year')) {
                $table->integer('payout_year')->after('payout_month');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_mitra_payouts', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_mitra_payouts', 'payout_month')) {
                $table->dropColumn('payout_month');
            }
            if (Schema::hasColumn('tbl_mitra_payouts', 'payout_year')) {
                $table->dropColumn('payout_year');
            }
        });
    }
};

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
            $table->string('license_plan_name')->nullable()->default('Lite')->after('license_expires_at');
            $table->integer('license_max_clients')->default(250)->after('license_plan_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_profile', function (Blueprint $table) {
            $table->dropColumn(['license_plan_name', 'license_max_clients']);
        });
    }
};

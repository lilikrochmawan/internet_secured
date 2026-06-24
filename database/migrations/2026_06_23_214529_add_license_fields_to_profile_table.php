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
            $table->string('license_key')->nullable()->after('foto');
            $table->string('license_status')->default('invalid')->after('license_key');
            $table->dateTime('license_expires_at')->nullable()->after('license_status');
            $table->dateTime('license_last_checked')->nullable()->after('license_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_profile', function (Blueprint $table) {
            $table->dropColumn(['license_key', 'license_status', 'license_expires_at', 'license_last_checked']);
        });
    }
};

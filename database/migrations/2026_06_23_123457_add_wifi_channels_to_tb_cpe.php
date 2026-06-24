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
        Schema::table('tb_cpe', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_cpe', 'wifi_channel_24')) {
                $table->string('wifi_channel_24', 50)->nullable()->default(null);
            }
            if (!Schema::hasColumn('tb_cpe', 'wifi_channel_5')) {
                $table->string('wifi_channel_5', 50)->nullable()->default(null);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_cpe', function (Blueprint $table) {
            if (Schema::hasColumn('tb_cpe', 'wifi_channel_24')) {
                $table->dropColumn('wifi_channel_24');
            }
            if (Schema::hasColumn('tb_cpe', 'wifi_channel_5')) {
                $table->dropColumn('wifi_channel_5');
            }
        });
    }
};

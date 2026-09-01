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
        Schema::table('tb_user', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_user', 'api_token')) {
                $table->string('api_token', 80)->after('password')
                            ->unique()
                            ->nullable()
                            ->default(null);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_user', function (Blueprint $table) {
            if (Schema::hasColumn('tb_user', 'api_token')) {
                $table->dropColumn('api_token');
            }
        });
    }
};

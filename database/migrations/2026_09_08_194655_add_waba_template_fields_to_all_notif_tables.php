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
        $tables = [
            'tbl_notifreminder',
            'tbl_blokir',
            'tbl_notifbayar',
            'tbl_bukablokir',
            'tbl_notifpromo',
            'tbl_npemasangan'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('template_name')->nullable();
                $table->string('template_params')->nullable();
                $table->string('template_language')->nullable()->default('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'tbl_notifreminder',
            'tbl_blokir',
            'tbl_notifbayar',
            'tbl_bukablokir',
            'tbl_notifpromo',
            'tbl_npemasangan'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['template_name', 'template_params', 'template_language']);
            });
        }
    }
};

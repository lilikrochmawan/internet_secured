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
        Schema::table('tbl_odp', function (Blueprint $table) {
            $table->boolean('has_ratio')->default(false)->after('port_odp');
            $table->unsignedBigInteger('parent_odp_id')->nullable()->after('has_ratio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_odp', function (Blueprint $table) {
            //
        });
    }
};

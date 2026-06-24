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
        // 1. Create tb_branch
        Schema::create('tb_branch', function (Blueprint $table) {
            $table->id();
            $table->string('nama_branch');
            $table->text('deskripsi')->nullable();
        });

        // 2. Create tb_sub_branch
        Schema::create('tb_sub_branch', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_branch');
            $table->string('nama_sub_branch');
            $table->text('deskripsi')->nullable();

            $table->foreign('id_branch')->references('id')->on('tb_branch')->onDelete('cascade');
        });

        // 3. Add columns to tb_pelanggan
        Schema::table('tb_pelanggan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_branch')->nullable()->after('id_mikrotik');
            $table->unsignedBigInteger('id_sub_branch')->nullable()->after('id_branch');

            // We use standard column types for FK references, but avoid strict FK constraints if types differ (tb_branch is bigInt, tb_pelanggan has its own structure).
            // Let's add standard indexes instead of strict constraints to avoid type mismatch issues with legacy DB tables.
            $table->index('id_branch');
            $table->index('id_sub_branch');
        });

        // 4. Create tb_user_branch_access
        Schema::create('tb_user_branch_access', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user'); // Match tb_user.id type (int)
            $table->unsignedBigInteger('id_branch');
            $table->unsignedBigInteger('id_sub_branch')->nullable();

            $table->foreign('id_branch')->references('id')->on('tb_branch')->onDelete('cascade');
            $table->foreign('id_sub_branch')->references('id')->on('tb_sub_branch')->onDelete('cascade');
            $table->index('id_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_user_branch_access');

        Schema::table('tb_pelanggan', function (Blueprint $table) {
            $table->dropColumn(['id_branch', 'id_sub_branch']);
        });

        Schema::dropIfExists('tb_sub_branch');
        Schema::dropIfExists('tb_branch');
    }
};


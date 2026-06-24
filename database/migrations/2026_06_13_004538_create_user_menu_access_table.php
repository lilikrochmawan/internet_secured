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
        Schema::create('tb_user_menu_access', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user'); // Matches tb_user.id type (int)
            $table->string('menu_key', 50);
            
            $table->unique(['id_user', 'menu_key']);
            $table->index('id_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_user_menu_access');
    }
};


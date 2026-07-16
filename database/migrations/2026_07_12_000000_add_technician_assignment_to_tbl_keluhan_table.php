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
        Schema::table('tbl_keluhan', function (Blueprint $table) {
            $table->string('status_keluhan', 50)->default('menunggu')->change();
            
            $table->integer('teknisi_id')->nullable()->after('user_id');
            $table->boolean('assign_to_all')->default(false)->after('teknisi_id');
            $table->text('tindakan')->nullable()->after('assign_to_all');
            $table->string('bukti_foto', 255)->nullable()->after('tindakan');

            // Set foreign key to tb_user
            $table->foreign('teknisi_id')->references('id')->on('tb_user')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_keluhan', function (Blueprint $table) {
            $table->dropForeign(['teknisi_id']);
            $table->dropColumn(['teknisi_id', 'assign_to_all', 'tindakan', 'bukti_foto']);
            
            // Revert status_keluhan to original enum
            $table->enum('status_keluhan', ['menunggu', 'proses', 'selesai', 'tidak merespon'])->default('menunggu')->change();
        });
    }
};

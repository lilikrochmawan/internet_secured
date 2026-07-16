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
            
            if (!Schema::hasColumn('tbl_keluhan', 'teknisi_id')) {
                $table->integer('teknisi_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('tbl_keluhan', 'assign_to_all')) {
                $table->boolean('assign_to_all')->default(false)->after('teknisi_id');
            }
            if (!Schema::hasColumn('tbl_keluhan', 'tindakan')) {
                $table->text('tindakan')->nullable()->after('assign_to_all');
            }
            if (!Schema::hasColumn('tbl_keluhan', 'bukti_foto')) {
                $table->string('bukti_foto', 255)->nullable()->after('tindakan');
            }
        });

        try {
            Schema::table('tbl_keluhan', function (Blueprint $table) {
                $table->foreign('teknisi_id')->references('id')->on('tb_user')->onDelete('set null');
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('tbl_keluhan', function (Blueprint $table) {
                $table->dropForeign(['teknisi_id']);
            });
        } catch (\Exception $e) {}

        Schema::table('tbl_keluhan', function (Blueprint $table) {
            $cols = ['teknisi_id', 'assign_to_all', 'tindakan', 'bukti_foto'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('tbl_keluhan', $col)) {
                    $table->dropColumn($col);
                }
            }
            $table->enum('status_keluhan', ['menunggu', 'proses', 'selesai', 'tidak merespon'])->default('menunggu')->change();
        });
    }
};

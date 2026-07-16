<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tbl_notifpromo')) {
            Schema::create('tbl_notifpromo', function (Blueprint $table) {
                $table->id('id_notifpromo');
                $table->text('pesan_promo');
                $table->string('status_promo', 20)->default('aktif');
                $table->timestamps();
            });

            // Insert default promo notification template (using single quotes to avoid PHP variable interpolation)
            DB::table('tbl_notifpromo')->insert([
                'pesan_promo' => 'Halo Bapak/Ibu *$nama*' . "\n\n" .
                                 'Semoga Bapak/Ibu dalam keadaan sehat dan baik.' . "\n\n" .
                                 'Kami sampaikan bahwa promo *$nama_promo* telah diaktifkan untuk akun Anda. Rincian promo Anda:' . "\n\n" .
                                 'Periode Promo: *$mulai_promo* sampai *$selesai_promo*' . "\n" .
                                 'Nominal Tagihan Awal: *Rp $tagihan*' . "\n\n" .
                                 'Tagihan bulanan Anda untuk periode promo tersebut akan otomatis terbayar (Lunas).' . "\n\n" .
                                 'Terima kasih atas kepercayaan Anda menggunakan layanan kami.',
                'status_promo' => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_notifpromo');
    }
};

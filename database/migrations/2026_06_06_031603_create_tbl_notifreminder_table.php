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
        Schema::create('tbl_notifreminder', function (Blueprint $table) {
            $table->increments('id_reminder');
            $table->enum('status_reminder', ['aktif', 'tidakaktif'])->default('aktif');
            $table->text('pesan_reminder');
        });

        // Seed initial/default reminder template
        DB::table('tbl_notifreminder')->insert([
            'status_reminder' => 'aktif',
            'pesan_reminder' => "Halo Bapak/Ibu *\$nama*\n\nMeningatkan kembali bahwa tagihan layanan internet Anda sebesar *Rp. \$tagihan* akan/telah memasuki masa jatuh tempo pada tanggal *\$jatuh_tempo*.\n\nMohon segera melakukan pembayaran agar dapat menikmati layanan internet tanpa hambatan. Jika Anda sudah melakukan pembayaran, abaikan pesan ini.\n\nTerimakasih."
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_notifreminder');
    }
};

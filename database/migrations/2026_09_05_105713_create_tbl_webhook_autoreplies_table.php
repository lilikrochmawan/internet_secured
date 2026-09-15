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
        Schema::create('tbl_webhook_autoreplies', function (Blueprint $table) {
            $table->id();
            $table->string('tipe')->unique(); // e.g., 'halo', 'tagihan_lunas', 'tagihan_tunggak', 'paket_internet'
            $table->string('keyword')->nullable(); // e.g., 'PAKET', 'HALO'
            $table->text('pesan');
            $table->string('media_path')->nullable(); // for image URL/path
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // Insert Default Data
        DB::table('tbl_webhook_autoreplies')->insert([
            [
                'tipe' => 'halo',
                'keyword' => 'HALO, PING',
                'pesan' => "Halo! Ini adalah sistem layanan otomatis. \nKetik *CEK TAGIHAN* untuk mengecek informasi tagihan Anda.",
                'media_path' => null,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipe' => 'tagihan_lunas',
                'keyword' => 'CEK TAGIHAN, INFO TAGIHAN',
                'pesan' => "Halo *{nama}*,\n\nTerima kasih, saat ini **tidak ada tagihan yang tertunggak** (Lunas). Terima kasih telah berlangganan!",
                'media_path' => null,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipe' => 'tagihan_tunggak',
                'keyword' => null, // triggered by same keyword as lunas
                'pesan' => "Halo *{nama}*,\nBerikut adalah informasi tagihan Anda yang belum dibayar:\n\n{list_tagihan}\n*Total Tunggakan: {total_tunggakan}*\n\nSilakan lakukan pembayaran agar layanan internet tetap berjalan lancar. Terima kasih.",
                'media_path' => null,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tipe' => 'paket_internet',
                'keyword' => 'PAKET, BROSUR, HARGA',
                'pesan' => "Halo Kak!\nBerikut adalah daftar harga dan brosur paket internet kami. Jika ada yang ingin ditanyakan, silakan balas pesan ini ya.",
                'media_path' => null,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_webhook_autoreplies');
    }
};

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Tagihan;

class SendAutoBillingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-auto-billing-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis mengirimkan notifikasi tagihan bulanan kepada pelanggan via WhatsApp Fonnte API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Ambil pengaturan profile
        $profile = DB::table('tb_profile')->where('id_profile', 1)->first();
        if (!$profile) {
            $this->error('Pengaturan profil (tb_profile) tidak ditemukan.');
            return Command::FAILURE;
        }

        // Cek apakah fitur pengiriman otomatis diaktifkan
        if (($profile->auto_send_billing ?? 0) != 1) {
            $this->info('Fitur pengiriman tagihan otomatis dinonaktifkan.');
            return Command::SUCCESS;
        }

        // Cek status global notifikasi WhatsApp
        $notifSetting = DB::table('tbl_notif')->first();
        if (!$notifSetting || $notifSetting->status_notifikasi !== 'aktif') {
            $this->warn('Fitur notifikasi WhatsApp dinonaktifkan secara global di pengaturan custom pesan.');
            Log::warning('SendAutoBillingNotifications: Notifikasi WhatsApp dinonaktifkan secara global.');
            return Command::SUCCESS;
        }

        // Cek token Fonnte
        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        if (!$tokenInfo || empty($tokenInfo->token)) {
            $this->error('Token WhatsApp Fonnte belum dikonfigurasi di pengaturan.');
            Log::error('SendAutoBillingNotifications: Token Fonnte belum dikonfigurasi.');
            return Command::FAILURE;
        }

        $tipe = $profile->tipe_jatuh_tempo ?? 'tanggal_tetap';
        $auto_date = $profile->auto_send_date ?? 5;
        $auto_h_minus = $profile->auto_send_h_minus ?? 3;

        $this->info("Menjalankan pengiriman tagihan otomatis. Tipe Jatuh Tempo: {$tipe}");
        Log::info("SendAutoBillingNotifications: Memulai pengiriman otomatis. Tipe Jatuh Tempo: {$tipe}");

        $bills = collect();

        if ($tipe === 'tanggal_tetap') {
            // Cek apakah hari ini sama dengan tanggal kirim otomatis
            $todayDay = (int) date('d');
            if ($todayDay !== (int) $auto_date) {
                $this->info("Hari ini tanggal {$todayDay}. Tanggal kirim otomatis dikonfigurasi pada tanggal {$auto_date}. Proses diabaikan.");
                return Command::SUCCESS;
            }

            // Ambil tagihan yang belum lunas dan belum dikirim untuk periode bulan ini
            $currentPeriod = date('mY'); // format mY e.g. 062026
            $bills = Tagihan::with('pelanggan')
                ->where('bulan_tahun', $currentPeriod)
                ->whereNull('status_bayar')
                ->where(function($q) {
                    $q->whereNull('terkirim')->orWhere('terkirim', '<>', 'terkirim');
                })
                ->get();
        } else {
            // Tipe Jatuh Tempo: tanggal_pasang
            // Ambil tagihan yang jatuh temponya tepat auto_h_minus hari dari hari ini
            $targetDate = Carbon::today()->addDays($auto_h_minus)->toDateString();
            
            $bills = Tagihan::with('pelanggan')
                ->whereNull('status_bayar')
                ->where(function($q) {
                    $q->whereNull('terkirim')->orWhere('terkirim', '<>', 'terkirim');
                })
                ->whereDate('jatuh_tempo', $targetDate)
                ->get();
        }

        if ($bills->isEmpty()) {
            $this->info('Tidak ada tagihan belum dibayar yang memenuhi kriteria pengiriman hari ini.');
            Log::info('SendAutoBillingNotifications: Tidak ada tagihan untuk dikirim hari ini.');
            return Command::SUCCESS;
        }

        $this->info("Ditemukan " . $bills->count() . " tagihan untuk dikirim.");
        Log::info("SendAutoBillingNotifications: Ditemukan " . $bills->count() . " tagihan untuk dikirim.");

        $successCount = 0;
        $failCount = 0;

        foreach ($bills as $index => $tx) {
            $pelanggan = $tx->pelanggan;
            if (!$pelanggan || empty($pelanggan->no_telp)) {
                $this->warn("Tagihan ID {$tx->id_tagihan} diabaikan karena data pelanggan tidak ditemukan atau nomor telepon kosong.");
                $failCount++;
                continue;
            }

            // Beri jeda 10 detik untuk mencegah spam filter/rate limit dari Fonnte (kecuali pesan pertama)
            if ($index > 0) {
                sleep(10);
            }

            // Format isi pesan menggunakan template notifikasi
            $pesan = $notifSetting->pesan_notifikasi;
            $pesan = str_replace('$nama', $pelanggan->nama_pelanggan, $pesan);
            $pesan = str_replace('$no_telp', $pelanggan->no_telp, $pesan);
            $pesan = str_replace('$jatuh_tempo', Carbon::parse($tx->jatuh_tempo)->translatedFormat('d F Y') ?? $pelanggan->jatuh_tempo, $pesan);
            $pesan = str_replace('$tagihan', number_format($tx->jml_bayar, 0, ',', '.'), $pesan);
            $pesan = str_replace('$hari_ini', Carbon::now()->translatedFormat('d F Y'), $pesan);

            try {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => $tokenInfo->token
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $pelanggan->no_telp,
                    'message' => $pesan,
                    'countryCode' => '62'
                ]);

                $resData = $response->json();
                if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                    $tx->update(['terkirim' => 'terkirim']);
                    $successCount++;
                    $this->info("Berhasil mengirim notifikasi tagihan ke {$pelanggan->nama_pelanggan} ({$pelanggan->no_telp})");
                } else {
                    $failCount++;
                    $reason = $resData['reason'] ?? $resData['message'] ?? 'Device Fonnte terputus atau token tidak valid';
                    $this->error("Gagal mengirim notifikasi ke {$pelanggan->nama_pelanggan}: {$reason}");
                    Log::error("SendAutoBillingNotifications: Gagal mengirim ke {$pelanggan->nama_pelanggan}. Fonnte: {$reason}");
                }
            } catch (\Exception $e) {
                $failCount++;
                $this->error("Exception saat mengirim ke {$pelanggan->nama_pelanggan}: " . $e->getMessage());
                Log::error("SendAutoBillingNotifications: Exception ke {$pelanggan->nama_pelanggan}: " . $e->getMessage());
            }
        }

        $this->info("Proses pengiriman otomatis selesai. Berhasil: {$successCount}, Gagal: {$failCount}");
        Log::info("SendAutoBillingNotifications: Selesai. Berhasil: {$successCount}, Gagal: {$failCount}");

        return Command::SUCCESS;
    }
}

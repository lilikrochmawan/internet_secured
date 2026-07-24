<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\User;

class AutoBlockPelanggan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-block-pelanggan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis memblokir pelanggan yang belum membayar tagihan melewati jatuh tempo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil pengaturan blokir kustom
        $blokirSetting = DB::table('tbl_blokir')->first();
        if (!$blokirSetting || ($blokirSetting->status_blokir ?? '') !== 'aktif') {
            $this->info('Fitur pemblokiran otomatis dinonaktifkan di pengaturan custom pesan.');
            Log::info('AutoBlockPelanggan: Fitur pemblokiran otomatis dinonaktifkan di pengaturan custom pesan.');
            return Command::SUCCESS;
        }

        // Ambil info sistem billing global
        $settings = DB::table('tb_profile')->first();
        $sistem = $settings->sistem_billing ?? 'prabayar';

        $this->info('Memulai pengecekan tagihan jatuh tempo... Sistem: ' . strtoupper($sistem));
        Log::info('AutoBlockPelanggan: Memulai proses pemblokiran otomatis. Sistem: ' . $sistem);

        // Cari tagihan yang belum bayar, belum diblokir, dan jatuh tempo sudah lewat (hanya untuk bulan berjalan saja)
        $now = now();
        $currentPeriod = now()->format('mY');
        $overdueBills = Tagihan::with('pelanggan')
            ->whereNull('status_bayar')
            ->where(function ($query) {
                $query->whereNull('blokir_status')
                      ->orWhere('blokir_status', 0);
            })
            ->where('bulan_tahun', $currentPeriod)
            ->where('jatuh_tempo', '<', $now)
            ->get();

        if ($overdueBills->isEmpty()) {
            $this->info('Tidak ada tagihan jatuh tempo yang perlu diblokir.');
            Log::info('AutoBlockPelanggan: Tidak ada tagihan jatuh tempo yang perlu diblokir.');
            return Command::SUCCESS;
        }

        $this->info('Ditemukan ' . $overdueBills->count() . ' tagihan overdue yang akan diblokir.');
        Log::info('AutoBlockPelanggan: Ditemukan ' . $overdueBills->count() . ' tagihan overdue.');

        require_once base_path('include/routeros_api.php');
        $checkUser = DB::table('tbl_penggunamikrotik')->first();
        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
        $blokirSetting = DB::table('tbl_blokir')->first();

        // Hubungkan ke router berdasarkan device mikrotik masing-masing
        $connections = [];
        $processedCustomerIds = [];

        foreach ($overdueBills as $tx) {
            $pelanggan = $tx->pelanggan;
            if (!$pelanggan) {
                $this->error('Tagihan ID ' . $tx->id_tagihan . ' tidak memiliki data pelanggan.');
                continue;
            }

            // Cegah pengiriman dobel jika pelanggan memiliki lebih dari satu tagihan menunggak
            if (in_array($pelanggan->id_pelanggan, $processedCustomerIds)) {
                $tx->update(['blokir_status' => 1]);
                $this->info("Pelanggan {$pelanggan->nama_pelanggan} memiliki tunggakan lain yang sudah diproses. Tandai status blokir tagihan ini dan lewati.");
                continue;
            }
            $processedCustomerIds[] = $pelanggan->id_pelanggan;

            // Cek apakah tagihan terbaru pelanggan ini sudah lunas. Jika lunas, abaikan blokir untuk tagihan lama.
            $latestBill = Tagihan::where('id_pelanggan', $pelanggan->id_pelanggan)
                ->orderBy('id_tagihan', 'desc')
                ->first();

            if ($latestBill && $latestBill->status_bayar == 1) {
                $this->info("Pelanggan {$pelanggan->nama_pelanggan} memiliki tagihan menunggak (ID {$tx->id_tagihan}), tetapi tagihan terbaru sudah lunas. Lewati blokir.");
                Log::info("AutoBlockPelanggan: Pelanggan {$pelanggan->nama_pelanggan} memiliki tagihan menunggak (ID {$tx->id_tagihan}), tetapi tagihan terbaru sudah lunas. Lewati blokir.");
                continue;
            }

            $id_mikrotik = $pelanggan->id_mikrotik ?: 1;
            $user = User::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

            // Ambil data mikrotik
            $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();
            if (!$mikrotik) {
                $this->error('Mikrotik ID ' . $id_mikrotik . ' tidak ditemukan untuk pelanggan: ' . $pelanggan->nama_pelanggan);
                Log::error('AutoBlockPelanggan: Mikrotik ID ' . $id_mikrotik . ' tidak ditemukan untuk ' . $pelanggan->nama_pelanggan);
                continue;
            }

            // Hubungkan jika belum terhubung
            if (!isset($connections[$id_mikrotik])) {
                $API = new \RouterosAPI();
                $API->timeout = 5;
                if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                    $connections[$id_mikrotik] = $API;
                } else {
                    $this->error('Gagal terhubung ke Mikrotik: ' . $mikrotik->nama_mikrotik);
                    Log::error('AutoBlockPelanggan: Gagal terhubung ke Mikrotik ID ' . $id_mikrotik);
                    continue;
                }
            }

            $API = $connections[$id_mikrotik];
            $success = false;

            try {
                if ($checkUser && $checkUser->ippelanggan === 'statik') {
                    // Blokir Statik: Tambah IP ke Address-List
                    $API->comm("/ip/firewall/address-list/add", [
                        "list"     => "blocked_clients",
                        "address"  => $pelanggan->ip_address,
                        "comment"  => "Blokir Bulanan " . $pelanggan->ip_address
                    ]);
                    $success = true;
                } else {
                    // Blokir PPPOE: Disable Secret & Disconnect Active Connection
                    if ($user) {
                        $API->comm("/ppp/secret/set", [
                            "numbers" => $user->username,
                            "profile" => "pppoe-isolir",
                        ]);
                        $API->comm("/ppp/secret/enable", [
                            "numbers" => $user->username,
                        ]);

                        // Cari dan putuskan koneksi aktif
                        $activeConnections = $API->comm("/ppp/active/print", [
                            "?name" => $user->username,
                        ]);

                        foreach ($activeConnections as $conn) {
                            $API->comm("/ppp/active/remove", [
                                ".id" => $conn['.id'],
                            ]);
                        }
                        $success = true;
                    } else {
                        $this->error('Pengguna (user) tidak ditemukan untuk pelanggan: ' . $pelanggan->nama_pelanggan);
                        Log::warning('AutoBlockPelanggan: User tidak ditemukan untuk ' . $pelanggan->nama_pelanggan);
                    }
                }

                if ($success) {
                    // Update status blokir di database
                    $tx->update(['blokir_status' => 1]);
                    $this->info('Berhasil memblokir pelanggan: ' . $pelanggan->nama_pelanggan);
                    Log::info('AutoBlockPelanggan: Berhasil memblokir ' . $pelanggan->nama_pelanggan);

                    // Kirim Notifikasi WhatsApp Pemblokiran
                    if ($blokirSetting && ($blokirSetting->status_blokir ?? '') === 'aktif' && $tokenInfo && !empty($tokenInfo->token) && !empty($pelanggan->no_telp)) {
                        $pesan = $blokirSetting->pesan_blokir;
                        $pesan = str_replace('$nama', $pelanggan->nama_pelanggan, $pesan);
                        $pesan = str_replace('$no_telp', $pelanggan->no_telp, $pesan);
                        $pesan = str_replace('$kode_pelanggan', $pelanggan->kode_pelanggan, $pesan);
                        $pesan = str_replace('$tagihan', number_format($tx->jml_bayar, 0, ',', '.'), $pesan);
                        $pesan = str_replace('$jatuh_tempo', \Carbon\Carbon::parse($tx->jatuh_tempo)->translatedFormat('d F Y') ?? $pelanggan->jatuh_tempo, $pesan);
                        $pesan = str_replace('$hari_ini', \Carbon\Carbon::now()->translatedFormat('d F Y'), $pesan);

                        try {
                            $response = \Illuminate\Support\Facades\Http::timeout(10)->withHeaders([
                                'Authorization' => $tokenInfo->token
                            ])->asForm()->post('https://api.fonnte.com/send', [
                                'target' => $pelanggan->no_telp,
                                'message' => $pesan,
                                'countryCode' => '62'
                            ]);

                            $resData = $response->json();
                            if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                                $this->info('Notifikasi WA pemblokiran terkirim ke: ' . $pelanggan->nama_pelanggan);
                                Log::info('AutoBlockPelanggan: Notifikasi WA pemblokiran terkirim ke ' . $pelanggan->nama_pelanggan);
                            } else {
                                $reason = $resData['reason'] ?? $resData['message'] ?? 'Device Fonnte tidak aktif.';
                                $this->warn('Gagal kirim WA pemblokiran ke ' . $pelanggan->nama_pelanggan . ': ' . $reason);
                                Log::warning('AutoBlockPelanggan: Gagal kirim WA pemblokiran ke ' . $pelanggan->nama_pelanggan . ': ' . $reason);
                            }
                        } catch (\Exception $e) {
                            $this->error('Exception kirim WA pemblokiran ke ' . $pelanggan->nama_pelanggan . ': ' . $e->getMessage());
                            Log::error('AutoBlockPelanggan: Exception kirim WA pemblokiran ke ' . $pelanggan->nama_pelanggan . ': ' . $e->getMessage());
                        }

                        // Jeda 5 detik antar pengiriman pesan WA untuk menghindari rate limit Fonnte
                        sleep(5);
                    }
                }

            } catch (\Exception $e) {
                $this->error('Error memblokir pelanggan ' . $pelanggan->nama_pelanggan . ': ' . $e->getMessage());
                Log::error('AutoBlockPelanggan: Exception saat memblokir ' . $pelanggan->nama_pelanggan . ': ' . $e->getMessage());
            }
        }

        // --- LOGIKA SELF-HEALING: Buka blokir otomatis bagi pelanggan yang sudah Lunas / belum Jatuh Tempo ---
        $this->info('Memulai pengecekan self-healing untuk membuka blokir pelanggan yang sudah lunas/aktif...');
        Log::info('AutoBlockPelanggan: Memulai pengecekan self-healing unblock.');

        $blockedBillsInDb = Tagihan::with('pelanggan')
            ->where('blokir_status', 1)
            ->get();

        $processedUnblockCustomerIds = [];

        foreach ($blockedBillsInDb as $tx) {
            $pelanggan = $tx->pelanggan;
            if (!$pelanggan) continue;

            // Prevent duplicate Mikrotik unblocking requests/disconnects for the same customer in the same run
            if (in_array($pelanggan->id_pelanggan, $processedUnblockCustomerIds)) {
                $tx->update(['blokir_status' => null]);
                continue;
            }
            $processedUnblockCustomerIds[] = $pelanggan->id_pelanggan;

            // Cek status tagihan bulan berjalan
            $currentBill = Tagihan::where('id_pelanggan', $pelanggan->id_pelanggan)
                ->where('bulan_tahun', $currentPeriod)
                ->first();

            $shouldUnblock = true;
            if ($currentBill && $currentBill->status_bayar != 1) {
                // Fix timezone parsing discrepancy using Carbon comparison instead of native php strtotime
                if (\Carbon\Carbon::parse($currentBill->jatuh_tempo)->lt($now)) {
                    $shouldUnblock = false;
                }
            }

            if ($shouldUnblock) {
                $id_mikrotik = $pelanggan->id_mikrotik ?: 1;
                $user = User::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

                // Ambil data mikrotik
                $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();
                if (!$mikrotik) continue;

                // Hubungkan jika belum terhubung
                if (!isset($connections[$id_mikrotik])) {
                    $API = new \RouterosAPI();
                    $API->timeout = 5;
                    if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                        $connections[$id_mikrotik] = $API;
                    } else {
                        Log::error('AutoBlockPelanggan Self-Healing: Gagal terhubung ke Mikrotik ID ' . $id_mikrotik);
                        continue;
                    }
                }

                $API = $connections[$id_mikrotik];
                $unblockedSuccessfully = false;

                try {
                    if ($checkUser && $checkUser->ippelanggan === 'statik') {
                        // Buka Blokir Statik: Hapus IP dari Address-List
                        $commentToSearch = "Blokir Bulanan " . $pelanggan->ip_address;
                        $API->write('/ip/firewall/address-list/print', false);
                        $API->write('?comment=' . $commentToSearch);
                        $ips = $API->read();

                        if (!empty($ips)) {
                            foreach ($ips as $ip_data) {
                                $API->write('/ip/firewall/address-list/remove', false);
                                $API->write('=.id=' . $ip_data['.id']);
                                $API->read();
                            }
                        }
                        $unblockedSuccessfully = true;
                    } else {
                        // Buka Blokir PPPOE: Kembalikan profile paket asal
                        if ($user) {
                            $paket = DB::table('tb_paket')->where('id_paket', $pelanggan->paket)->first();
                            $profile = $paket ? $paket->id_pmikrotik : 'default';

                            $API->comm("/ppp/secret/set", [
                                "numbers" => $user->username,
                                "profile" => $profile,
                            ]);
                            $API->comm("/ppp/secret/enable", [
                                "numbers" => $user->username,
                            ]);

                            // Putuskan koneksi aktif agar dial ulang
                            $activeConnections = $API->comm("/ppp/active/print", [
                                "?name" => $user->username,
                            ]);

                            foreach ($activeConnections as $conn) {
                                $API->comm("/ppp/active/remove", [
                                    ".id" => $conn['.id'],
                                ]);
                            }
                            $unblockedSuccessfully = true;
                        }
                    }

                    if ($unblockedSuccessfully) {
                        $tx->update(['blokir_status' => null]);
                        $this->info("Self-healing: Berhasil membuka blokir pelanggan {$pelanggan->nama_pelanggan}");
                        Log::info("AutoBlockPelanggan Self-Healing: Berhasil membuka blokir {$pelanggan->nama_pelanggan}");
                    }
                } catch (\Exception $ex) {
                    Log::error("AutoBlockPelanggan Self-Healing: Error membuka blokir {$pelanggan->nama_pelanggan}: " . $ex->getMessage());
                }
            }
        }

        // Putus koneksi semua router
        foreach ($connections as $API) {
            $API->disconnect();
        }

        $this->info('Pengecekan dan pemblokiran selesai.');
        Log::info('AutoBlockPelanggan: Proses pemblokiran otomatis selesai.');

        return Command::SUCCESS;
    }
}

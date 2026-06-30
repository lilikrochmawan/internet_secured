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
        // Ambil info sistem billing global
        $settings = DB::table('tb_profile')->first();
        $sistem = $settings->sistem_billing ?? 'prabayar';

        $this->info('Memulai pengecekan tagihan jatuh tempo... Sistem: ' . strtoupper($sistem));
        Log::info('AutoBlockPelanggan: Memulai proses pemblokiran otomatis. Sistem: ' . $sistem);

        // Cari tagihan yang belum bayar, belum diblokir, dan jatuh tempo sudah lewat
        $now = now();
        $overdueBills = Tagihan::with('pelanggan')
            ->whereNull('status_bayar')
            ->where(function ($query) {
                $query->whereNull('blokir_status')
                      ->orWhere('blokir_status', 0);
            })
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

        // Hubungkan ke router berdasarkan device mikrotik masing-masing
        $connections = [];

        foreach ($overdueBills as $tx) {
            $pelanggan = $tx->pelanggan;
            if (!$pelanggan) {
                $this->error('Tagihan ID ' . $tx->id_tagihan . ' tidak memiliki data pelanggan.');
                continue;
            }

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
                }

            } catch (\Exception $e) {
                $this->error('Error memblokir pelanggan ' . $pelanggan->nama_pelanggan . ': ' . $e->getMessage());
                Log::error('AutoBlockPelanggan: Exception saat memblokir ' . $pelanggan->nama_pelanggan . ': ' . $e->getMessage());
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

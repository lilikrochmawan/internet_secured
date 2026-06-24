<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class CheckLicenseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:check';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Melakukan pemeriksaan lisensi harian ke Server Lisensi pusat.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan lisensi harian...');

        // 1. Ambil data profil/lisensi
        $profile = DB::table('tb_profile')->where('id_profile', 1)->first();
        if (!$profile || empty($profile->license_key)) {
            $this->warn('License Key tidak diatur atau tabel tb_profile belum siap.');
            return Command::FAILURE;
        }

        $licenseKey = $profile->license_key;
        $serverUrl = env('LICENSE_SERVER_URL', 'http://localhost:8000'); // URL default
        
        // 2. Dapatkan domain & IP server saat ini
        $appUrl = config('app.url', 'localhost');
        $domain = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';

        // Cari IP eksternal
        $ipAddress = '127.0.0.1';
        try {
            $responseIp = Http::timeout(3)->get('https://api.ipify.org');
            if ($responseIp->successful()) {
                $ipAddress = trim($responseIp->body());
            }
        } catch (\Exception $e) {
            $ipAddress = gethostbyname(gethostname()) ?: '127.0.0.1';
        }

        $this->info("Domain: {$domain} | IP: {$ipAddress}");

        // 3. Kirim Request Verifikasi ke Server Lisensi
        try {
            $apiUrl = rtrim($serverUrl, '/') . '/api/license/verify';
            $response = Http::timeout(10)->post($apiUrl, [
                'license_key' => $licenseKey,
                'domain' => $domain,
                'ip_address' => $ipAddress,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && $data['status'] === 'active') {
                $expiresAt = $data['expires_at'] !== 'lifetime' ? Carbon::parse($data['expires_at']) : null;

                DB::table('tb_profile')->where('id_profile', 1)->update([
                    'license_status' => 'active',
                    'license_expires_at' => $expiresAt,
                    'license_plan_name' => $data['plan_name'] ?? 'Lite',
                    'license_max_clients' => intval($data['max_clients'] ?? 250),
                    'license_client_name' => $data['client_name'] ?? null,
                    'license_last_checked' => Carbon::now(),
                ]);

                $this->info('Lisensi VALID. Masa berlaku: ' . ($data['expires_at'] ?? 'Lifetime'));
                return Command::SUCCESS;
            } else {
                // Lisensi ditolak/tidak valid/expired
                $status = $data['status'] ?? 'invalid';
                $expiresAt = isset($data['expires_at']) && $data['expires_at'] !== 'lifetime' 
                    ? Carbon::parse($data['expires_at']) 
                    : null;

                DB::table('tb_profile')->where('id_profile', 1)->update([
                    'license_status' => $status,
                    'license_expires_at' => $expiresAt,
                    'license_last_checked' => Carbon::now(),
                ]);

                $this->error('Lisensi TIDAK VALID. Status: ' . $status . '. Message: ' . ($data['message'] ?? ''));
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            // Server offline atau koneksi gagal
            $this->warn('Gagal menghubungi server lisensi: ' . $e->getMessage());
            $this->warn('Menggunakan cache lisensi lokal (Grace Period berjalan).');

            // Grace period: Jika sudah lebih dari 3 hari offline, status akan terpengaruh di Middleware
            return Command::FAILURE;
        }
    }
}

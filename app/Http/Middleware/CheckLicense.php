<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Definisikan rute-rute yang dikecualikan dari pemeriksaan lisensi
        $excludedRoutes = [
            'payment/notification', // Penting agar callback Midtrans tetap masuk
            'administrator/unlicensed', // Halaman input lisensi baru
            'administrator/login', // Halaman login admin
            'login', // Halaman login client
        ];

        $path = $request->path();
        foreach ($excludedRoutes as $route) {
            if ($path === $route || str_starts_with($path, $route . '/')) {
                return $next($request);
            }
        }

        // 2. Baca konfigurasi lisensi dari database lokal
        $profile = DB::table('tb_profile')->where('id_profile', 1)->first();

        // Jika data profil belum di-seed, lewati dahulu (mencegah error saat setup awal)
        if (!$profile) {
            return $next($request);
        }

        // Mencegah error jika migrasi belum dijalankan di server live
        if (!property_exists($profile, 'license_key')) {
            return $next($request);
        }

        $licenseKey = $profile->license_key;
        $status = $profile->license_status;
        $expiresAt = $profile->license_expires_at ? Carbon::parse($profile->license_expires_at) : null;
        $lastChecked = $profile->license_last_checked ? Carbon::parse($profile->license_last_checked) : null;

        // 3. Jika Lisensi belum pernah dimasukkan
        if (empty($licenseKey)) {
            return $this->lockApplication('Lisensi belum diaktifkan. Silakan masukkan License Key Anda.');
        }

        // 4. Periksa status kedaluwarsa secara lokal
        if ($expiresAt && Carbon::now()->greaterThan($expiresAt)) {
            if ($status !== 'expired') {
                DB::table('tb_profile')->where('id_profile', 1)->update([
                    'license_status' => 'expired'
                ]);
            }
            return $this->lockApplication('Lisensi Anda telah kedaluwarsa pada ' . $expiresAt->format('d-m-Y H:i') . '. Silakan lakukan perpanjangan.');
        }

        // 5. Periksa status penangguhan atau ketidakvalidan
        if ($status === 'suspended') {
            return $this->lockApplication('Lisensi Anda ditangguhkan karena melanggar ketentuan. Hubungi pihak pengembang.');
        }

        if ($status === 'invalid' || $status === 'expired') {
            return $this->lockApplication('Lisensi tidak valid atau telah kedaluwarsa. Silakan periksa kembali.');
        }

        // 6. Grace Period (Proteksi Offline 3 Hari)
        // Jika server lisensi offline selama lebih dari 3 hari berturut-turut, kunci aplikasi
        if ($lastChecked && Carbon::now()->diffInDays($lastChecked) > 3) {
            return $this->lockApplication('Aplikasi gagal melakukan verifikasi lisensi selama lebih dari 3 hari. Mohon periksa koneksi internet server Anda.');
        }

        return $next($request);
    }

    /**
     * Redirect to unlicensed page with lock message.
     */
    private function lockApplication($message)
    {
        // Menyimpan pesan error ke session untuk ditampilkan di halaman unlicensed
        session()->flash('license_error', $message);
        return redirect()->route('admin.unlicensed');
    }
}

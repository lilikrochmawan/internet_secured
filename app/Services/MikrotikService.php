<?php

namespace App\Services;

use App\Models\Mikrotik;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    public function getPppoeStatus(string $pppUsername, ?string $pelangganIp = null): array
    {
        $mikrotik = Mikrotik::find(1);

        if (!$mikrotik) {
            return $this->unavailableResult('Konfigurasi Mikrotik tidak ditemukan.');
        }

        if ($pppUsername === '') {
            return $this->unavailableResult('Username PPPOE tidak tersedia pada akun Anda.');
        }

        require_once base_path('/include/routeros_api.php');

        $api = new \RouterosAPI();
        $api->port = (int) ($mikrotik->port_mikrotik ?: 8728);
        $api->timeout = 5;
        $api->attempts = 2;
        $api->delay = 1;

        if (!$api->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            Log::warning('Mikrotik: gagal koneksi.', [
                'error_no' => $api->error_no,
                'error_str' => $api->error_str,
            ]);

            return $this->unavailableResult('Tidak dapat terhubung ke server jaringan.');
        }

        try {
            $secrets = $api->comm('/ppp/secret/print', [
                '?name' => $pppUsername,
            ]);

            if (empty($secrets)) {
                return $this->unavailableResult('Akun PPPOE tidak ditemukan di Mikrotik.');
            }

            $secret = $secrets[0];
            $disabled = ($secret['disabled'] ?? 'false') === 'true';
            $isIsolir = $this->isIpIsolir($api, $pelangganIp);
            $hasIsolirProfile = ($secret['profile'] ?? '') === 'pppoe-isolir';

            if ($disabled || $isIsolir || $hasIsolirProfile) {
                return [
                    'status' => 'terblokir',
                    'label' => 'Terblokir',
                    'online' => false,
                    'ppp_username' => $pppUsername,
                    'message' => $disabled
                        ? 'Layanan internet anda dinonaktifkan'
                        : 'Layanan internet sedang diblokir (isolir).',
                ];
            }

            $activeConnections = $api->comm('/ppp/active/print', [
                '?name' => $pppUsername,
            ]);
            $online = !empty($activeConnections);

            return [
                'status' => 'aktif',
                'label' => 'Aktif',
                'online' => $online,
                'ppp_username' => $pppUsername,
                'message' => $online
                    ? 'Koneksi Anda Online.'
                    : 'Layanan aktif, namun saat ini tidak terhubung.',
            ];
        } catch (\Throwable $exception) {
            Log::error('Mikrotik: ' . $exception->getMessage());

            return $this->unavailableResult('Gagal membaca status PPPOE.');
        } finally {
            if ($api->connected) {
                $api->disconnect();
            }
        }
    }

    private function isIpIsolir(\RouterosAPI $api, ?string $pelangganIp): bool
    {
        if (empty($pelangganIp)) {
            return false;
        }

        $addressLists = $api->comm('/ip/firewall/address-list/print', [
            '?comment' => 'Blokir Bulanan ' . $pelangganIp
        ]) ?: [];

        return !empty($addressLists);
    }

    public function getPppoeStats(string $pppUsername): array
    {
        $mikrotik = Mikrotik::find(1);
        if (!$mikrotik || $pppUsername === '') {
            return [
                'online' => false,
                'profile' => '',
                'uptime' => '0s',
                'bytes_in' => 0,
                'bytes_out' => 0,
                'status' => 'offline',
                'status_label' => 'Koneksi Terputus',
                'status_color' => 'warning'
            ];
        }

        require_once base_path('/include/routeros_api.php');
        $api = new \RouterosAPI();
        $api->port = (int) ($mikrotik->port_mikrotik ?: 8728);
        $api->timeout = 3; // Short timeout for AJAX
        $api->attempts = 1;
        $api->delay = 0;

        if (!$api->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            return [
                'online' => false,
                'profile' => '',
                'uptime' => '0s',
                'bytes_in' => 0,
                'bytes_out' => 0,
                'status' => 'offline',
                'status_label' => 'Koneksi Terputus',
                'status_color' => 'warning'
            ];
        }

        try {
            // Check PPPOE Secret Profile
            $secrets = $api->comm('/ppp/secret/print', [
                '?name' => $pppUsername,
            ]);

            $profile = '';
            if (!empty($secrets)) {
                $profile = $secrets[0]['profile'] ?? '';
            }

            // Check Active Connection
            $activeConnections = $api->comm('/ppp/active/print', [
                '?name' => $pppUsername,
            ]);

            $hasActive = !empty($activeConnections);
            $ac = $hasActive ? $activeConnections[0] : null;

            $uptime = $ac ? ($ac['uptime'] ?? '0s') : '0s';
            $bytesIn = $ac ? (int)($ac['bytes-in'] ?? 0) : 0;
            $bytesOut = $ac ? (int)($ac['bytes-out'] ?? 0) : 0;

            // Retrieve data from interface `<pppoe-username>` as requested
            try {
                $interfaceName = '<pppoe-' . $pppUsername . '>';
                $interfaces = $api->comm('/interface/print', [
                    '?name' => $interfaceName,
                ]);
                if (!empty($interfaces)) {
                    $intf = $interfaces[0];
                    // Rx on Mikrotik PPPoE interface is client Upload (bytes_in)
                    $bytesIn = isset($intf['rx-byte']) ? (int)$intf['rx-byte'] : $bytesIn;
                    // Tx on Mikrotik PPPoE interface is client Download (bytes_out)
                    $bytesOut = isset($intf['tx-byte']) ? (int)$intf['tx-byte'] : $bytesOut;
                }
            } catch (\Exception $ex) {
                Log::warning("Gagal mengambil bytes dari interface $interfaceName: " . $ex->getMessage());
            }

            if ($profile === 'pppoe-isolir') {
                return [
                    'online' => false,
                    'profile' => $profile,
                    'uptime' => $uptime,
                    'bytes_in' => $bytesIn,
                    'bytes_out' => $bytesOut,
                    'status' => 'terisolir',
                    'status_label' => 'Internet Terisolir',
                    'status_color' => 'danger' // Merah
                ];
            }

            if ($hasActive) {
                return [
                    'online' => true,
                    'profile' => $profile,
                    'uptime' => $uptime,
                    'bytes_in' => $bytesIn,
                    'bytes_out' => $bytesOut,
                    'status' => 'aktif',
                    'status_label' => 'Internet Aktif',
                    'status_color' => 'success' // Hijau
                ];
            }

            return [
                'online' => false,
                'profile' => $profile,
                'uptime' => $uptime,
                'bytes_in' => $bytesIn,
                'bytes_out' => $bytesOut,
                'status' => 'aktif_tidak_terhubung',
                'status_label' => 'Internet Aktif',
                'status_color' => 'success' // Hijau
            ];
        } catch (\Exception $e) {
            return [
                'online' => false,
                'profile' => '',
                'uptime' => '0s',
                'bytes_in' => 0,
                'bytes_out' => 0,
                'status' => 'error',
                'status_label' => 'Error Koneksi',
                'status_color' => 'warning'
            ];
        } finally {
            if ($api->connected) {
                $api->disconnect();
            }
        }
    }

    private function unavailableResult(string $message): array
    {
        return [
            'status' => 'unknown',
            'label' => 'Tidak Tersedia',
            'online' => false,
            'ppp_username' => null,
            'message' => $message,
        ];
    }
}

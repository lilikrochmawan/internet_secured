<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class NetworkStatusController extends Controller
{
    public function __construct(
        private MikrotikService $mikrotikService
    ) {
    }

    public function index()
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;

        if (!$pelanggan) {
            abort(404, 'Pelanggan tidak ditemukan.');
        }

        $pelanggan->load('paketDetail');

        $pppStatus = $this->mikrotikService->getPppoeStatus(
            (string) $user->username,
            $pelanggan->ip_address
        );

        $currentSpeed = $this->parsePaketSpeed($pelanggan->paketDetail?->nama_paket);

        $paketUpgrade = Paket::whereIn('nama_paket', ['20 Mb', '30 Mb'])
            ->orderBy('harga')
            ->get();

        $cpe = DB::table('tb_cpe')
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->first();

        return view('network.status', [
            'pelanggan' => $pelanggan,
            'paket' => $pelanggan->paketDetail,
            'pppStatus' => $pppStatus,
            'paketUpgrade' => $paketUpgrade,
            'currentSpeed' => $currentSpeed,
            'cpe' => $cpe,
        ]);
    }

    public function updateWifi(Request $request)
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;
        if (!$pelanggan) {
            return back()->withErrors(['error' => 'Pelanggan tidak ditemukan.']);
        }

        $cpe = DB::table('tb_cpe')
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->first();

        if (!$cpe) {
            return back()->withErrors(['error' => 'Perangkat modem tidak terhubung dengan akun Anda.']);
        }

        // Validate SSID and Password inputs
        $rules = [
            'wifi_ssid_24' => 'required|string|min:1|max:32',
            'wifi_password_24' => 'nullable|string|min:8|max:63',
        ];

        if (!empty($cpe->wifi_ssid_5)) {
            $rules['wifi_ssid_5'] = 'required|string|min:1|max:32';
            $rules['wifi_password_5'] = 'nullable|string|min:8|max:63';
        }

        $request->validate($rules);

        // Detect if it is TR-181 or TR-098 based on CPE model
        $hasTr181InQueue = ($cpe->cwmp_model === 'tr181');

        $mfg = strtolower(trim($cpe->manufacturer));
        $index5g = $mfg === 'cdt' ? 2 : 5;

        $params = [];
        if ($hasTr181InQueue) {
            // TR-181
            $params['Device.WiFi.SSID.1.SSID'] = $request->wifi_ssid_24;
            if (!empty($request->wifi_password_24)) {
                $params['Device.WiFi.AccessPoint.1.Security.KeyPassphrase'] = $request->wifi_password_24;
            }
            if (!empty($cpe->wifi_ssid_5)) {
                $params['Device.WiFi.SSID.' . $index5g . '.SSID'] = $request->wifi_ssid_5;
                if (!empty($request->wifi_password_5)) {
                    $params['Device.WiFi.AccessPoint.' . $index5g . '.Security.KeyPassphrase'] = $request->wifi_password_5;
                }
            }
        } else {
            // TR-098
            $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID'] = $request->wifi_ssid_24;
            if (!empty($request->wifi_password_24)) {
                $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey'] = $request->wifi_password_24;
            }
            if (!empty($cpe->wifi_ssid_5)) {
                $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.' . $index5g . '.SSID'] = $request->wifi_ssid_5;
                if (!empty($request->wifi_password_5)) {
                    $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.' . $index5g . '.PreSharedKey.1.PreSharedKey'] = $request->wifi_password_5;
                }
            }
        }

        if (!empty($params)) {
            // Insert to ACS queue
            DB::table('tb_acs_queue')->insert([
                'serial_number' => $cpe->serial_number,
                'command_type' => 'SetParameterValues',
                'command_data' => json_encode($params),
                'status' => 'pending',
                'created_at' => now(),
            ]);

            // Trigger Connection Request
            $this->sendConnectionRequest($cpe->connection_request_url);
            
            // Also queue a GetParameterValues to update the DB with new SSIDs after connection
            $getPaths = [];
            if ($hasTr181InQueue) {
                $getPaths[] = 'Device.WiFi.SSID.1.SSID';
                if (!empty($cpe->wifi_ssid_5)) {
                    $getPaths[] = 'Device.WiFi.SSID.' . $index5g . '.SSID';
                }
            } else {
                $getPaths[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID';
                if (!empty($cpe->wifi_ssid_5)) {
                    $getPaths[] = 'InternetGatewayDevice.LANDevice.1.WLANConfiguration.' . $index5g . '.SSID';
                }
            }
            
            foreach ($getPaths as $path) {
                DB::table('tb_acs_queue')->insert([
                    'serial_number' => $cpe->serial_number,
                    'command_type' => 'GetParameterValues',
                    'command_data' => json_encode([$path]),
                    'status' => 'pending',
                    'created_at' => now(),
                ]);
            }

            $this->cleanOldQueue($cpe->serial_number);

            return back()->with('success', 'Perintah perubahan nama & password WiFi telah dikirim ke modem. Perubahan biasanya aktif dalam 1-2 menit setelah modem memproses antrean.');
        }

        return back()->withErrors(['error' => 'Gagal memperbarui pengaturan WiFi.']);
    }

    private function cleanOldQueue($serialNumber)
    {
        try {
            DB::delete("DELETE FROM tb_acs_queue 
                WHERE serial_number = ? 
                  AND status IN ('success', 'failed') 
                  AND id_command NOT IN (
                      SELECT id_command FROM (
                          SELECT id_command FROM tb_acs_queue 
                          WHERE serial_number = ? 
                          ORDER BY id_command DESC 
                          LIMIT 100
                      ) tmp
                  )", [$serialNumber, $serialNumber]);
        } catch (\Exception $e) {
            // Suppress database deletion errors to avoid interrupting user flows
        }
    }

    private function sendConnectionRequest($url)
    {
        if (empty($url)) return false;
        try {
            $response = Http::timeout(4)->connectTimeout(4)->get($url);
            $status = $response->status();
            return ($status === 200 || $status === 204 || $status === 401);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function parsePaketSpeed(?string $namaPaket): int
    {
        return preg_match('/(\d+)/', (string) $namaPaket, $match) ? (int) $match[1] : 0;
    }
}

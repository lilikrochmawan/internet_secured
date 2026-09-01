<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Pelanggan;

class AdminAcsController extends Controller
{
    private function sendConnectionRequest($url)
    {
        if (empty($url)) return false;
        try {
            // Standard response for connection request is 200 OK or 204 No Content
            // 401 means it reached the device but requires basic authentication (still successful trigger)
            $response = Http::timeout(4)->connectTimeout(4)->get($url);
            $status = $response->status();
            return ($status === 200 || $status === 204 || $status === 401);
        } catch (\Exception $e) {
            return false;
        }
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

    public function index()
    {
        $cpes = DB::table('tb_cpe')
            ->leftJoin('tb_pelanggan', 'tb_cpe.id_pelanggan', '=', 'tb_pelanggan.id_pelanggan')
            ->select('tb_cpe.*', 'tb_pelanggan.nama_pelanggan')
            ->orderBy('tb_cpe.last_inform', 'desc')
            ->get();

        $pelanggan = Pelanggan::whereNotIn('id_pelanggan', function ($query) {
            $query->select('id_pelanggan')
                  ->from('tb_cpe')
                  ->whereNotNull('id_pelanggan');
        })->orderBy('nama_pelanggan')->get();

        return view('admin.tr069.index', compact('cpes', 'pelanggan'));
    }

    public function linkCustomer(Request $request)
    {
        $request->validate([
            'id_cpe' => 'required|integer',
            'id_pelanggan' => 'required|integer',
        ]);

        DB::table('tb_cpe')
            ->where('id_cpe', $request->id_cpe)
            ->update(['id_pelanggan' => $request->id_pelanggan]);

        if ($request->has('auto_provision') && $request->auto_provision == '1') {
            $this->performAutoProvision($request->id_cpe);
        }

        return redirect()->route('admin.tr069.index')->with('success', 'Berhasil menghubungkan CPE ke pelanggan.');
    }

    public function unlinkCustomer(Request $request)
    {
        $request->validate([
            'id_cpe' => 'required|integer',
        ]);

        DB::table('tb_cpe')
            ->where('id_cpe', $request->id_cpe)
            ->update(['id_pelanggan' => null]);

        return redirect()->route('admin.tr069.index')->with('success', 'Berhasil melepas hubungan pelanggan dari perangkat ini.');
    }

    private function ensureSchemaColumns()
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('tb_cpe', 'wifi_password')) {
                \Illuminate\Support\Facades\Schema::table('tb_cpe', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('wifi_password')->nullable()->after('wifi_ssid_24');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('tb_cpe', 'wifi_password_5')) {
                \Illuminate\Support\Facades\Schema::table('tb_cpe', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('wifi_password_5')->nullable()->after('wifi_ssid_5');
                });
            }
        } catch (\Exception $e) {
            // Silence exception
        }
    }

    public function detail($id)
    {
        $this->ensureSchemaColumns();

        $cpe = DB::table('tb_cpe')
            ->leftJoin('tb_pelanggan', 'tb_cpe.id_pelanggan', '=', 'tb_pelanggan.id_pelanggan')
            ->select('tb_cpe.*', 'tb_pelanggan.nama_pelanggan', 'tb_pelanggan.kode_pelanggan', 'tb_pelanggan.alamat', 'tb_pelanggan.odp')
            ->where('tb_cpe.id_cpe', $id)
            ->first();

        if (!$cpe) {
            abort(404, 'Perangkat tidak ditemukan.');
        }

        $odpName = '-';
        if ($cpe->odp) {
            $odp = DB::table('tbl_odp')->where('id_odp', $cpe->odp)->first();
            if ($odp) {
                $odpName = $odp->nama_odp;
            }
        }

        $queue = DB::table('tb_acs_queue')
            ->where('serial_number', $cpe->serial_number)
            ->orderByRaw("CASE WHEN status IN ('pending', 'sent') THEN 0 ELSE 1 END")
            ->orderBy('id_command', 'desc')
            ->limit(100)
            ->get();

        return view('admin.tr069.detail', compact('cpe', 'queue', 'odpName'));
    }

    public function triggerConnectionRequest(Request $request)
    {
        $request->validate([
            'id_cpe' => 'required|integer',
        ]);

        $cpe = DB::table('tb_cpe')->where('id_cpe', $request->id_cpe)->first();
        if (!$cpe) {
            return back()->withErrors(['error' => 'Perangkat tidak ditemukan.']);
        }

        $success = $this->sendConnectionRequest($cpe->connection_request_url);
        if ($success) {
            return redirect()->route('admin.tr069.detail', $cpe->id_cpe)->with('success', 'Connection Request terkirim. CPE akan melakukan Inform sesi sesaat lagi.');
        }

        return redirect()->route('admin.tr069.detail', $cpe->id_cpe)->withErrors(['error' => 'Gagal mengirim Connection Request. CPE mungkin sedang offline atau tidak dapat dijangkau.']);
    }

    public function reboot(Request $request)
    {
        $request->validate([
            'id_cpe' => 'required|integer',
        ]);

        $cpe = DB::table('tb_cpe')->where('id_cpe', $request->id_cpe)->first();
        if (!$cpe) {
            return back()->withErrors(['error' => 'Perangkat tidak ditemukan.']);
        }

        DB::table('tb_acs_queue')->insert([
            'serial_number' => $cpe->serial_number,
            'command_type' => 'Reboot',
            'command_data' => '{}',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $this->cleanOldQueue($cpe->serial_number);

        // Attempt to notify CPE immediately
        $this->sendConnectionRequest($cpe->connection_request_url);

        return redirect()->route('admin.tr069.detail', $cpe->id_cpe)->with('success', 'Perintah Reboot telah dimasukkan ke dalam antrean.');
    }

    public function setParameters(Request $request)
    {
        $request->validate([
            'id_cpe' => 'required|integer',
            'param_type' => 'required|string',
        ]);

        $cpe = DB::table('tb_cpe')->where('id_cpe', $request->id_cpe)->first();
        if (!$cpe) {
            return back()->withErrors(['error' => 'Perangkat tidak ditemukan.']);
        }

        $paramType = $request->param_type;
        $params = [];

        $mfg = strtolower(trim($cpe->manufacturer));
        if (!empty($cpe->wifi_ssid_5_index)) {
            $index5g = (int) $cpe->wifi_ssid_5_index;
        } else {
            $index5g = 5;
        }

        if ($paramType === 'tr098') {
            $ssid = $request->tr098_ssid;
            $ssid5 = $request->tr098_ssid_5;
            $pass = $request->tr098_password;
            $pass5 = $request->tr098_password_5;
            $pppUser = $request->tr098_pppoe_username;
            $pppPass = $request->tr098_pppoe_password;
            $adminPass = $request->tr098_admin_password;

            if ($ssid !== null && $ssid !== '') {
                $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID'] = $ssid;
            }
            if ($ssid5 !== null && $ssid5 !== '') {
                $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.' . $index5g . '.SSID'] = $ssid5;
            }
            if ($pass !== null && $pass !== '') {
                $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey'] = $pass;
            }
            if ($pass5 !== null && $pass5 !== '') {
                $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.' . $index5g . '.PreSharedKey.1.PreSharedKey'] = $pass5;
            }
            if ($pppUser !== null && $pppUser !== '') {
                $params['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username'] = $pppUser;
            }
            if ($pppPass !== null && $pppPass !== '') {
                $params['InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Password'] = $pppPass;
            }
            if ($adminPass !== null && $adminPass !== '') {
                $params['InternetGatewayDevice.UserInterface.Password'] = $adminPass;
            }
        } elseif ($paramType === 'tr181') {
            $ssid = $request->tr181_ssid;
            $ssid5 = $request->tr181_ssid_5;
            $pass = $request->tr181_password;
            $pass5 = $request->tr181_password_5;
            $pppUser = $request->tr181_pppoe_username;
            $pppPass = $request->tr181_pppoe_password;
            $adminPass = $request->tr181_admin_password;

            if ($ssid !== null && $ssid !== '') {
                $params['Device.WiFi.SSID.1.SSID'] = $ssid;
            }
            if ($ssid5 !== null && $ssid5 !== '') {
                $params['Device.WiFi.SSID.' . $index5g . '.SSID'] = $ssid5;
            }
            if ($pass !== null && $pass !== '') {
                $params['Device.WiFi.AccessPoint.1.Security.KeyPassphrase'] = $pass;
            }
            if ($pass5 !== null && $pass5 !== '') {
                $params['Device.WiFi.AccessPoint.' . $index5g . '.Security.KeyPassphrase'] = $pass5;
            }
            if ($pppUser !== null && $pppUser !== '') {
                $params['Device.PPP.Interface.1.Username'] = $pppUser;
            }
            if ($pppPass !== null && $pppPass !== '') {
                $params['Device.PPP.Interface.1.Password'] = $pppPass;
            }
            if ($adminPass !== null && $adminPass !== '') {
                $params['Device.Users.User.1.Password'] = $adminPass;
            }
        } else {
            $customPath = trim($request->custom_path);
            $customVal = trim($request->custom_value);
            if ($customPath !== '') {
                $params[$customPath] = $customVal;
            }
        }

        if (!empty($params)) {
            DB::table('tb_acs_queue')->insert([
                'serial_number' => $cpe->serial_number,
                'command_type' => 'SetParameterValues',
                'command_data' => json_encode($params),
                'status' => 'pending',
                'created_at' => now(),
            ]);

            $this->cleanOldQueue($cpe->serial_number);

            // Attempt to notify CPE immediately
            $this->sendConnectionRequest($cpe->connection_request_url);

            return redirect()->route('admin.tr069.detail', $cpe->id_cpe)->with('success', 'Perintah ubah parameter telah dimasukkan ke dalam antrean.');
        }

        return redirect()->route('admin.tr069.detail', $cpe->id_cpe)->withErrors(['error' => 'Harap masukkan nilai parameter yang valid.']);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_cpe' => 'required|integer',
        ]);

        $cpe = DB::table('tb_cpe')->where('id_cpe', $request->id_cpe)->first();
        if (!$cpe) {
            return back()->withErrors(['error' => 'Perangkat tidak ditemukan.']);
        }

        // Delete from queue first
        DB::table('tb_acs_queue')->where('serial_number', $cpe->serial_number)->delete();
        
        // Delete from tb_cpe
        DB::table('tb_cpe')->where('id_cpe', $request->id_cpe)->delete();

        return redirect()->route('admin.tr069.index')->with('success', 'Perangkat CPE berhasil dihapus.');
    }

    public function autoProvision(Request $request)
    {
        $request->validate([
            'id_cpe' => 'required|integer',
        ]);

        $success = $this->performAutoProvision($request->id_cpe);

        if ($success) {
            return redirect()->route('admin.tr069.detail', $request->id_cpe)->with('success', 'Setup otomatis WiFi & PPPoE berhasil diantrekan.');
        }

        return redirect()->route('admin.tr069.detail', $request->id_cpe)->withErrors(['error' => 'Gagal melakukan setup otomatis. Pastikan perangkat terhubung ke pelanggan.']);
    }

    public function autoLink(Request $request)
    {
        // Fetch all CPEs that are not linked to a customer
        $unlinkedCpes = DB::table('tb_cpe')
            ->whereNull('id_pelanggan')
            ->whereNotNull('pppoe_username')
            ->where('pppoe_username', '<>', '')
            ->where('pppoe_username', '<>', 'default')
            ->get();

        $linkedCount = 0;

        foreach ($unlinkedCpes as $cpe) {
            // Find a user with matching username in tb_user
            $user = DB::table('tb_user')
                ->where('username', $cpe->pppoe_username)
                ->whereNotNull('id_pelanggan')
                ->first();

            if ($user) {
                // Link the CPE to the customer
                DB::table('tb_cpe')
                    ->where('id_cpe', $cpe->id_cpe)
                    ->update(['id_pelanggan' => $user->id_pelanggan]);

                $linkedCount++;
            }
        }

        if ($linkedCount > 0) {
            return redirect()->route('admin.tr069.index')->with('success', "Berhasil menghubungkan {$linkedCount} perangkat secara otomatis.");
        }

        return redirect()->route('admin.tr069.index')->with('info', 'Tidak ada perangkat baru yang cocok dengan username PPPoE pelanggan.');
    }

    private function performAutoProvision($cpeId)
    {
        $cpe = DB::table('tb_cpe')->where('id_cpe', $cpeId)->first();
        if (!$cpe || empty($cpe->id_pelanggan)) {
            return false;
        }

        $pelanggan = DB::table('tb_pelanggan')->where('id_pelanggan', $cpe->id_pelanggan)->first();
        if (!$pelanggan) {
            return false;
        }

        // Get PPPoE credentials from tb_user
        $user = DB::table('tb_user')->where('id_pelanggan', $cpe->id_pelanggan)->first();
        $pppUser = $user ? $user->username : $pelanggan->kode_pelanggan;
        // Fallback password to a default if not found
        $pppPass = ($user && !empty($user->password) && strpos($user->password, '$2y$') === false) ? $user->password : 'Indotel@123';

        // Generate WiFi SSID & Password
        // WiFi SSID 2.4G = Pelanggan's name (sanitized)
        // WiFi SSID 5G = 5G-Pelanggan's name (sanitized)
        $cleanName = preg_replace('/[^a-zA-Z0-9\s_-]/', '', $pelanggan->nama_pelanggan);
        // Limit SSID length to 32 chars
        $wifiSsid24 = substr(trim($cleanName), 0, 32);
        $wifiSsid5 = substr("5G-" . trim($cleanName), 0, 32);
        
        // WiFi password defaults to 12345678
        $wifiPass = '12345678';

        $params = [];
        $cwmpModel = $cpe->cwmp_model ?? 'tr098';

        if ($cwmpModel === 'tr098') {
            // Determine WAN PPP Connection path
            $pppPath = 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1';
            if (!empty($cpe->pppoe_conn_key)) {
                $pppPath = 'InternetGatewayDevice.WANDevice.1.' . $cpe->pppoe_conn_key;
            } else {
                // Default fallback based on manufacturer
                $mfg = strtolower(trim($cpe->manufacturer));
                if ($mfg === 'pteg' || strpos($mfg, 'zte') !== false) {
                    $pppPath = 'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.3.WANPPPConnection.1';
                }
            }

            // Set PPPoE
            $params[$pppPath . '.Username'] = $pppUser;
            $params[$pppPath . '.Password'] = $pppPass;

            // Set WiFi 2.4G
            $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID'] = $wifiSsid24;
            $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.PreSharedKey.1.PreSharedKey'] = $wifiPass;

            // Set WiFi 5G (if available)
            $index5g = !empty($cpe->wifi_ssid_5_index) ? (int)$cpe->wifi_ssid_5_index : 5;
            $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.' . $index5g . '.SSID'] = $wifiSsid5;
            $params['InternetGatewayDevice.LANDevice.1.WLANConfiguration.' . $index5g . '.PreSharedKey.1.PreSharedKey'] = $wifiPass;
        } else {
            // TR-181
            $params['Device.PPP.Interface.1.Username'] = $pppUser;
            $params['Device.PPP.Interface.1.Password'] = $pppPass;

            $params['Device.WiFi.SSID.1.SSID'] = $wifiSsid24;
            $params['Device.WiFi.AccessPoint.1.Security.KeyPassphrase'] = $wifiPass;

            $index5g = !empty($cpe->wifi_ssid_5_index) ? (int)$cpe->wifi_ssid_5_index : 5;
            $params['Device.WiFi.SSID.' . $index5g . '.SSID'] = $wifiSsid5;
            $params['Device.WiFi.AccessPoint.' . $index5g . '.Security.KeyPassphrase'] = $wifiPass;
        }

        // Insert into queue
        DB::table('tb_acs_queue')->insert([
            'serial_number' => $cpe->serial_number,
            'command_type' => 'SetParameterValues',
            'command_data' => json_encode($params),
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $this->cleanOldQueue($cpe->serial_number);

        // Notify CPE
        $this->sendConnectionRequest($cpe->connection_request_url);

        return true;
    }
}

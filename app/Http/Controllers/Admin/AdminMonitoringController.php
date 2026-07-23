<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $mikrotik_devices = DB::table('tbl_mikrotik')->get();
        if ($mikrotik_devices->isEmpty()) {
            return view('admin.monitoring.index', [
                'mikrotik_devices' => $mikrotik_devices,
                'selected_device_id' => 1,
                'connected' => false,
                'error' => 'Koneksi router Mikrotik tidak ditemukan. Silakan tambahkan di pengaturan.'
            ]);
        }

        $selected_device_id = $request->get('device_id');
        if (!$selected_device_id) {
            $selected_device_id = $mikrotik_devices->first()->id_mikrotik;
        }

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $selected_device_id)->first();
        if (!$mikrotik) {
            $mikrotik = $mikrotik_devices->first();
            $selected_device_id = $mikrotik->id_mikrotik;
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 2;
        $API->attempts = 1;
        $API->delay = 0;

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            // Get Clock Info
            $API->write('/system/clock/print');
            $clockInfo = $API->read();
            $date = date('Y-m-d');
            $time = date('H:i:s');
            if (!empty($clockInfo) && isset($clockInfo[0]['time'])) {
                $dateTime = $clockInfo[0]['time'];
                $date = date('Y-m-d', strtotime($dateTime));
                $time = date('H:i:s', strtotime($dateTime));
            }

            // Get Resource Info
            $API->write('/system/resource/print');
            $resourceInfo = $API->read();
            
            $uptime = 'N/A';
            $board_name = 'N/A';
            $version = 'N/A';
            $cpu_load = 0;
            $free_memory = 0;
            $total_hdd = 'N/A';

            if (!empty($resourceInfo)) {
                $uptime = $resourceInfo[0]['uptime'] ?? 'N/A';
                $board_name = $resourceInfo[0]['board-name'] ?? 'N/A';
                $version = $resourceInfo[0]['version'] ?? 'N/A';
                $cpu_load = $resourceInfo[0]['cpu-load'] ?? 0;
                
                $freeMemoryInBytes = (int)($resourceInfo[0]['free-memory'] ?? 0);
                $free_memory = number_format($freeMemoryInBytes / 1048576, 1);

                $totalHddSpaceInBytes = (int)($resourceInfo[0]['total-hdd-space'] ?? 0);
                $total_hdd = number_format(($totalHddSpaceInBytes / 1024) / 1.024, 0) . ' KB';
            }

            // Get Routerboard Info
            $API->write('/system/routerboard/print');
            $routerboard = $API->read();
            $model = $routerboard[0]['model'] ?? 'N/A';

            $API->disconnect();

            $connected = true;

            return view('admin.monitoring.index', compact(
                'mikrotik_devices',
                'selected_device_id',
                'connected',
                'date',
                'time',
                'uptime',
                'board_name',
                'version',
                'cpu_load',
                'free_memory',
                'total_hdd',
                'model'
            ));
        }

        return view('admin.monitoring.index', [
            'mikrotik_devices' => $mikrotik_devices,
            'selected_device_id' => $selected_device_id,
            'connected' => false,
            'error' => 'Gagal terhubung ke Router Mikrotik API. Periksa IP, Port, Username, Password, atau pastikan service API Winbox aktif.'
        ]);
    }

    public function getResources(Request $request)
    {
        $selected_device_id = $request->get('device_id');
        if (!$selected_device_id) {
            $first_device = DB::table('tbl_mikrotik')->first();
            $selected_device_id = $first_device ? $first_device->id_mikrotik : 1;
        }

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $selected_device_id)->first();
        if (!$mikrotik) {
            return response()->json(['success' => false, 'error' => 'Device not found']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 2;
        $API->attempts = 1;
        $API->delay = 0;

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            // Get Clock Info
            $API->write('/system/clock/print');
            $clockInfo = $API->read();
            $date = date('Y-m-d');
            $time = date('H:i:s');
            if (!empty($clockInfo) && isset($clockInfo[0]['time'])) {
                $dateTime = $clockInfo[0]['time'];
                $date = date('Y-m-d', strtotime($dateTime));
                $time = date('H:i:s', strtotime($dateTime));
            }

            // Get Resource Info
            $API->write('/system/resource/print');
            $resourceInfo = $API->read();
            
            $uptime = 'N/A';
            $cpu_load = 0;
            $free_memory = 0;
            $total_hdd = 'N/A';

            if (!empty($resourceInfo)) {
                $uptime = $resourceInfo[0]['uptime'] ?? 'N/A';
                $cpu_load = $resourceInfo[0]['cpu-load'] ?? 0;
                
                $freeMemoryInBytes = (int)($resourceInfo[0]['free-memory'] ?? 0);
                $free_memory = number_format($freeMemoryInBytes / 1048576, 1);

                $totalHddSpaceInBytes = (int)($resourceInfo[0]['total-hdd-space'] ?? 0);
                $total_hdd = number_format(($totalHddSpaceInBytes / 1024) / 1.024, 0) . ' KB';
            }

            $API->disconnect();

            return response()->json([
                'success' => true,
                'date' => $date,
                'time' => $time,
                'uptime' => $uptime,
                'cpu_load' => $cpu_load,
                'free_memory' => $free_memory,
                'total_hdd' => $total_hdd
            ]);
        }

        return response()->json(['success' => false, 'error' => 'Could not connect to router']);
    }

    public function getInterfaces(Request $request)
    {
        $device_id = $request->get('device_id', 1);
        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $device_id)->first();
        if (!$mikrotik) {
            return response()->json(['success' => false, 'message' => 'Mikrotik device not found']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 2;
        $API->attempts = 1;
        $API->delay = 0;

        if (!$API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            return response()->json(['success' => false, 'message' => 'Gagal konek ke Mikrotik API']);
        }

        try {
            // Ethernet
            $API->write('/interface/ethernet/print', false);
            $API->write('=.proplist=name,running,disabled');
            $eth_res = $API->read();
            
            // VLAN
            $API->write('/interface/vlan/print', false);
            $API->write('=.proplist=name,running,disabled');
            $vlan_res = $API->read();

            $interfaces = [];
            
            if (is_array($eth_res)) {
                foreach ($eth_res as $it) {
                    $name = $it['name'] ?? '';
                    if ($name === '') continue;

                    $interfaces[] = [
                        'name' => $name,
                        'type' => 'ether',
                        'running' => ($it['running'] ?? 'false') === 'true',
                        'disabled' => ($it['disabled'] ?? 'false') === 'true',
                    ];
                }
            }

            if (is_array($vlan_res)) {
                foreach ($vlan_res as $it) {
                    $name = $it['name'] ?? '';
                    if ($name === '') continue;

                    $interfaces[] = [
                        'name' => $name,
                        'type' => 'vlan',
                        'running' => ($it['running'] ?? 'false') === 'true',
                        'disabled' => ($it['disabled'] ?? 'false') === 'true',
                    ];
                }
            }

            usort($interfaces, function ($a, $b) {
                if ($a['running'] !== $b['running']) return $a['running'] ? -1 : 1;
                return strcmp($a['name'], $b['name']);
            });

            return response()->json(['success' => true, 'data' => $interfaces]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        } finally {
            $API->disconnect();
        }
    }

    public function getTraffic(Request $request)
    {
        $device_id = $request->post('device_id', 1);
        $iface = $request->post('iface', '');
        
        if (empty($iface)) {
            return response()->json(['success' => false, 'message' => 'Interface is empty']);
        }

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $device_id)->first();
        if (!$mikrotik) {
            return response()->json(['success' => false, 'message' => 'Mikrotik device not found']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 2;
        $API->attempts = 1;
        $API->delay = 0;

        if (!$API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            return response()->json(['success' => false, 'message' => 'Gagal konek ke Mikrotik API']);
        }

        try {
            $API->write('/interface/monitor-traffic', false);
            $API->write('=interface=' . $iface, false);
            $API->write('=once=');
            $res = $API->read();

            $row0 = (is_array($res) && isset($res[0])) ? $res[0] : [];
            $rx = (int)($row0['rx-bits-per-second'] ?? 0);
            $tx = (int)($row0['tx-bits-per-second'] ?? 0);

            return response()->json([
                'success' => true,
                'data' => [
                    'iface' => $iface,
                    'rx_bps' => $rx,
                    'tx_bps' => $tx,
                    'timestamp_ms' => (int) floor(microtime(true) * 1000),
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        } finally {
            $API->disconnect();
        }
    }

    public function getLogs(Request $request)
    {
        $device_id = $request->get('device_id', 1);
        $limit = (int)$request->get('limit', 100);
        if ($limit <= 0) $limit = 100;
        if ($limit > 1000) $limit = 1000;

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $device_id)->first();
        if (!$mikrotik) {
            return response()->json(['success' => false, 'message' => 'Mikrotik device not found']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 2;
        $API->attempts = 1;
        $API->delay = 0;

        if (!$API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            return response()->json(['success' => false, 'message' => 'Gagal konek ke Mikrotik API']);
        }

        try {
            $API->write('/log/print', false);
            $API->write('=.proplist=time,topics,message');
            $logs = $API->read();

            if (!is_array($logs)) $logs = [];
            $last = array_slice($logs, -$limit);
            $last = array_reverse($last);

            $out = [];
            foreach ($last as $it) {
                $out[] = [
                    'time' => $it['time'] ?? '',
                    'topics' => $it['topics'] ?? '',
                    'message' => $it['message'] ?? '',
                ];
            }

            return response()->json(['success' => true, 'data' => $out]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        } finally {
            $API->disconnect();
        }
    }

    public function teknisiClients(Request $request)
    {
        $mikrotik_devices = DB::table('tbl_mikrotik')->get();
        if ($mikrotik_devices->isEmpty()) {
            return view('admin.monitoring.teknisi_clients', [
                'mikrotik_devices' => $mikrotik_devices,
                'selected_device_id' => 1,
                'connected' => false,
                'error' => 'Koneksi router Mikrotik tidak ditemukan. Silakan hubungi admin.'
            ]);
        }

        $selected_device_id = $request->get('device_id');
        if (!$selected_device_id) {
            $selected_device_id = $mikrotik_devices->first()->id_mikrotik;
        }

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $selected_device_id)->first();
        if (!$mikrotik) {
            $mikrotik = $mikrotik_devices->first();
            $selected_device_id = $mikrotik->id_mikrotik;
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 3;
        $API->attempts = 1;
        $API->delay = 0;

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $pppSecrets = $API->comm("/ppp/secret/print", [
                ".proplist" => "name,last-logged-out,remote-address,disabled,profile"
            ]) ?: [];
            $activeClients = $API->comm("/ppp/active/print", [
                ".proplist" => ".id,name,address"
            ]) ?: [];

            // Pengecekan status isolir
            $db_ips = DB::table('tb_pelanggan')->whereNotNull('ip_address')->where('ip_address', '<>', '')->pluck('ip_address')->toArray();
            $db_ips_map = array_flip($db_ips);
            $isolirList = [];
            
            $dataAddressList = $API->comm("/ip/firewall/address-list/print", [
                "?list" => "blocked_clients"
            ]) ?: [];
            
            foreach ($dataAddressList as $list) {
                if (isset($list['address']) && isset($db_ips_map[$list['address']])) {
                    if (isset($list['comment']) && strpos($list['comment'], 'Blokir Bulanan ') === 0) {
                        $isolirList[] = $list['address'];
                    }
                }
            }

            // Fetch pelanggan details mapped by lowercased username (scoped to user branches)
            $pelanggans = \App\Models\Pelanggan::with(['users', 'odpDetail'])->allowedForUser()->get();
            $pelangganMap = [];
            foreach ($pelanggans as $p) {
                foreach ($p->users as $u) {
                    $pelangganMap[strtolower($u->username)] = $p;
                }
            }

            $activeClientsMap = [];
            foreach ($activeClients as $ac) {
                if (isset($ac['name'])) {
                    $activeClientsMap[$ac['name']] = $ac;
                }
            }

            $clientsList = [];
            foreach ($pppSecrets as $secret) {
                $username = $secret['name'] ?? '';
                $usernameLower = strtolower($username);
                
                // If technician level, apply scope mapping/filtering (if client not in scope, skip)
                if (auth()->user()->level !== 'admin' && !isset($pelangganMap[$usernameLower])) {
                    continue;
                }

                $lastLogout = $secret['last-logged-out'] ?? '-';
                $ipAddress = $secret['remote-address'] ?? '-';
                $disabled = $secret['disabled'] ?? 'false';
                $profile = $secret['profile'] ?? '';

                $ac = $activeClientsMap[$username] ?? null;
                $isActive = ($ac !== null);
                $activeId = $ac ? ($ac['.id'] ?? null) : null;
                $ipActive = $ac ? ($ac['address'] ?? "") : "";
                if ($isActive && $ipActive !== "") {
                    $ipAddress = $ipActive;
                }

                if ($isActive && !in_array($ipActive, $isolirList) && $profile !== 'pppoe-isolir') {
                    $status = 'aktif';
                } elseif ((in_array($ipActive, $isolirList) && $ipActive != "") || $disabled == 'true' || $profile === 'pppoe-isolir') {
                    $status = 'terisolir';
                } else {
                    $status = 'tidak_aktif';
                }

                // Filter: we only want 'terisolir' and 'tidak_aktif'
                if ($status === 'aktif') {
                    continue;
                }

                $p = $pelangganMap[$usernameLower] ?? null;

                $clientsList[] = [
                    'username' => $username,
                    'ip_address' => $ipAddress,
                    'last_logout' => $lastLogout,
                    'status' => $status,
                    'active_id' => $activeId,
                    'nama_pelanggan' => $p ? $p->nama_pelanggan : 'Tidak Diketahui',
                    'alamat' => $p ? $p->alamat : '-',
                    'no_telp' => $p ? $p->no_telp : '-',
                    'odp' => ($p && $p->odpDetail) ? $p->odpDetail->nama_odp : '-',
                    'id_pelanggan' => $p ? $p->id_pelanggan : null,
                    'location' => $p ? $p->location : null,
                    'odp_location' => ($p && $p->odpDetail) ? $p->odpDetail->location : null,
                ];
            }

            // Sort by status priority (terisolir first, then tidak_aktif) then username
            usort($clientsList, function ($a, $b) {
                $pA = ($a['status'] === 'terisolir') ? 1 : 2;
                $pB = ($b['status'] === 'terisolir') ? 1 : 2;
                if ($pA !== $pB) {
                    return $pA - $pB;
                }
                return strcmp($a['username'], $b['username']);
            });

            $API->disconnect();

            return view('admin.monitoring.teknisi_clients', compact(
                'mikrotik_devices',
                'selected_device_id',
                'clientsList'
            ))->with('connected', true);
        }

        return view('admin.monitoring.teknisi_clients', [
            'mikrotik_devices' => $mikrotik_devices,
            'selected_device_id' => $selected_device_id,
            'connected' => false,
            'error' => 'Gagal terhubung ke Router Mikrotik API. Periksa IP, Port, Username, Password, atau pastikan service API Winbox aktif.'
        ]);
    }

    public function activeClients(Request $request)
    {
        $mikrotik_devices = DB::table('tbl_mikrotik')->get();
        if ($mikrotik_devices->isEmpty()) {
            return view('admin.monitoring.active', [
                'mikrotik_devices' => $mikrotik_devices,
                'selected_device_id' => 1,
                'connected' => false,
                'error' => 'Koneksi router Mikrotik tidak ditemukan. Silakan tambahkan di pengaturan.'
            ]);
        }

        $selected_device_id = $request->get('device_id');
        if (!$selected_device_id) {
            $selected_device_id = $mikrotik_devices->first()->id_mikrotik;
        }

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $selected_device_id)->first();
        if (!$mikrotik) {
            $mikrotik = $mikrotik_devices->first();
            $selected_device_id = $mikrotik->id_mikrotik;
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 2;
        $API->attempts = 1;
        $API->delay = 0;

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $pppSecrets = $API->comm("/ppp/secret/print", [
                ".proplist" => "name,last-logged-out,remote-address,disabled,profile"
            ]) ?: [];
            $activeClients = $API->comm("/ppp/active/print", [
                ".proplist" => ".id,name,address"
            ]) ?: [];

            // Pengecekan status isolir
            $db_ips = DB::table('tb_pelanggan')->whereNotNull('ip_address')->where('ip_address', '<>', '')->pluck('ip_address')->toArray();
            $db_ips_map = array_flip($db_ips);
            $isolirList = [];
            
            $dataAddressList = $API->comm("/ip/firewall/address-list/print", [
                "?list" => "blocked_clients"
            ]) ?: [];
            
            foreach ($dataAddressList as $list) {
                if (isset($list['address']) && isset($db_ips_map[$list['address']])) {
                    if (isset($list['comment']) && strpos($list['comment'], 'Blokir Bulanan ') === 0) {
                        $isolirList[] = $list['address'];
                    }
                }
            }

            $totalActive = count($activeClients);
            $totalAll = count($pppSecrets);

            $activeClientsMap = [];
            foreach ($activeClients as $ac) {
                if (isset($ac['name'])) {
                    $activeClientsMap[$ac['name']] = $ac;
                }
            }

            $usersMap = DB::table('tb_user')
                ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tb_user.id_pelanggan')
                ->pluck('tb_pelanggan.nama_pelanggan', 'tb_user.username')
                ->toArray();
            $usersMap = array_change_key_case($usersMap, CASE_LOWER);

            $clientsList = [];
            foreach ($pppSecrets as $secret) {
                $username = $secret['name'] ?? '';
                $usernameLower = strtolower($username);
                $namaPelanggan = $usersMap[$usernameLower] ?? null;

                $lastLogout = $secret['last-logged-out'] ?? '-';
                $ipAddress = $secret['remote-address'] ?? '-';
                $disabled = $secret['disabled'] ?? 'false';
                $profile = $secret['profile'] ?? '';

                $ac = $activeClientsMap[$username] ?? null;
                $isActive = ($ac !== null);
                $activeId = $ac ? ($ac['.id'] ?? null) : null;
                $ipActive = $ac ? ($ac['address'] ?? "") : "";
                if ($isActive && $ipActive !== "") {
                    $ipAddress = $ipActive;
                }

                if ($isActive && !in_array($ipActive, $isolirList) && $profile !== 'pppoe-isolir') {
                    $status = 'aktif';
                    $sortPriority = 1;
                } elseif ((in_array($ipActive, $isolirList) && $ipActive != "") || $disabled == 'true' || $profile === 'pppoe-isolir') {
                    $status = 'terisolir';
                    $sortPriority = 2;
                } else {
                    $status = 'tidak_aktif';
                    $sortPriority = 3;
                }

                $clientsList[] = [
                    'username' => $username,
                    'nama_pelanggan' => $namaPelanggan,
                    'ip_address' => $ipAddress,
                    'last_logout' => $lastLogout,
                    'status' => $status,
                    'sort_priority' => $sortPriority,
                    'active_id' => $activeId,
                ];
            }

            usort($clientsList, function ($a, $b) {
                if ($a['sort_priority'] !== $b['sort_priority']) {
                    return $a['sort_priority'] - $b['sort_priority'];
                }
                return strcmp($a['username'], $b['username']);
            });

            $API->disconnect();

            return view('admin.monitoring.active', compact(
                'mikrotik_devices',
                'selected_device_id',
                'clientsList',
                'totalActive',
                'totalAll'
            ))->with('connected', true);
        }

        return view('admin.monitoring.active', [
            'mikrotik_devices' => $mikrotik_devices,
            'selected_device_id' => $selected_device_id,
            'connected' => false,
            'error' => 'Gagal terhubung ke Router Mikrotik API. Periksa IP, Port, Username, Password, atau pastikan service API Winbox aktif.'
        ]);
    }

    public function disconnectActive(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'id' => 'required|string',
        ]);

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $request->device_id)->first();
        if (!$mikrotik) {
            return back()->withErrors(['error' => 'Device Mikrotik tidak ditemukan.']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $API->comm("/ppp/active/remove", [
                ".id" => $request->id,
            ]);
            $API->disconnect();
            return back()->with('success', 'Koneksi aktif PPPoE berhasil diputuskan.');
        }

        return back()->withErrors(['error' => 'Gagal terhubung ke Router Mikrotik API.']);
    }

    public function remoteOnt(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'ip' => 'required|string',
        ]);

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $request->device_id)->first();
        if (!$mikrotik) {
            return response()->json(['success' => false, 'message' => 'Device Mikrotik tidak ditemukan.']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $getNAT = $API->comm("/ip/firewall/nat/print", [
                "?comment" => "FORWARD MODEM"
            ]);

            if (!empty($getNAT)) {
                $rule = $getNAT[0];
                $idNAT = $rule['.id'];
                
                // Get configured dst-port, default to 8063
                $dstPort = $rule['dst-port'] ?? '8063';
                
                // Get remote host (configured in database, default to rmtsg3.perwiramedia.com)
                $remoteHost = !empty($mikrotik->remote_host) ? $mikrotik->remote_host : 'rmtsg3.perwiramedia.com';

                // Extract only host/domain/IP, stripping protocol/path if present
                $hostOnly = $remoteHost;
                if (preg_match('/^https?:\/\//i', $hostOnly)) {
                    $hostOnly = preg_replace('/^https?:\/\//i', '', $hostOnly);
                }
                $hostOnly = explode('/', $hostOnly)[0];
                $cleanHost = trim($hostOnly);

                $API->comm("/ip/firewall/nat/set", [
                    ".id" => $idNAT,
                    "to-addresses" => $request->ip
                ]);
                $API->disconnect();
                
                // If the configured remote host already has its own port, use it. Otherwise, append dst-port.
                if (strpos($cleanHost, ':') !== false) {
                    $url = "http://{$cleanHost}/";
                } else {
                    $url = "http://{$cleanHost}:{$dstPort}/";
                }
                return response()->json(['success' => true, 'url' => $url]);
            }

            $API->disconnect();
            return response()->json(['success' => false, 'message' => 'Rule NAT dengan comment "FORWARD MODEM" tidak ditemukan.']);
        }

        return response()->json(['success' => false, 'message' => 'Gagal login ke MikroTik.']);
    }

    public function getNatSettings(Request $request)
    {
        $device_id = $request->get('device_id');
        if (!$device_id) {
            return response()->json(['success' => false, 'message' => 'ID Perangkat Mikrotik diperlukan.']);
        }

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $device_id)->first();
        if (!$mikrotik) {
            return response()->json(['success' => false, 'message' => 'Perangkat Mikrotik tidak ditemukan.']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $getNAT = $API->comm("/ip/firewall/nat/print", [
                "?comment" => "FORWARD MODEM"
            ]);

            $interfaces = $API->comm("/interface/print") ?: [];
            $ifaceList = [];
            foreach ($interfaces as $iface) {
                if (isset($iface['name'])) {
                    $ifaceList[] = $iface['name'];
                }
            }

            $API->disconnect();

            $remoteHost = $mikrotik->remote_host ?? 'rmtsg3.perwiramedia.com';

            if (!empty($getNAT)) {
                $rule = $getNAT[0];
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'rule' => [
                        'id' => $rule['.id'],
                        'dst_port' => $rule['dst-port'] ?? '',
                        'to_ports' => $rule['to-ports'] ?? '80',
                        'in_interface' => $rule['in-interface'] ?? '',
                        'protocol' => $rule['protocol'] ?? 'tcp',
                        'comment' => $rule['comment'] ?? 'FORWARD MODEM',
                    ],
                    'remote_host' => $remoteHost,
                    'interfaces' => $ifaceList
                ]);
            }

            return response()->json([
                'success' => true,
                'exists' => false,
                'rule' => [
                    'dst_port' => '8063',
                    'to_ports' => '80',
                    'in_interface' => '',
                    'protocol' => 'tcp',
                    'comment' => 'FORWARD MODEM'
                ],
                'remote_host' => $remoteHost,
                'interfaces' => $ifaceList
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal terhubung ke Router Mikrotik API.']);
    }

    public function updateNatSettings(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'dst_port' => 'required|string',
            'to_ports' => 'required|string',
            'in_interface' => 'nullable|string',
            'protocol' => 'required|string',
            'remote_host' => 'required|string',
        ]);

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $request->device_id)->first();
        if (!$mikrotik) {
            return response()->json(['success' => false, 'message' => 'Perangkat Mikrotik tidak ditemukan.']);
        }

        // Save remote host in database
        DB::table('tbl_mikrotik')->where('id_mikrotik', $request->device_id)->update([
            'remote_host' => htmlspecialchars(strip_tags($request->remote_host))
        ]);

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $getNAT = $API->comm("/ip/firewall/nat/print", [
                "?comment" => "FORWARD MODEM"
            ]);

            $params = [
                'chain' => 'dstnat',
                'action' => 'dst-nat',
                'protocol' => $request->protocol,
                'dst-port' => $request->dst_port,
                'to-ports' => $request->to_ports,
                'comment' => 'FORWARD MODEM',
            ];

            if ($request->in_interface) {
                $params['in-interface'] = $request->in_interface;
            } else {
                $params['in-interface'] = 'all'; // Default empty/all
            }

            if (!empty($getNAT)) {
                // Update
                $params['.id'] = $getNAT[0]['.id'];
                // Winbox API doesn't support setting in-interface to empty directly, so unset if empty
                if (!$request->in_interface) {
                    $API->comm("/ip/firewall/nat/unset", [
                        '.id' => $getNAT[0]['.id'],
                        'value-name' => 'in-interface'
                    ]);
                    unset($params['in-interface']);
                }
                $API->comm("/ip/firewall/nat/set", $params);
                $msg = 'Rule NAT FORWARD MODEM berhasil diperbarui!';
            } else {
                // Add new rule
                $params['to-addresses'] = '0.0.0.0'; // placeholder
                if (!$request->in_interface) {
                    unset($params['in-interface']);
                }
                $API->comm("/ip/firewall/nat/add", $params);
                $msg = 'Rule NAT FORWARD MODEM baru berhasil dibuat di MikroTik!';
            }

            $API->disconnect();
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal terhubung ke Router Mikrotik API.']);
    }

    public function pppoeSecrets(Request $request)
    {
        $mikrotik_devices = DB::table('tbl_mikrotik')->get();
        if ($mikrotik_devices->isEmpty()) {
            return view('admin.monitoring.pppoe', [
                'mikrotik_devices' => $mikrotik_devices,
                'selected_device_id' => 1,
                'connected' => false,
                'secrets' => [],
                'profiles' => [],
                'error' => 'Koneksi router Mikrotik tidak ditemukan. Silakan tambahkan di pengaturan.'
            ]);
        }

        $selected_device_id = $request->get('device_id');
        if (!$selected_device_id) {
            $selected_device_id = $mikrotik_devices->first()->id_mikrotik;
        }

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $selected_device_id)->first();
        if (!$mikrotik) {
            $mikrotik = $mikrotik_devices->first();
            $selected_device_id = $mikrotik->id_mikrotik;
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 2;
        $API->attempts = 1;
        $API->delay = 0;

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $secrets = $API->comm("/ppp/secret/print") ?: [];
            $profiles = $API->comm("/ppp/profile/print") ?: [];

            $API->disconnect();

            return view('admin.monitoring.pppoe', compact(
                'mikrotik_devices',
                'selected_device_id',
                'secrets',
                'profiles'
            ))->with('connected', true);
        }

        return view('admin.monitoring.pppoe', [
            'mikrotik_devices' => $mikrotik_devices,
            'selected_device_id' => $selected_device_id,
            'connected' => false,
            'secrets' => [],
            'profiles' => [],
            'error' => 'Gagal terhubung ke Router Mikrotik API.'
        ]);
    }

    public function storeSecret(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'name' => 'required|string',
            'password' => 'required|string',
            'profile' => 'required|string',
            'local_address' => 'nullable|string',
            'remote_address' => 'nullable|string',
            'old_name' => 'nullable|string',
        ]);

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $request->device_id)->first();
        if (!$mikrotik) {
            return back()->withErrors(['error' => 'Device Mikrotik tidak ditemukan.']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $params = [
                'name' => $request->name,
                'password' => $request->password,
                'service' => 'pppoe',
                'profile' => $request->profile,
            ];

            if ($request->local_address) {
                $params['local-address'] = $request->local_address;
            }
            if ($request->remote_address) {
                $params['remote-address'] = $request->remote_address;
            }

            if ($request->old_name) {
                // Edit
                $secrets = $API->comm("/ppp/secret/print", [
                    "?name" => $request->old_name
                ]);
                if (!empty($secrets)) {
                    $params['.id'] = $secrets[0]['.id'];
                    $API->comm("/ppp/secret/set", $params);
                    $msg = 'PPPoE Secret berhasil diperbarui!';
                } else {
                    $API->disconnect();
                    return back()->withErrors(['error' => 'PPPoE Secret lama tidak ditemukan di Mikrotik.']);
                }
            } else {
                // Add
                $API->comm("/ppp/secret/add", $params);
                $msg = 'PPPoE Secret baru berhasil ditambahkan!';
            }

            $API->disconnect();
            return back()->with('success', $msg);
        }

        return back()->withErrors(['error' => 'Gagal terhubung ke Router Mikrotik API.']);
    }

    public function deleteSecret(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'id' => 'required|string',
        ]);

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $request->device_id)->first();
        if (!$mikrotik) {
            return back()->withErrors(['error' => 'Device Mikrotik tidak ditemukan.']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $API->comm("/ppp/secret/remove", [
                ".id" => $request->id
            ]);
            $API->disconnect();
            return back()->with('success', 'PPPoE Secret berhasil dihapus dari Mikrotik.');
        }

        return back()->withErrors(['error' => 'Gagal terhubung ke Router Mikrotik API.']);
    }

    public function pppoeProfiles(Request $request)
    {
        $mikrotik_devices = DB::table('tbl_mikrotik')->get();
        if ($mikrotik_devices->isEmpty()) {
            return view('admin.monitoring.profiles', [
                'mikrotik_devices' => $mikrotik_devices,
                'selected_device_id' => 1,
                'connected' => false,
                'profiles' => [],
                'pools' => [],
                'queues' => [],
                'error' => 'Koneksi router Mikrotik tidak ditemukan. Silakan tambahkan di pengaturan.'
            ]);
        }

        $selected_device_id = $request->get('device_id');
        if (!$selected_device_id) {
            $selected_device_id = $mikrotik_devices->first()->id_mikrotik;
        }

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $selected_device_id)->first();
        if (!$mikrotik) {
            $mikrotik = $mikrotik_devices->first();
            $selected_device_id = $mikrotik->id_mikrotik;
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 2;
        $API->attempts = 1;
        $API->delay = 0;

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $profiles = $API->comm("/ppp/profile/print") ?: [];
            $pools = $API->comm("/ip/pool/print") ?: [];
            $queues = $API->comm("/queue/simple/print") ?: [];

            $API->disconnect();

            return view('admin.monitoring.profiles', compact(
                'mikrotik_devices',
                'selected_device_id',
                'profiles',
                'pools',
                'queues'
            ))->with('connected', true);
        }

        return view('admin.monitoring.profiles', [
            'mikrotik_devices' => $mikrotik_devices,
            'selected_device_id' => $selected_device_id,
            'connected' => false,
            'profiles' => [],
            'pools' => [],
            'queues' => [],
            'error' => 'Gagal terhubung ke Router Mikrotik API.'
        ]);
    }

    public function storeProfile(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'name' => 'required|string',
            'local_address' => 'nullable|string',
            'remote_address' => 'nullable|string',
            'rate_limit' => 'nullable|string',
            'parent_queue' => 'nullable|string',
            'on_up' => 'nullable|string',
            'on_down' => 'nullable|string',
            'old_id' => 'nullable|string',
        ]);

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $request->device_id)->first();
        if (!$mikrotik) {
            return back()->withErrors(['error' => 'Device Mikrotik tidak ditemukan.']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $params = [
                'name' => $request->name,
                'on-up' => $request->on_up ?? '',
                'on-down' => $request->on_down ?? '',
            ];

            if ($request->local_address) {
                $params['local-address'] = $request->local_address;
            }
            if ($request->remote_address) {
                $params['remote-address'] = $request->remote_address;
            }
            if ($request->rate_limit) {
                $params['rate-limit'] = $request->rate_limit;
            }
            if ($request->parent_queue) {
                $params['parent-queue'] = $request->parent_queue;
            }

            if ($request->old_id) {
                $params['.id'] = $request->old_id;
                $API->comm("/ppp/profile/set", $params);
                $msg = 'PPPoE Profile berhasil diperbarui!';
            } else {
                $API->comm("/ppp/profile/add", $params);
                $msg = 'PPPoE Profile baru berhasil ditambahkan!';
            }

            $API->disconnect();
            return back()->with('success', $msg);
        }

        return back()->withErrors(['error' => 'Gagal terhubung ke Router Mikrotik API.']);
    }

    public function deleteProfile(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'id' => 'required|string',
        ]);

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $request->device_id)->first();
        if (!$mikrotik) {
            return back()->withErrors(['error' => 'Device Mikrotik tidak ditemukan.']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $API->comm("/ppp/profile/remove", [
                ".id" => $request->id
            ]);
            $API->disconnect();
            return back()->with('success', 'PPPoE Profile berhasil dihapus dari Mikrotik.');
        }

        return back()->withErrors(['error' => 'Gagal terhubung ke Router Mikrotik API.']);
    }
}

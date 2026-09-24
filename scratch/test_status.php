<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$startTime = microtime(true);

$terisolirCount = 0;
$nonaktifCount = 0;
$aktifCount = 0;

$mikrotiks = DB::table('tbl_mikrotik')->get();
require_once base_path('include/routeros_api.php');

$pelanggans = \App\Models\Pelanggan::with(['users'])->get();
$pelangganMap = [];
foreach ($pelanggans as $p) {
    foreach ($p->users as $u) {
        $pelangganMap[strtolower($u->username)] = $p;
    }
}

$db_ips = DB::table('tb_pelanggan')->whereNotNull('ip_address')->where('ip_address', '<>', '')->pluck('ip_address')->toArray();
$db_ips_map = array_flip($db_ips);

foreach ($mikrotiks as $mikrotik) {
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

        $activeClientsMap = [];
        foreach ($activeClients as $ac) {
            if (isset($ac['name'])) {
                $activeClientsMap[$ac['name']] = $ac;
            }
        }

        foreach ($pppSecrets as $secret) {
            $username = $secret['name'] ?? '';
            $usernameLower = strtolower($username);
            if (!isset($pelangganMap[$usernameLower])) {
                continue;
            }
            
            $ipAddress = $secret['remote-address'] ?? '-';
            $disabled = $secret['disabled'] ?? 'false';
            $profile = $secret['profile'] ?? '';

            $ac = $activeClientsMap[$username] ?? null;
            $isActive = ($ac !== null);
            $ipActive = $ac ? ($ac['address'] ?? "") : "";
            if ($isActive && $ipActive !== "") {
                $ipAddress = $ipActive;
            }

            if ($isActive && !in_array($ipActive, $isolirList) && $profile !== 'pppoe-isolir') {
                $aktifCount++;
            } elseif ((in_array($ipActive, $isolirList) && $ipActive != "") || $disabled == 'true' || $profile === 'pppoe-isolir') {
                $terisolirCount++;
            } else {
                $nonaktifCount++;
            }
        }
        $API->disconnect();
    }
}

$time = microtime(true) - $startTime;
echo "Time: {$time}s\n";
echo "Aktif: $aktifCount, Isolir: $terisolirCount, Nonaktif: $nonaktifCount\n";

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

$mikrotik = \Illuminate\Support\Facades\DB::table('tbl_mikrotik')->first();
if (!$mikrotik) {
    die("No Mikrotik device in DB.\n");
}

echo "Connecting to Mikrotik at {$mikrotik->ip}...\n";

require_once base_path('include/routeros_api.php');
$API = new \RouterosAPI();
$API->timeout = 5;
$API->attempts = 1;

if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
    echo "Connected successfully!\n";
    
    echo "Fetching PPP Secrets...\n";
    $secrets = $API->comm("/ppp/secret/print") ?: [];
    echo "Total PPP Secrets: " . count($secrets) . "\n";
    
    echo "Fetching PPP Active...\n";
    $active = $API->comm("/ppp/active/print") ?: [];
    echo "Total PPP Active: " . count($active) . "\n";
    
    echo "Fetching Firewall Address-List...\n";
    $addressList = $API->comm("/ip/firewall/address-list/print") ?: [];
    echo "Total Firewall Address-List: " . count($addressList) . "\n";
    
    $API->disconnect();
} else {
    echo "Failed to connect to Mikrotik API.\n";
}

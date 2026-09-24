<?php

require 'c:/xampp/htdocs/internet/vendor/autoload.php';
$app = require_once 'c:/xampp/htdocs/internet/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pgate = DB::table('tbl_pgate')->first();
$serverKey = trim($pgate->tserverkey);

echo "Server Key: $serverKey\n";
echo "Testing Sandbox endpoint with this key...\n";

$apiUrl = 'https://app.sandbox.midtrans.com/snap/v1/transactions';
$payload = [
    'transaction_details' => [
        'order_id' => 'test-auth-' . time(),
        'gross_amount' => 10000,
    ]
];

$response = Http::withBasicAuth($serverKey, '')
    ->withHeaders(['Accept' => 'application/json'])
    ->post($apiUrl, $payload);

echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n";

echo "\nTesting Live endpoint with this key...\n";
$apiUrlLive = 'https://app.midtrans.com/snap/v1/transactions';
$responseLive = Http::withBasicAuth($serverKey, '')
    ->withHeaders(['Accept' => 'application/json'])
    ->post($apiUrlLive, $payload);

echo "Status: " . $responseLive->status() . "\n";
echo "Body: " . $responseLive->body() . "\n";

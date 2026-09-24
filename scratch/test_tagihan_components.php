<?php
require 'c:/xampp/htdocs/internet/vendor/autoload.php';
$app = require_once 'c:/xampp/htdocs/internet/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$token = \Illuminate\Support\Facades\DB::table('tbl_token')->where('id_token', 1)->first()->bablast_token;
$target = '6285868955896'; // From user's screenshot

$payload = [
    'phone' => $target,
    'template_name' => 'tagihan',
    'language' => 'en',
    'parameters' => [
        [
            'type' => 'image',
            'image' => [
                'link' => 'https://www.google.com/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png'
            ]
        ],
        'Bapak Budi',
        'Bapak Budi',
        'Rp 100.000',
        '10 Sep 2026'
    ]
];

echo "Testing tagihan with explicit components\n";
try {
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json'
    ])->post('https://api.bablast.id/waba/send-template', $payload);
    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

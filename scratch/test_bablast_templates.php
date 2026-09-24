<?php
require 'c:/xampp/htdocs/internet/vendor/autoload.php';
$app = require_once 'c:/xampp/htdocs/internet/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$token = \Illuminate\Support\Facades\DB::table('tbl_token')->where('id_token', 1)->first()->bablast_token;

try {
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json'
    ])->get('https://api.bablast.id/waba/templates');
    $data = $response->json();
    if(isset($data['data'])) {
        foreach($data['data'] as $tpl) {
            echo "Template: " . $tpl['name'] . "\n";
            echo "Language: " . $tpl['language'] . "\n";
            echo "Components: " . json_encode($tpl['components'], JSON_PRETTY_PRINT) . "\n\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

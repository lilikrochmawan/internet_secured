<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- ENV VALUES ---\n";
echo "env('DB_DATABASE'): " . env('DB_DATABASE') . "\n";
echo "getenv('DB_DATABASE'): " . getenv('DB_DATABASE') . "\n";
echo "--- CONFIG VALUES ---\n";
echo "config('database.default'): " . config('database.default') . "\n";
echo "config('database.connections.mysql.database'): " . config('database.connections.mysql.database') . "\n";
echo "--- DATABASE CONNECTION ---\n";
echo "Resolved DB Name: " . DB::connection()->getDatabaseName() . "\n";
echo "--- ENV FILE CONTENT ---\n";
echo file_get_contents(__DIR__ . '/../.env');

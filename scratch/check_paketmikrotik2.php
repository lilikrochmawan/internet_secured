<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$data = \Illuminate\Support\Facades\DB::select('SELECT * FROM tbl_paketmikrotik LIMIT 5');
print_r($data);

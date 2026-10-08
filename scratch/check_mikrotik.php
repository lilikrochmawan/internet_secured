<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$cols = \Illuminate\Support\Facades\DB::select('SHOW FULL COLUMNS FROM tb_mikrotik');
foreach($cols as $col) echo $col->Field . "\n";

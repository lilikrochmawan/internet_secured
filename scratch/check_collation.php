<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$cols = DB::select('SHOW FULL COLUMNS FROM tb_pelanggan');
foreach($cols as $col) {
    if ($col->Field == 'no_telp') {
        echo $col->Collation . "\n";
    }
}

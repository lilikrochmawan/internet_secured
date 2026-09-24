<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tables = ['tbl_notifbayar', 'tbl_notifikasi', 'tbl_notifikasiblokir', 'tbl_notifikasiregister', 'tbl_notifpromo', 'tbl_notifreminder'];
foreach ($tables as $t) {
    echo "--- $t ---\n";
    $row = DB::table($t)->first();
    print_r($row);
}

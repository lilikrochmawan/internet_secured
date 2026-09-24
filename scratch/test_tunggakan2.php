<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$unpaidTagihan = DB::table('tb_tagihan')->where('status_bayar', '!=', 1)->get();
foreach ($unpaidTagihan as $t) {
    echo "ID Tagihan: {$t->id_tagihan}, ID Pelanggan: {$t->id_pelanggan}, Bulan: {$t->bulan_tahun}, Status: {$t->status_bayar}\n";
}

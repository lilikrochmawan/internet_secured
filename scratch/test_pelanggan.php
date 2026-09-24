<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = DB::table('tb_pelanggan')->whereIn('id_pelanggan', [11, 38, 48, 91])->get();
foreach ($p as $c) {
    echo $c->id_pelanggan . ' - ' . $c->nama_pelanggan . ' - Status: ' . $c->status_berlangganan . ' - Tagihan: ' . ($c->tgl_tagih ?? '') . "\n";
}

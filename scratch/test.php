<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$null = DB::table('tb_tagihan')->whereNull('status_bayar')->count();
$zero = DB::table('tb_tagihan')->where('status_bayar', 0)->count();
$one = DB::table('tb_tagihan')->where('status_bayar', 1)->count();
echo "NULL: $null\nZERO: $zero\nONE: $one\n";

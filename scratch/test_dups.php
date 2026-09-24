<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$dups = DB::table('tb_pelanggan')->select('no_telp', DB::raw('count(*) as total'))->groupBy('no_telp')->having('total', '>', 1)->get();
echo json_encode($dups);

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pels = DB::table('tb_tagihan')
    ->where(function($q){ $q->whereNull('status_bayar')->orWhereIn('status_bayar', [0, '0', 'belum', '']); })
    ->pluck('id_pelanggan')->toArray();
$counts = array_count_values($pels);
arsort($counts);
print_r(array_slice($counts, 0, 10));

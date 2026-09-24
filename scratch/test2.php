<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$allowedPelangganIds = DB::table('tb_pelanggan')->pluck('id_pelanggan')->toArray();

$total = count($allowedPelangganIds);

$nonaktifCount = DB::table('tb_tagihan')
    ->where(function($q){ $q->whereNull('status_bayar')->orWhereIn('status_bayar', [0, '0', 'belum', '']); })
    ->where('jatuh_tempo', '<', \Carbon\Carbon::now()->subDays(60))
    ->whereIn('id_pelanggan', $allowedPelangganIds)
    ->distinct('id_pelanggan')
    ->count('id_pelanggan');

$terisolirCount = DB::table('tb_tagihan')
    ->where(function($q){ $q->whereNull('status_bayar')->orWhereIn('status_bayar', [0, '0', 'belum', '']); })
    ->where('blokir_status', 1)
    ->whereIn('id_pelanggan', $allowedPelangganIds)
    ->distinct('id_pelanggan')
    ->count('id_pelanggan');

echo "Total: $total\nNonaktif: $nonaktifCount\nTerisolir: $terisolirCount\n";

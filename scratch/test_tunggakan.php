<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$unpaidTagihan = DB::table('tb_tagihan')->where('status_bayar', '!=', 1)->get();
echo "Total unpaid tagihan rows: " . count($unpaidTagihan) . "\n";

$topMenunggak = DB::table('tb_tagihan')
    ->join('tb_pelanggan', 'tb_tagihan.id_pelanggan', '=', 'tb_pelanggan.id_pelanggan')
    ->select('tb_tagihan.id_pelanggan', 'tb_pelanggan.nama_pelanggan', 'tb_pelanggan.no_telp', DB::raw('COUNT(tb_tagihan.id_tagihan) as jumlah_tunggakan'), DB::raw('SUM(tb_tagihan.jml_bayar) as total_tunggakan'))
    ->where('tb_tagihan.status_bayar', '!=', 1)
    ->groupBy('tb_tagihan.id_pelanggan', 'tb_pelanggan.nama_pelanggan', 'tb_pelanggan.no_telp')
    ->orderBy('jumlah_tunggakan', 'desc')
    ->limit(10)
    ->get();

echo "Top Menunggak rows: " . count($topMenunggak) . "\n";
foreach ($topMenunggak as $t) {
    echo $t->nama_pelanggan . " - " . $t->jumlah_tunggakan . " bulan - Rp " . $t->total_tunggakan . "\n";
}

$allPelanggan = DB::table('tb_pelanggan')->count();
echo "Total pelanggan: " . $allPelanggan . "\n";

$pelangganWithTunggakan = DB::table('tb_tagihan')->where('status_bayar', '!=', 1)->distinct('id_pelanggan')->count('id_pelanggan');
echo "Total pelanggan with unpaid bills: " . $pelangganWithTunggakan . "\n";

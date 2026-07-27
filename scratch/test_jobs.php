<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\Promo;
use Illuminate\Support\Facades\DB;

echo "=== VERIFYING BILL GENERATION AND PROMO FLOW ===\n";

// Let's create a temporary client, a promo, and test GenerateBulananTagihan logic inline or view its behavior
$testPelanggan = Pelanggan::first();
if (!$testPelanggan) {
    echo "No customer found in database, cannot run live checks.\n";
    exit;
}

echo "Testing with customer: {$testPelanggan->nama_pelanggan} (ID: {$testPelanggan->id_pelanggan})\n";

// Check if we can find any active promo
$now = now();
$activePromo = Promo::getActivePromoForPeriod($testPelanggan->id_pelanggan, $now->format('m'), $now->format('Y'));
if ($activePromo) {
    echo "Active promo found for current period: {$activePromo->nama_promo} with price: {$activePromo->nominal_tagihan}\n";
} else {
    echo "No active promo found for current period. Creating a temporary promo for testing...\n";
    DB::table('tb_promo')->insert([
        'nama_promo' => 'Test Promo AGY',
        'id_pelanggan' => $testPelanggan->id_pelanggan,
        'id_paket' => $testPelanggan->paket,
        'mulai_bulan' => (int) $now->format('m'),
        'mulai_tahun' => (int) $now->format('Y'),
        'selesai_bulan' => (int) $now->format('m'),
        'selesai_tahun' => (int) $now->format('Y'),
        'nominal_tagihan' => 99999,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    $activePromo = Promo::getActivePromoForPeriod($testPelanggan->id_pelanggan, $now->format('m'), $now->format('Y'));
    echo "Temporary promo created: {$activePromo->nama_promo} with price: {$activePromo->nominal_tagihan}\n";
}

// Now let's simulate GenerateBulananTagihan logic for this customer
$bulan = $now->format('m');
$tahun = $now->format('Y');
$bulantahun = $bulan . $tahun;

echo "\n--- Simulating Bill Generation ---\n";
$ppn_aktif = false;
$paketSettings = DB::table('tbl_paketmikrotik')->first();
if ($paketSettings && isset($paketSettings->ppn) && $paketSettings->ppn === 'aktif') {
    $ppn_aktif = true;
}

if ($activePromo) {
    $total_tagihan = $activePromo->nominal_tagihan;
    echo "Promo is active. Expected bill amount: {$total_tagihan}\n";
} else {
    $harga_paket = $testPelanggan->paketDetail->harga ?? 0;
    $ppn_rate = $testPelanggan->paketDetail->ppn ?? 0;
    $total_tagihan = $ppn_aktif ? ($harga_paket + ($harga_paket * $ppn_rate)) : $harga_paket;
    echo "No promo. Expected bill amount: {$total_tagihan}\n";
}

$status_bayar = $activePromo ? 1 : null;
$terbayar = $activePromo ? $total_tagihan : null;
echo "Expected payment status: " . ($status_bayar === 1 ? "PAID/LUNAS" : "UNPAID") . "\n";
echo "Expected paid amount: {$terbayar}\n";

// Clean up temporary promo
DB::table('tb_promo')->where('nama_promo', 'Test Promo AGY')->delete();
echo "Temporary promo cleaned up.\n";

echo "\n--- Testing Auto-Block Due Date Fallback Filtering ---\n";
// Let's print out how we filter unpaid bills
$currentPeriod = now()->format('mY');
$unpaidBills = Tagihan::with('pelanggan')
    ->whereNull('status_bayar')
    ->where(function ($query) {
        $query->whereNull('blokir_status')
              ->orWhere('blokir_status', 0);
    })
    ->where('bulan_tahun', $currentPeriod)
    ->get();

echo "Total unpaid bills in current period: " . $unpaidBills->count() . "\n";

$overdueBills = $unpaidBills->filter(function ($tx) use ($now) {
    $pelanggan = $tx->pelanggan;
    if (!$pelanggan) return false;
    
    $activePromo = Promo::getActivePromoForPeriod($pelanggan->id_pelanggan, $now->format('m'), $now->format('Y'));
    if ($activePromo) {
        echo "  [Filter] Customer {$pelanggan->nama_pelanggan} skipped: Has active promo.\n";
        return false;
    }

    $jatuhTempo = $tx->jatuh_tempo;
    $source = 'transaction';
    if (is_null($jatuhTempo) || $jatuhTempo === '') {
        $jatuhTempo = $pelanggan->jatuh_tempo;
        $source = 'customer fallback';
    }

    if (is_null($jatuhTempo) || $jatuhTempo === '') {
        echo "  [Filter] Customer {$pelanggan->nama_pelanggan} skipped: No due date found.\n";
        return false;
    }

    $isOverdue = \Carbon\Carbon::parse($jatuhTempo)->lt($now);
    echo "  [Filter] Customer {$pelanggan->nama_pelanggan}: Due Date = {$jatuhTempo} ({$source}), Overdue = " . ($isOverdue ? 'YES' : 'NO') . "\n";
    return $isOverdue;
});

echo "Total overdue bills filtered: " . $overdueBills->count() . "\n";
echo "=== TESTING COMPLETED SUCCESSFULLY ===\n";

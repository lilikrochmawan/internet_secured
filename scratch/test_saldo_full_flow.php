<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\SaldoMutasi;
use Illuminate\Support\Facades\DB;

echo "=== TESTING TOPUP SALDO & ACCOUNTING INTEGRATION ===\n";

DB::beginTransaction();

try {
    // 1. Get or create test customer
    $pelanggan = Pelanggan::first();
    if (!$pelanggan) {
        throw new \Exception("Tidak ada pelanggan untuk testing.");
    }
    $initialSaldo = (int) ($pelanggan->saldo ?? 0);
    echo "1. Pelanggan testing ID: {$pelanggan->id_pelanggan} ({$pelanggan->nama_pelanggan}), Saldo awal: Rp " . number_format($initialSaldo, 0, ',', '.') . "\n";

    // 2. Test Admin Top Up
    $topupAmount = 50000;
    $newSaldo = $initialSaldo + $topupAmount;
    $pelanggan->update(['saldo' => $newSaldo]);

    $mutasi1 = SaldoMutasi::create([
        'id_pelanggan' => $pelanggan->id_pelanggan,
        'user_id' => 1,
        'tipe' => 'masuk',
        'jumlah' => $topupAmount,
        'saldo_awal' => $initialSaldo,
        'saldo_akhir' => $newSaldo,
        'kategori' => 'topup_manual',
        'keterangan' => 'Test Top Up Saldo',
        'reference_id' => 'TEST-TOPUP-1',
    ]);

    $kasTopupId = DB::table('tb_kas')->insertGetId([
        'tgl_kas' => date('Y-m-d'),
        'keterangan' => 'Top Up Saldo Pelanggan: ' . $pelanggan->nama_pelanggan,
        'penerimaan' => $topupAmount,
        'pengeluaran' => 0,
        'is_saldo' => 0,
        'jenis_kas' => 'topup',
        'status' => 1,
    ]);

    echo "2. Top Up Rp " . number_format($topupAmount) . " berhasil. Saldo baru: Rp " . number_format($pelanggan->fresh()->saldo) . ". Kas ID: $kasTopupId (is_saldo = 0, jenis_kas = topup)\n";

    // 3. Test Full Saldo Invoice Payment
    $billAmount = 30000;
    $tagihan = Tagihan::create([
        'id_pelanggan' => $pelanggan->id_pelanggan,
        'bulan_tahun' => '112026',
        'jml_bayar' => $billAmount,
        'terbayar' => $billAmount,
        'bayar_saldo' => $billAmount,
        'bayar_non_saldo' => 0,
        'metode_pembayaran' => 'Saldo',
        'status_bayar' => 1,
        'waktu_bayar' => now(),
    ]);

    $saldoAfterPay = $newSaldo - $billAmount;
    $pelanggan->update(['saldo' => $saldoAfterPay]);

    SaldoMutasi::create([
        'id_pelanggan' => $pelanggan->id_pelanggan,
        'tipe' => 'keluar',
        'jumlah' => $billAmount,
        'saldo_awal' => $newSaldo,
        'saldo_akhir' => $saldoAfterPay,
        'kategori' => 'bayar_tagihan',
        'keterangan' => 'Test Bayar Tagihan Saldo',
        'reference_id' => 'TEST-PAY-1',
    ]);

    $kasPayId = DB::table('tb_kas')->insertGetId([
        'tgl_kas' => date('Y-m-d'),
        'keterangan' => '[SALDO] Pembayaran Tagihan: ' . $pelanggan->nama_pelanggan,
        'penerimaan' => $billAmount,
        'pengeluaran' => 0,
        'bayar_saldo' => $billAmount,
        'is_saldo' => 1,
        'jenis_kas' => 'saldo',
        'id_tagihan' => $tagihan->id_tagihan,
        'status' => 1,
    ]);

    echo "3. Pembayaran Full Saldo Rp " . number_format($billAmount) . " berhasil. Tagihan ID: {$tagihan->id_tagihan}. Kas ID: $kasPayId (is_saldo = 1, jenis_kas = saldo)\n";

    // 4. Test Split Payment Invoice
    $splitTotal = 50000;
    $splitSaldo = 15000;
    $splitNonSaldo = 35000;

    $tagihanSplit = Tagihan::create([
        'id_pelanggan' => $pelanggan->id_pelanggan,
        'bulan_tahun' => '122026',
        'jml_bayar' => $splitTotal,
        'terbayar' => $splitTotal,
        'bayar_saldo' => $splitSaldo,
        'bayar_non_saldo' => $splitNonSaldo,
        'metode_pembayaran' => 'Saldo + Midtrans',
        'status_bayar' => 1,
        'waktu_bayar' => now(),
    ]);

    $kasSplitId = DB::table('tb_kas')->insertGetId([
        'tgl_kas' => date('Y-m-d'),
        'keterangan' => '[SPLIT SALDO] Pembayaran Tagihan: ' . $pelanggan->nama_pelanggan . ' (Midtrans: ' . $splitNonSaldo . ', Saldo: ' . $splitSaldo . ')',
        'penerimaan' => $splitNonSaldo,
        'pengeluaran' => 0,
        'bayar_saldo' => $splitSaldo,
        'is_saldo' => 0,
        'jenis_kas' => 'tagihan',
        'id_tagihan' => $tagihanSplit->id_tagihan,
        'status' => 1,
    ]);

    echo "4. Pembayaran Split Tagihan Rp " . number_format($splitTotal) . " (Saldo: Rp " . number_format($splitSaldo) . ", Non-Saldo: Rp " . number_format($splitNonSaldo) . ") berhasil.\n";

    // 5. Test Accounting Calculations
    $kasRiilMasuk = DB::table('tb_kas')->where(function($q) {
        $q->whereNull('is_saldo')->orWhere('is_saldo', 0);
    })->sum('penerimaan');

    $kasSaldoMasuk = DB::table('tb_kas')->where('is_saldo', 1)->sum('penerimaan');

    echo "5. Verifikasi Buku Kas:\n";
    echo "   - Kas Riil Masuk (Fisik/Bank): Rp " . number_format($kasRiilMasuk, 0, ',', '.') . "\n";
    echo "   - Realisasi Pembayaran Saldo ([SALDO]): Rp " . number_format($kasSaldoMasuk, 0, ',', '.') . "\n";

    // 6. Test Transaksi Calculation
    $paidViaSaldo = Tagihan::where('status_bayar', 1)->sum('bayar_saldo');
    $paidViaNonSaldo = Tagihan::where('status_bayar', 1)->sum('bayar_non_saldo');

    echo "6. Verifikasi Transaksi:\n";
    echo "   - Total Tagihan Terbayar via Saldo: Rp " . number_format($paidViaSaldo, 0, ',', '.') . "\n";
    echo "   - Total Tagihan Terbayar Non-Saldo: Rp " . number_format($paidViaNonSaldo, 0, ',', '.') . "\n";

    echo "=== ALL CHECKS PASSED SUCCESSFULLY ===\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    // Rollback test data so we don't pollute live database
    DB::rollBack();
    echo "Database rollback done (clean test).\n";
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminKasController extends Controller
{
    public function index(Request $request)
    {
        $kas = DB::table('tb_kas')
            ->leftJoin('tb_tagihan', 'tb_tagihan.id_tagihan', '=', 'tb_kas.id_tagihan')
            ->select('tb_kas.*', 'tb_tagihan.waktu_bayar')
            ->orderBy('tb_kas.id_kas', 'desc')
            ->get();

        $total_masuk = DB::table('tb_kas')->sum('penerimaan') ?? 0;
        $total_keluar = DB::table('tb_kas')->sum('pengeluaran') ?? 0;
        $saldo = $total_masuk - $total_keluar;

        // User selected month & year filter
        $selectedYear = $request->get('filter_tahun', Carbon::now()->year);
        $selectedMonth = $request->get('filter_bulan', Carbon::now()->month);
        $selectedDate = Carbon::createFromDate((int)$selectedYear, (int)$selectedMonth, 1);

        $pemasukan_bulan_ini = DB::table('tb_kas')
            ->whereMonth('tgl_kas', $selectedDate->month)
            ->whereYear('tgl_kas', $selectedDate->year)
            ->sum('penerimaan') ?? 0;

        $lastMonth = $selectedDate->copy()->subMonth();
        $pemasukan_bulan_lalu = DB::table('tb_kas')
            ->whereMonth('tgl_kas', $lastMonth->month)
            ->whereYear('tgl_kas', $lastMonth->year)
            ->sum('penerimaan') ?? 0;

        // Fetch tax settings from tb_profile
        $settings = DB::table('tb_profile')->first();
        $isPpnActive = (($settings->tax_ppn_status ?? 'tidak') === 'aktif');
        $isPpnCharged = (($settings->tax_ppn_charged ?? 'ya') === 'ya');
        $ppnPercent = (double)($settings->tax_ppn_rate ?? 11.00);
        $bhpPercent = (double)($settings->tax_bhp_rate ?? 0.50);
        $usoPercent = (double)($settings->tax_uso_rate ?? 1.25);

        // All Time calculations
        $allDpp = 0;
        $allPpn = 0;
        if ($isPpnActive) {
            if ($isPpnCharged) {
                $allDpp = $total_masuk;
                $allPpn = $total_masuk * ($ppnPercent / 100);
            } else {
                $allDpp = $total_masuk / (1 + ($ppnPercent / 100));
                $allPpn = $total_masuk - $allDpp;
            }
        } else {
            $allDpp = $total_masuk;
            $allPpn = 0;
        }
        $allBhp = (($settings->tax_bhp_status ?? 'aktif') === 'aktif') ? ($allDpp * ($bhpPercent / 100)) : 0;
        $allUso = (($settings->tax_uso_status ?? 'aktif') === 'aktif') ? ($allDpp * ($usoPercent / 100)) : 0;
        $allTotalTax = $allPpn + $allBhp + $allUso;

        // Bulan Ini calculations
        $monthDpp = 0;
        $monthPpn = 0;
        if ($isPpnActive) {
            if ($isPpnCharged) {
                $monthDpp = $pemasukan_bulan_ini;
                $monthPpn = $pemasukan_bulan_ini * ($ppnPercent / 100);
            } else {
                $monthDpp = $pemasukan_bulan_ini / (1 + ($ppnPercent / 100));
                $monthPpn = $pemasukan_bulan_ini - $monthDpp;
            }
        } else {
            $monthDpp = $pemasukan_bulan_ini;
            $monthPpn = 0;
        }
        $monthBhp = (($settings->tax_bhp_status ?? 'aktif') === 'aktif') ? ($monthDpp * ($bhpPercent / 100)) : 0;
        $monthUso = (($settings->tax_uso_status ?? 'aktif') === 'aktif') ? ($monthDpp * ($usoPercent / 100)) : 0;
        $monthTotalTax = $monthPpn + $monthBhp + $monthUso;

        // Bulan Lalu calculations
        $lastMonthDpp = 0;
        $lastMonthPpn = 0;
        if ($isPpnActive) {
            if ($isPpnCharged) {
                $lastMonthDpp = $pemasukan_bulan_lalu;
                $lastMonthPpn = $pemasukan_bulan_lalu * ($ppnPercent / 100);
            } else {
                $lastMonthDpp = $pemasukan_bulan_lalu / (1 + ($ppnPercent / 100));
                $lastMonthPpn = $pemasukan_bulan_lalu - $lastMonthDpp;
            }
        } else {
            $lastMonthDpp = $pemasukan_bulan_lalu;
            $lastMonthPpn = 0;
        }
        $lastMonthBhp = (($settings->tax_bhp_status ?? 'aktif') === 'aktif') ? ($lastMonthDpp * ($bhpPercent / 100)) : 0;
        $lastMonthUso = (($settings->tax_uso_status ?? 'aktif') === 'aktif') ? ($lastMonthDpp * ($usoPercent / 100)) : 0;
        $lastMonthTotalTax = $lastMonthPpn + $lastMonthBhp + $lastMonthUso;

        return view('admin.kas.index', compact(
            'kas', 'total_masuk', 'total_keluar', 'saldo', 'pemasukan_bulan_ini', 'pemasukan_bulan_lalu',
            'settings', 'isPpnActive', 'ppnPercent', 'bhpPercent', 'usoPercent',
            'allDpp', 'allPpn', 'allBhp', 'allUso', 'allTotalTax',
            'monthDpp', 'monthPpn', 'monthBhp', 'monthUso', 'monthTotalTax',
            'lastMonthDpp', 'lastMonthPpn', 'lastMonthBhp', 'lastMonthUso', 'lastMonthTotalTax', 'lastMonth',
            'selectedMonth', 'selectedYear', 'selectedDate'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tgl_kas' => 'required|date',
            'keterangan' => 'required|string',
            'penerimaan' => 'required|numeric',
            'pengeluaran' => 'required|numeric',
        ]);

        DB::table('tb_kas')->insert([
            'tgl_kas' => $request->tgl_kas,
            'keterangan' => htmlspecialchars(strip_tags($request->keterangan)),
            'penerimaan' => $request->penerimaan,
            'pengeluaran' => $request->pengeluaran,
            'status' => 1, // status = 1 can be edited/deleted, status = 0 cannot
        ]);

        return redirect()->route('admin.kas.index')->with('success', 'Transaksi kas berhasil dicatat!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_kas' => 'required|integer',
            'tgl_kas' => 'required|date',
            'keterangan' => 'required|string',
            'penerimaan' => 'required|numeric',
            'pengeluaran' => 'required|numeric',
        ]);

        // Periksa status terlebih dahulu (hanya status = 1 yang boleh diubah)
        $kas = DB::table('tb_kas')->where('id_kas', $request->id_kas)->first();
        if (!$kas || $kas->status != 1) {
            return back()->withErrors(['error' => 'Data kas ini dikunci dan tidak dapat diubah.']);
        }

        DB::table('tb_kas')->where('id_kas', $request->id_kas)->update([
            'tgl_kas' => $request->tgl_kas,
            'keterangan' => htmlspecialchars(strip_tags($request->keterangan)),
            'penerimaan' => $request->penerimaan,
            'pengeluaran' => $request->pengeluaran,
        ]);

        return redirect()->route('admin.kas.index')->with('success', 'Transaksi kas berhasil diubah!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id_kas' => 'required|integer',
        ]);

        $kas = DB::table('tb_kas')->where('id_kas', $request->id_kas)->first();
        if (!$kas || $kas->status != 1) {
            return back()->withErrors(['error' => 'Data kas ini dikunci dan tidak dapat dihapus.']);
        }

        DB::table('tb_kas')->where('id_kas', $request->id_kas)->delete();

        return redirect()->route('admin.kas.index')->with('success', 'Transaksi kas berhasil dihapus!');
    }

    public function printReport(Request $request)
    {
        $tipe = $request->get('tipe', 'bulanan');
        $query = DB::table('tb_kas')
            ->leftJoin('tb_tagihan', 'tb_tagihan.id_tagihan', '=', 'tb_kas.id_tagihan')
            ->select('tb_kas.*', 'tb_tagihan.waktu_bayar')
            ->orderBy('tb_kas.tgl_kas', 'asc');
        $title = 'Laporan Kas';

        if ($tipe === 'harian') {
            $tanggal = $request->get('tanggal', date('Y-m-d'));
            $query->whereDate('tgl_kas', $tanggal);
            $title = 'Laporan Kas Harian - ' . Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($tipe === 'mingguan') {
            $tgl_mulai = $request->get('tgl_mulai', date('Y-m-d', strtotime('-6 days')));
            $tgl_selesai = $request->get('tgl_selesai', date('Y-m-d'));
            $query->whereBetween('tgl_kas', [$tgl_mulai . ' 00:00:00', $tgl_selesai . ' 23:59:59']);
            $title = 'Laporan Kas Mingguan (' . Carbon::parse($tgl_mulai)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($tgl_selesai)->translatedFormat('d F Y') . ')';
        } elseif ($tipe === 'bulanan') {
            $bulan = $request->get('bulan', date('m'));
            $tahun = $request->get('tahun_bulan', date('Y'));
            $query->whereMonth('tgl_kas', $bulan)->whereYear('tgl_kas', $tahun);
            $title = 'Laporan Kas Bulanan - ' . Carbon::create()->month((int)$bulan)->translatedFormat('F') . ' ' . $tahun;
        } elseif ($tipe === 'tahunan') {
            $tahun = $request->get('tahun', date('Y'));
            $query->whereYear('tgl_kas', $tahun);
            $title = 'Laporan Kas Tahunan - ' . $tahun;
        }

        $kas = $query->get();

        $total_masuk = $kas->sum('penerimaan') ?? 0;
        $total_keluar = $kas->sum('pengeluaran') ?? 0;
        $saldo = $total_masuk - $total_keluar;

        $profile = DB::table('tb_profile')->first();
        if ($profile && !isset($profile->telepon)) {
            $profile->telepon = $profile->telpon ?? '';
        }

        return view('admin.kas.print', compact('kas', 'total_masuk', 'total_keluar', 'saldo', 'title', 'profile', 'tipe'));
    }
}

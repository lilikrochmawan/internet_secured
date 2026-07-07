<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminKasController extends Controller
{
    public function index()
    {
        $kas = DB::table('tb_kas')
            ->leftJoin('tb_tagihan', 'tb_tagihan.id_tagihan', '=', 'tb_kas.id_tagihan')
            ->select('tb_kas.*', 'tb_tagihan.waktu_bayar')
            ->orderBy('tb_kas.id_kas', 'desc')
            ->get();

        $total_masuk = DB::table('tb_kas')->sum('penerimaan') ?? 0;
        $total_keluar = DB::table('tb_kas')->sum('pengeluaran') ?? 0;
        $saldo = $total_masuk - $total_keluar;

        $pemasukan_bulan_ini = DB::table('tb_kas')
            ->whereMonth('tgl_kas', Carbon::now()->month)
            ->whereYear('tgl_kas', Carbon::now()->year)
            ->sum('penerimaan') ?? 0;

        return view('admin.kas.index', compact('kas', 'total_masuk', 'total_keluar', 'saldo', 'pemasukan_bulan_ini'));
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

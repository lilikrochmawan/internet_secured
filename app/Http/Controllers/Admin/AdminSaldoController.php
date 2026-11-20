<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\SaldoMutasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminSaldoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Query Pelanggan dengan pencarian
        $pelangganQuery = Pelanggan::with('paketDetail')->allowedForUser();

        if (!empty($search)) {
            $pelangganQuery->where(function ($q) use ($search) {
                $q->where('nama_pelanggan', 'like', "%{$search}%")
                  ->orWhere('kode_pelanggan', 'like', "%{$search}%")
                  ->orWhere('no_telp', 'like', "%{$search}%");
            });
        }

        $pelangganList = $pelangganQuery->orderBy('saldo', 'desc')->orderBy('nama_pelanggan', 'asc')->get();

        // Ringkasan Statistik
        $totalSaldoMengendap = Pelanggan::allowedForUser()->sum('saldo') ?? 0;
        
        $now = Carbon::now();
        $totalTopupBulanIni = SaldoMutasi::where('tipe', 'masuk')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('jumlah') ?? 0;

        $totalPakaiBulanIni = SaldoMutasi::where('tipe', 'keluar')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('jumlah') ?? 0;

        $pelangganBersaldoCount = Pelanggan::allowedForUser()->where('saldo', '>', 0)->count();

        // Riwayat Mutasi Keseluruhan (50 data terbaru)
        $mutasiKeseluruhan = SaldoMutasi::with(['pelanggan', 'creator'])
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return view('admin.saldo.index', compact(
            'pelangganList',
            'totalSaldoMengendap',
            'totalTopupBulanIni',
            'totalPakaiBulanIni',
            'pelangganBersaldoCount',
            'mutasiKeseluruhan',
            'search'
        ));
    }

    public function topup(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|integer|exists:tb_pelanggan,id_pelanggan',
            'tipe' => 'required|in:masuk,keluar',
            'jumlah' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string',
            'catat_di_kas' => 'nullable',
        ], [
            'jumlah.min' => 'Nominal minimal mutasi adalah Rp 1.000.',
            'id_pelanggan.required' => 'Pelanggan wajib dipilih.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $pelanggan = Pelanggan::lockForUpdate()->findOrFail($request->id_pelanggan);
                $saldoAwal = (int) ($pelanggan->saldo ?? 0);
                $nominal = (int) $request->jumlah;

                if ($request->tipe === 'keluar') {
                    if ($saldoAwal < $nominal) {
                        throw new \Exception("Saldo pelanggan tidak mencukupi untuk pengurangan ini! (Saldo saat ini: Rp " . number_format($saldoAwal, 0, ',', '.') . ")");
                    }
                    $saldoAkhir = $saldoAwal - $nominal;
                    $kategori = 'penyesuaian';
                } else {
                    $saldoAkhir = $saldoAwal + $nominal;
                    $kategori = 'topup_manual';
                }

                // Update saldo pelanggan
                $pelanggan->update(['saldo' => $saldoAkhir]);

                // Insert mutasi log
                SaldoMutasi::create([
                    'id_pelanggan' => $pelanggan->id_pelanggan,
                    'tipe' => $request->tipe,
                    'jumlah' => $nominal,
                    'saldo_awal' => $saldoAwal,
                    'saldo_akhir' => $saldoAkhir,
                    'kategori' => $kategori,
                    'keterangan' => $request->keterangan ?: (($request->tipe === 'masuk') ? 'Top Up Saldo Manual oleh Admin/Kasir' : 'Pengurangan Saldo oleh Admin/Kasir'),
                    'created_by' => Auth::id(),
                ]);

                // Catat ke Buku Kas jika opsi dicentang
                if ($request->filled('catat_di_kas') && in_array($request->catat_di_kas, [1, '1', 'on', 'true'])) {
                    if ($request->tipe === 'masuk') {
                        $ket = 'Top Up Saldo Pelanggan AN. ' . $pelanggan->nama_pelanggan . ' (' . ($request->keterangan ?: 'Kas Masuk') . ')';
                        DB::table('tb_kas')->insert([
                            'tgl_kas' => date('Y-m-d'),
                            'keterangan' => $ket,
                            'penerimaan' => $nominal,
                            'pengeluaran' => 0,
                            'jenis_kas' => 'topup',
                            'is_saldo' => 0,
                            'status' => 1,
                        ]);
                    } else {
                        $ket = 'Pengurangan/Penyesuaian Saldo Pelanggan AN. ' . $pelanggan->nama_pelanggan . ' (' . ($request->keterangan ?: 'Kas Keluar') . ')';
                        DB::table('tb_kas')->insert([
                            'tgl_kas' => date('Y-m-d'),
                            'keterangan' => $ket,
                            'penerimaan' => 0,
                            'pengeluaran' => $nominal,
                            'jenis_kas' => 'topup',
                            'is_saldo' => 0,
                            'status' => 1,
                        ]);
                    }
                }
            });

            $aksiText = ($request->tipe === 'masuk') ? 'ditambahkan' : 'dikurangi';
            return redirect()->route('admin.saldo.index')->with('success', "Saldo pelanggan berhasil {$aksiText}!");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function customerHistory($id_pelanggan)
    {
        $pelanggan = Pelanggan::with('paketDetail')->findOrFail($id_pelanggan);
        $mutasi = SaldoMutasi::where('id_pelanggan', $id_pelanggan)
            ->with('creator')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'pelanggan' => [
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'nama_pelanggan' => $pelanggan->nama_pelanggan,
                'kode_pelanggan' => $pelanggan->kode_pelanggan,
                'no_telp' => $pelanggan->no_telp,
                'saldo' => (int) ($pelanggan->saldo ?? 0),
                'saldo_formatted' => 'Rp ' . number_format($pelanggan->saldo ?? 0, 0, ',', '.'),
                'paket' => $pelanggan->paketDetail->nama_paket ?? '-',
            ],
            'mutasi' => $mutasi->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i'),
                    'tipe' => $item->tipe,
                    'jumlah' => (int) $item->jumlah,
                    'jumlah_formatted' => ($item->tipe === 'masuk' ? '+' : '-') . ' Rp ' . number_format($item->jumlah, 0, ',', '.'),
                    'saldo_awal_formatted' => 'Rp ' . number_format($item->saldo_awal, 0, ',', '.'),
                    'saldo_akhir_formatted' => 'Rp ' . number_format($item->saldo_akhir, 0, ',', '.'),
                    'kategori' => $item->kategori,
                    'keterangan' => $item->keterangan ?? '-',
                    'admin' => $item->creator->nama_user ?? 'Sistem',
                ];
            }),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;

class AdminRiwayatTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $metode = $request->input('metode_pembayaran');
        $search = $request->input('search');
        
        // Query for Riwayat Pembayaran (Status = 1)
        $query = Tagihan::with(['pelanggan', 'penerima'])->where('status_bayar', 1);
        
        if ($search) {
            $query->whereHas('pelanggan', function($q) use ($search) {
                $q->where('nama_pelanggan', 'like', "%{$search}%");
            });
        }
        
        if ($metode) {
            if ($metode === 'manual') {
                $query->where(function($q) {
                    $q->where('metode_pembayaran', 'Manual/Tunai')
                      ->orWhere(function($sub) {
                          $sub->whereNull('metode_pembayaran')->whereNotNull('user_id');
                      });
                });
            } elseif ($metode === 'gateway') {
                $query->where(function($q) {
                    $q->where('metode_pembayaran', 'Payment Gateway')
                      ->orWhere(function($sub) {
                          $sub->whereNull('metode_pembayaran')->whereNull('user_id');
                      });
                });
            } else {
                $query->where('metode_pembayaran', $metode);
            }
        }
        
        // Sort descending by tgl_bayar or waktu_bayar
        $riwayat = $query->orderBy('waktu_bayar', 'desc')
                         ->orderBy('tgl_bayar', 'desc')
                         ->paginate(20)
                         ->appends($request->all());

        // Get Top 10 Menunggak
        $topMenunggak = DB::table('tb_tagihan')
            ->join('tb_pelanggan', 'tb_tagihan.id_pelanggan', '=', 'tb_pelanggan.id_pelanggan')
            ->select('tb_tagihan.id_pelanggan', 'tb_pelanggan.nama_pelanggan', 'tb_pelanggan.no_telp', DB::raw('COUNT(tb_tagihan.id_tagihan) as jumlah_tunggakan'), DB::raw('SUM(tb_tagihan.jml_bayar) as total_tunggakan'))
            ->where(function ($q) {
                $q->where('tb_tagihan.status_bayar', '!=', 1)
                  ->orWhereNull('tb_tagihan.status_bayar');
            })
            ->groupBy('tb_tagihan.id_pelanggan', 'tb_pelanggan.nama_pelanggan', 'tb_pelanggan.no_telp')
            ->orderBy('jumlah_tunggakan', 'desc')
            ->limit(10)
            ->get();

        // Get all unique payment methods for the filter
        $metodeList = Tagihan::whereNotNull('metode_pembayaran')
            ->where('metode_pembayaran', '!=', '')
            ->distinct()
            ->pluck('metode_pembayaran');

        return view('admin.riwayat_transaksi.index', compact('riwayat', 'topMenunggak', 'metodeList', 'metode', 'search'));
    }

    public function laporan(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        
        $tagihan = Tagihan::with(['penerima', 'pelanggan'])
            ->where('status_bayar', 1)
            ->whereNotNull('user_id')
            ->whereMonth('waktu_bayar', $bulan)
            ->whereYear('waktu_bayar', $tahun)
            ->get();

        $laporan = [];
        $grouped = $tagihan->groupBy('user_id');

        foreach ($grouped as $userId => $items) {
            $user = $items->first()->penerima;
            
            // Map and count frequency if needed, but simple unique list is requested
            $pelangganNames = $items->map(function($item) {
                return $item->pelanggan ? $item->pelanggan->nama_pelanggan : 'Pelanggan Dihapus';
            })->unique()->values()->all();

            $laporan[] = [
                'nama_user' => $user ? $user->nama_user : 'Unknown',
                'total_transaksi' => $items->count(),
                'total_nominal' => $items->sum(function($item) {
                    return $item->terbayar > 0 ? $item->terbayar : $item->jml_bayar;
                }),
                'pelanggan_list' => $pelangganNames
            ];
        }

        // Sort descending by total nominal
        usort($laporan, function($a, $b) {
            return $b['total_nominal'] <=> $a['total_nominal'];
        });

        return response()->json($laporan);
    }
}

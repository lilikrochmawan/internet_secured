<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Models\Informasi;
use App\Services\TagihanService;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ApiDashboardController extends Controller
{
    public function __construct(
        private TagihanService $tagihanService
    ) {
    }

    public function index()
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pelanggan tidak ditemukan.'
            ], 404);
        }

        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);

        $tagihanBulanIni = $this->tagihanService->sumUnpaidBulanIni($pelangganIds);
        $tagihanManual = $this->tagihanService->sumUnpaidManual($pelangganIds);
        $tagihanTotal = $tagihanBulanIni + $tagihanManual;
        $jumlahAkunGabung = count($pelangganIds);

        // Paket rekomendasi
        $paketRekomendasi = Paket::where(function ($query) {
                $query->where('nama_paket', 'like', '%20%')
                    ->orWhere('nama_paket', 'like', '%30%');
            })
            ->orderBy('harga')
            ->get();

        if ($paketRekomendasi->isEmpty()) {
            $paketRekomendasi = Paket::where(function ($query) {
                    $query->where('nama_paket', 'like', '%Mb%')
                        ->orWhere('nama_paket', 'like', '%Mbps%');
                })
                ->orderBy('harga')
                ->limit(2)
                ->get();
        }

        // Pengumuman/Informasi terakhir
        $informasi = Informasi::orderByDesc('id_informasi')->first();

        // 5 invoice terakhir
        $invoices = DB::table('tb_tagihan')
            ->whereIn('id_pelanggan', $pelangganIds)
            ->orderBy('id_tagihan', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nama_user' => $user->nama_user,
                    'level' => $user->level,
                ],
                'pelanggan' => [
                    'id_pelanggan' => $pelanggan->id_pelanggan,
                    'kode_pelanggan' => $pelanggan->kode_pelanggan,
                    'nama_pelanggan' => $pelanggan->nama_pelanggan,
                    'no_telp' => $pelanggan->no_telp,
                    'alamat' => $pelanggan->alamat,
                    'paket_name' => $pelanggan->paketDetail?->nama_paket ?? 'N/A',
                    'paket_harga' => $pelanggan->paketDetail?->harga ?? 0,
                    'ip_address' => $pelanggan->ip_address,
                ],
                'tagihan' => [
                    'total' => $tagihanTotal,
                    'bulan_ini' => $tagihanBulanIni,
                    'manual' => $tagihanManual,
                ],
                'jumlah_akun_gabung' => $jumlahAkunGabung,
                'paket_rekomendasi' => $paketRekomendasi,
                'informasi' => $informasi,
                'invoices_history' => $invoices,
            ]
        ]);
    }

    public function routerStats()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $mikrotikService = app(MikrotikService::class);
        $stats = $mikrotikService->getPppoeStats((string)$user->username);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}

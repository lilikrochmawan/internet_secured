<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\Informasi;
use App\Services\TagihanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private TagihanService $tagihanService
    ) {
    }

    public function showLogin()
    {
        $profile = \Illuminate\Support\Facades\DB::table('tb_profile')->first();
        return view('auth.login', compact('profile'));
    }

    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        // Clean phone number - remove non-digits
        $phone = preg_replace('/[^0-9]/', '', $request->phone);

        // Find pelanggan by phone number
        $pelanggan = Pelanggan::findByPhone($phone);

        if (!$pelanggan) {
            return back()->withErrors([
                'phone' => 'Nomor HP tidak ditemukan dalam sistem.',
            ])->withInput();
        }

        // Find user associated with this pelanggan
        $user = User::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

        if (!$user) {
            return back()->withErrors([
                'phone' => 'Akun pengguna tidak ditemukan untuk pelanggan ini.',
            ])->withInput();
        }

        // Login user without password check
        Auth::login($user, $request->boolean('remember'));

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;
        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);

        $tagihanBulanIni = $this->tagihanService->sumUnpaidBulanIni($pelangganIds);
        $tagihanManual = $this->tagihanService->sumUnpaidManual($pelangganIds);
        $tagihanTotal = $tagihanBulanIni + $tagihanManual + $this->tagihanService->sumPiutang($pelangganIds);
        $jumlahAkunGabung = count($pelangganIds);

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

        $informasi = Informasi::orderByDesc('id_informasi')->first();
        $hasInformasi = Informasi::exists();

        // Fetch last 5 invoices for history card
        $invoices = \Illuminate\Support\Facades\DB::table('tb_tagihan')
            ->whereIn('id_pelanggan', $pelangganIds)
            ->orderBy('id_tagihan', 'desc')
            ->limit(5)
            ->get();

        $saldo = (int) ($pelanggan->saldo ?? 0);
        $mutasiSaldo = \App\Models\SaldoMutasi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get();

        $pgate = \App\Models\Pgate::first();
        $clientKey = $pgate ? trim($pgate->tclientkey ?? '') : '';
        $isSandbox = $pgate ? ($pgate->mode === 'sandbox' || (!$pgate->mode && str_starts_with($clientKey, 'SB-'))) : true;
        $snapJsUrl = $isSandbox
            ? 'https://app.sandbox.midtrans.com/snap/snap.js'
            : 'https://app.midtrans.com/snap/snap.js';

        return view('dashboard', [
            'user' => $user,
            'pelanggan' => $pelanggan,
            'saldo' => $saldo,
            'mutasiSaldo' => $mutasiSaldo,
            'clientKey' => $clientKey,
            'snapJsUrl' => $snapJsUrl,
            'paket' => $pelanggan->paketDetail,
            'paketRekomendasi' => $paketRekomendasi,
            'tagihanTotal' => $tagihanTotal,
            'tagihanBulanIni' => $tagihanBulanIni,
            'tagihanManual' => $tagihanManual,
            'jumlahAkunGabung' => $jumlahAkunGabung,
            'informasi' => $informasi,
            'hasInformasi' => $hasInformasi,
            'invoices' => $invoices,
        ]);
    }

        public function riwayatSaldo()
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;
        if (!$pelanggan) return response()->json(['error' => 'Pelanggan not found'], 404);

        $mutasi = \App\Models\SaldoMutasi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'saldo' => (int) ($pelanggan->saldo ?? 0),
            'saldo_formatted' => 'Rp ' . number_format($pelanggan->saldo ?? 0, 0, ',', '.'),
            'mutasi' => $mutasi->map(function($m) {
                return [
                    'tanggal' => \Carbon\Carbon::parse($m->created_at)->translatedFormat('d F Y H:i'),
                    'tipe' => $m->tipe,
                    'jumlah' => (int) $m->jumlah,
                    'jumlah_formatted' => ($m->tipe === 'masuk' ? '+' : '-') . ' Rp ' . number_format($m->jumlah, 0, ',', '.'),
                    'saldo_akhir_formatted' => 'Rp ' . number_format($m->saldo_akhir, 0, ',', '.'),
                    'keterangan' => $m->keterangan ?: '-',
                ];
            })
        ]);
    }

    public function getRouterStats()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $mikrotikService = app(\App\Services\MikrotikService::class);
        $stats = $mikrotikService->getPppoeStats((string)$user->username);

        return response()->json($stats);
    }

    public function profil()
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;
        
        if (!$pelanggan) {
            abort(404, 'Pelanggan tidak ditemukan.');
        }

        $pelangganIds = $this->tagihanService->getPelangganIdsByPhone($pelanggan->no_telp);
        $tagihanBulanIni = $this->tagihanService->sumUnpaidBulanIni($pelangganIds);
        $tagihanManual = $this->tagihanService->sumUnpaidManual($pelangganIds);
        $tagihanTotal = $tagihanBulanIni + $tagihanManual + $this->tagihanService->sumPiutang($pelangganIds);
        
        // Status layanan diisolir jika ada tagihan yang status blokirnya aktif (blokir_status = 1)
        $isBlocked = \App\Models\Tagihan::whereIn('id_pelanggan', $pelangganIds)
            ->where('blokir_status', 1)
            ->exists();
        
        $statusPaket = $isBlocked ? 'Terisolir' : 'Aktif';

        return view('profile', [
            'user' => $user,
            'pelanggan' => $pelanggan,
            'statusPaket' => $statusPaket,
        ]);
    }
}

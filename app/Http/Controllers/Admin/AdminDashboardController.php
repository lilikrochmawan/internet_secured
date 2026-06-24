<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Keluhan;
use App\Models\Tagihan;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $thisMonth = Carbon::now()->format('Y-m');
        $thisMonthForTagihan = Carbon::now()->format('mY'); // format 'mY' untuk tagihan, misal '062026'

        // Get allowed customer IDs for query scoping
        $allowedPelangganIds = Pelanggan::allowedForUser()->pluck('id_pelanggan')->toArray();

        // 1. Ambil Pemasukan Hari Ini (dari tb_kas)
        $kasHariIni = DB::table('tb_kas')
            ->where('tgl_kas', $today)
            ->sum('penerimaan');

        // 2. Keuangan Bulan Ini (dari tb_kas)
        $kasBulanIni = DB::table('tb_kas')
            ->where('tgl_kas', 'like', $thisMonth . '%')
            ->sum('penerimaan');

        $keluarBulanIni = DB::table('tb_kas')
            ->where('tgl_kas', 'like', $thisMonth . '%')
            ->sum('pengeluaran');

        $saldoBulanIni = $kasBulanIni - $keluarBulanIni;

        // 1.a. Data Tren Keuangan 6 Bulan Terakhir (Pemasukan vs Pengeluaran)
        $keuangan6Bulan = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStr = $month->format('Y-m');
            $monthLabel = $month->translatedFormat('M Y');
            
            $pemasukan = DB::table('tb_kas')
                ->where('tgl_kas', 'like', $monthStr . '%')
                ->sum('penerimaan');
                
            $pengeluaran = DB::table('tb_kas')
                ->where('tgl_kas', 'like', $monthStr . '%')
                ->sum('pengeluaran');
                
            $keuangan6Bulan[] = [
                'label' => $monthLabel,
                'pemasukan' => (int) $pemasukan,
                'pengeluaran' => (int) $pengeluaran,
            ];
        }

        // 2. Status Pelanggan (Aktif, Terisolir, Non-aktif)
        $totalPelanggan = count($allowedPelangganIds);
        $terisolirCount = Tagihan::whereNull('status_bayar')
            ->where('blokir_status', 1)
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->distinct('id_pelanggan')
            ->count('id_pelanggan');

        $bukaSementaraCount = Tagihan::whereNull('status_bayar')
            ->where('jatuh_tempo', '<', Carbon::now())
            ->where(function($q) {
                $q->whereNull('blokir_status')->orWhere('blokir_status', '!=', 1);
            })
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->distinct('id_pelanggan')
            ->count('id_pelanggan');

        // Non-aktif: Pelanggan yang nunggak > 60 hari
        $nonaktifCount = Tagihan::whereNull('status_bayar')
            ->where('jatuh_tempo', '<', Carbon::now()->subDays(60))
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->distinct('id_pelanggan')
            ->count('id_pelanggan');

        $aktifCount = max(0, $totalPelanggan - $terisolirCount - $nonaktifCount);

        // 2.a. Status Pembayaran
        $regularBelumBayar = Tagihan::where(function($q) {
                $q->whereNull('status_bayar')->orWhereIn('status_bayar', [0, '0', 'belum', '']);
            })
            ->where('bulan_tahun', $thisMonthForTagihan)
            ->where(function($q) {
                $q->whereNull('manual_invoice')->orWhere('manual_invoice', 0);
            })
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->count();

        $manualBelumBayar = Tagihan::where(function($q) {
                $q->whereNull('status_bayar')->orWhereIn('status_bayar', [0, '0', 'belum', '']);
            })
            ->where('manual_invoice', 1)
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->count();

        $belumTerbayarCount = $regularBelumBayar + $manualBelumBayar;

        $suksesBayarCount = Tagihan::where('status_bayar', 1)
            ->where('bulan_tahun', $thisMonthForTagihan)
            ->where(function($q) {
                $q->whereNull('manual_invoice')->orWhere('manual_invoice', 0);
            })
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->count();

        $bukaSementaraCount = Tagihan::where(function($q) {
                $q->whereNull('status_bayar')->orWhereIn('status_bayar', [0, '0', 'belum', '']);
            })
            ->where('bulan_tahun', $thisMonthForTagihan)
            ->where('jatuh_tempo', '<', Carbon::now())
            ->where(function($q) {
                $q->whereNull('blokir_status')->orWhere('blokir_status', '!=', 1);
            })
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->distinct('id_pelanggan')
            ->count('id_pelanggan');


        // 3. Keluhan Stats
        $keluhanMenunggu = Keluhan::where('status_keluhan', 'menunggu')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->count();
        $keluhanProses = Keluhan::where('status_keluhan', 'proses')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->count();
        $keluhanSelesai = Keluhan::where('status_keluhan', 'selesai')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->count();
        $keluhanTotal = $keluhanMenunggu + $keluhanProses + $keluhanSelesai;

        // 4. Status Isolasi & Tunggakan
        $jatuhTempo3Hari = Tagihan::whereNull('status_bayar')
            ->whereBetween('jatuh_tempo', [Carbon::now(), Carbon::now()->addDays(3)])
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->distinct('id_pelanggan')
            ->count('id_pelanggan');

        // 5. Status Perangkat Mikrotik (Realtime Check)
        require_once base_path('include/routeros_api.php');
        $mikrotikDevices = DB::table('tbl_mikrotik')->get();
        $totalMikrotik = $mikrotikDevices->count();
        $mikrotikOnline = 0;
        $mikrotikOffline = 0;
        $deviceList = [];

        foreach ($mikrotikDevices as $dev) {
            $api = new \RouterosAPI();
            $api->timeout = 1;
            $api->attempts = 1;
            $api->delay = 0;
            
            $isOnline = false;
            try {
                if ($api->connect($dev->ip, $dev->username, $dev->password)) {
                    $isOnline = true;
                    $api->disconnect();
                }
            } catch (\Exception $e) {
                $isOnline = false;
            }
            
            if ($isOnline) {
                $mikrotikOnline++;
            } else {
                $mikrotikOffline++;
            }
            
            $deviceList[] = (object) [
                'nama' => $dev->nama_mikrotik,
                'ip' => $dev->ip,
                'online' => $isOnline,
                'waktu' => Carbon::now()->format('d M Y, H:i')
            ];
        }

        // 6. Infrastruktur ODC / ODP
        $totalOdc = DB::table('tbl_odc')->count();
        $totalOdp = DB::table('tbl_odp')->count();
        $portTerpakai = DB::table('tb_pelanggan')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->whereNotNull('odp')
            ->where('odp', '<>', '')
            ->count();
        $totalCapacityPort = (int) DB::table('tbl_odp')->sum('port_odp');
        $portTersedia = max(0, $totalCapacityPort - $portTerpakai);

        // 7. Aktivitas Terbaru (Isolate / Restore)
        // Ambil pembayaran tagihan terbaru (RESTORE)
        $recentPayments = Tagihan::with('pelanggan')
            ->whereNotNull('waktu_bayar')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->orderBy('waktu_bayar', 'desc')
            ->limit(10)
            ->get();

        // Ambil pemblokiran terbaru (ISOLATE)
        $recentBlocks = Tagihan::with('pelanggan')
            ->where('blokir_status', 1)
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->orderBy('id_tagihan', 'desc')
            ->limit(10)
            ->get();

        $aktivitasTerbaru = [];
        foreach ($recentPayments as $tx) {
            if ($tx->pelanggan) {
                $aktivitasTerbaru[] = (object) [
                    'waktu' => Carbon::parse($tx->waktu_bayar)->format('d/m/Y, H.i.s'),
                    'customer' => $tx->pelanggan->nama_pelanggan,
                    'aksi' => 'RESTORE',
                    'status' => 'Sukses'
                ];
            }
        }
        foreach ($recentBlocks as $tx) {
            if ($tx->pelanggan) {
                $blockTime = $tx->jatuh_tempo ? Carbon::parse($tx->jatuh_tempo) : Carbon::now();
                $aktivitasTerbaru[] = (object) [
                    'waktu' => $blockTime->format('d/m/Y, H.i.s'),
                    'customer' => $tx->pelanggan->nama_pelanggan,
                    'aksi' => 'ISOLATE',
                    'status' => 'Sukses'
                ];
            }
        }

        // Urutkan aktivitas berdasarkan waktu DESC
        usort($aktivitasTerbaru, function($a, $b) {
            $timeA = strtotime(str_replace('.', ':', $a->waktu));
            $timeB = strtotime(str_replace('.', ':', $b->waktu));
            return $timeB <=> $timeA;
        });
        $aktivitasTerbaru = array_slice($aktivitasTerbaru, 0, 10);

        // Profile data
        $profile = DB::table('tb_profile')->first();

        // Statistik Tagihan (Bulan ini) untuk kompatibilitas jika dibutuhkan
        $totalLunasCount = Tagihan::where('status_bayar', 1)
            ->where('bulan_tahun', 'like', $thisMonthForTagihan . '%')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->count();

        $totalBelumBayarCount = Tagihan::whereNull('status_bayar')
            ->where('bulan_tahun', 'like', $thisMonthForTagihan . '%')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->count();

        $totalPenerimaanTagihan = Tagihan::where('status_bayar', 1)
            ->where('bulan_tahun', 'like', $thisMonthForTagihan . '%')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->sum('jml_bayar');

        $totalBelumBayarTagihan = Tagihan::whereNull('status_bayar')
            ->where('bulan_tahun', 'like', $thisMonthForTagihan . '%')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->sum('jml_bayar');

        $prediksiPendapatan = Tagihan::where('bulan_tahun', 'like', $thisMonthForTagihan . '%')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->sum('jml_bayar');

        $transaksiTerbaru = Tagihan::with(['pelanggan', 'penerima'])
            ->whereNotNull('waktu_bayar')
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->orderBy('waktu_bayar', 'desc')
            ->limit(10)
            ->get();

        // 8. Cek Pengingat Jatuh Tempo Lisensi (H-14)
        $showLicenseWarning = false;
        $licenseDaysRemaining = null;

        if ($profile && $profile->license_status === 'active' && $profile->license_expires_at) {
            $expiry = Carbon::parse($profile->license_expires_at);
            $days = Carbon::now()->diffInDays($expiry, false);

            if ($days >= 0 && $days <= 14) {
                $licenseDaysRemaining = (int) ceil($days);
                
                if (!session()->has('license_warning_shown')) {
                    $showLicenseWarning = true;
                    session()->put('license_warning_shown', true);
                }
            }
        }

        return view('admin.dashboard', compact(
            'kasHariIni',
            'kasBulanIni',
            'keluarBulanIni',
            'saldoBulanIni',
            'keuangan6Bulan',
            'totalPelanggan',
            'aktifCount',
            'terisolirCount',
            'nonaktifCount',
            'bukaSementaraCount',
            'belumTerbayarCount',
            'suksesBayarCount',
            'jatuhTempo3Hari',
            'keluhanMenunggu',
            'keluhanProses',
            'keluhanSelesai',
            'keluhanTotal',
            'totalMikrotik',
            'mikrotikOnline',
            'mikrotikOffline',
            'deviceList',
            'totalOdc',
            'totalOdp',
            'portTerpakai',
            'portTersedia',
            'aktivitasTerbaru',
            'profile',
            'totalLunasCount',
            'totalBelumBayarCount',
            'totalPenerimaanTagihan',
            'totalBelumBayarTagihan',
            'prediksiPendapatan',
            'transaksiTerbaru',
            'showLicenseWarning',
            'licenseDaysRemaining'
        ));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Branch;
use App\Models\SubBranch;
use Carbon\Carbon;

class AdminMitraController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->level !== 'admin' && !$user->hasMenuAccess('mitra')) {
            abort(403, 'Akses Ditolak');
        }

        $isAdmin = ($user->level === 'admin');

        if ($isAdmin) {
            // ADMIN VIEW
            $mitras = User::where('level', 'mitra')->get();
            $mitraMonthlyData = [];

            foreach ($mitras as $mitra) {
                // Get config
                $mitra->config = DB::table('tbl_mitra_config')
                    ->where('id_user', $mitra->id)
                    ->first();
                
                // Get total earned commission
                $mitra->total_komisi = DB::table('tbl_mitra_komisi_logs')
                    ->where('id_user', $mitra->id)
                    ->sum('komisi_diterima') ?? 0;

                // Get total paid payout
                $mitra->total_payout = DB::table('tbl_mitra_payouts')
                    ->where('id_user', $mitra->id)
                    ->sum('jumlah') ?? 0;

                // Calculate unpaid balance per month
                $monthlyCommissions = DB::table('tbl_mitra_komisi_logs')
                    ->where('id_user', $mitra->id)
                    ->select(
                        DB::raw('MONTH(created_at) as bulan'),
                        DB::raw('YEAR(created_at) as tahun'),
                        DB::raw('SUM(komisi_diterima) as total_komisi')
                    )
                    ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                    ->get();

                $saldo_belum_dibayar = 0;
                $monthlyData = [];

                foreach ($monthlyCommissions as $mc) {
                    $isPaid = DB::table('tbl_mitra_payouts')
                        ->where('id_user', $mitra->id)
                        ->where('payout_month', $mc->bulan)
                        ->where('payout_year', $mc->tahun)
                        ->exists();

                    if (!$isPaid) {
                        $saldo_belum_dibayar += $mc->total_komisi;
                    }

                    $monthName = Carbon::create()->month($mc->bulan)->translatedFormat('F');
                    $monthlyData[] = [
                        'bulan' => $mc->bulan,
                        'tahun' => $mc->tahun,
                        'label' => $monthName . ' ' . $mc->tahun,
                        'komisi' => (float)$mc->total_komisi,
                        'is_paid' => $isPaid
                    ];
                }

                $mitra->saldo_belum_dibayar = $saldo_belum_dibayar;
                $mitraMonthlyData[$mitra->id] = $monthlyData;

                // Get customer count in their region
                $accessList = DB::table('tb_user_branch_access')->where('id_user', $mitra->id)->get();
                if ($accessList->isEmpty()) {
                    $mitra->customer_count = 0;
                } else {
                    $cQuery = DB::table('tb_pelanggan');
                    $cQuery->where(function($q) use ($accessList) {
                        foreach ($accessList as $acc) {
                            if (is_null($acc->id_sub_branch)) {
                                $q->orWhere('id_branch', $acc->id_branch);
                            } else {
                                $q->orWhere(function($subQ) use ($acc) {
                                    $subQ->where('id_branch', $acc->id_branch)
                                         ->where('id_sub_branch', $acc->id_sub_branch);
                                });
                            }
                        }
                    });
                    $mitra->customer_count = $cQuery->count();
                }
            }

            return view('admin.mitra.index', compact('mitras', 'mitraMonthlyData', 'isAdmin'));
        } else {
            // MITRA VIEW
            $config = DB::table('tbl_mitra_config')
                ->where('id_user', $user->id)
                ->first();

            // Fetch branch/sub-branch access list
            $accessList = DB::table('tb_user_branch_access')->where('id_user', $user->id)->get();
            
            // Get customers under access list
            $customersQuery = DB::table('tb_pelanggan')
                ->leftJoin('tb_branch', 'tb_branch.id', '=', 'tb_pelanggan.id_branch')
                ->leftJoin('tb_sub_branch', 'tb_sub_branch.id', '=', 'tb_pelanggan.id_sub_branch')
                ->select('tb_pelanggan.*', 'tb_branch.nama_branch', 'tb_sub_branch.nama_sub_branch');

            if ($accessList->isEmpty()) {
                $customersQuery->whereNull('tb_pelanggan.id_pelanggan'); // force empty
            } else {
                $customersQuery->where(function($q) use ($accessList) {
                    foreach ($accessList as $acc) {
                        if (is_null($acc->id_sub_branch)) {
                            $q->orWhere('tb_pelanggan.id_branch', $acc->id_branch);
                        } else {
                            $q->orWhere(function($subQ) use ($acc) {
                                $subQ->where('tb_pelanggan.id_branch', $acc->id_branch)
                                     ->where('tb_pelanggan.id_sub_branch', $acc->id_sub_branch);
                            });
                        }
                    }
                });
            }
            $pelanggans = $customersQuery->paginate(10);

            // Access list filter helper for statistics
            $applyAccessFilter = function($query) use ($accessList) {
                if ($accessList->isEmpty()) {
                    $query->whereNull('tb_pelanggan.id_pelanggan'); // force empty
                } else {
                    $query->where(function($q) use ($accessList) {
                        foreach ($accessList as $acc) {
                            if (is_null($acc->id_sub_branch)) {
                                $q->orWhere('tb_pelanggan.id_branch', $acc->id_branch);
                            } else {
                                $q->orWhere(function($subQ) use ($acc) {
                                    $subQ->where('tb_pelanggan.id_branch', $acc->id_branch)
                                         ->where('tb_pelanggan.id_sub_branch', $acc->id_sub_branch);
                                });
                            }
                        }
                    });
                }
            };

            // Total statistics
            $totalKomisiQuery = DB::table('tbl_mitra_komisi_logs')
                ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tbl_mitra_komisi_logs.id_pelanggan')
                ->where('tbl_mitra_komisi_logs.id_user', $user->id);
            $applyAccessFilter($totalKomisiQuery);
            $total_komisi = $totalKomisiQuery->sum('tbl_mitra_komisi_logs.komisi_diterima') ?? 0;

            // Calculate unpaid balance per month
            $monthlyCommissionsQuery = DB::table('tbl_mitra_komisi_logs')
                ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tbl_mitra_komisi_logs.id_pelanggan')
                ->where('tbl_mitra_komisi_logs.id_user', $user->id)
                ->select(
                    DB::raw('MONTH(tbl_mitra_komisi_logs.created_at) as bulan'),
                    DB::raw('YEAR(tbl_mitra_komisi_logs.created_at) as tahun'),
                    DB::raw('SUM(tbl_mitra_komisi_logs.komisi_diterima) as total_komisi')
                )
                ->groupBy(DB::raw('YEAR(tbl_mitra_komisi_logs.created_at)'), DB::raw('MONTH(tbl_mitra_komisi_logs.created_at)'));
            $applyAccessFilter($monthlyCommissionsQuery);
            $monthlyCommissions = $monthlyCommissionsQuery->get();

            $saldo_belum_dibayar = 0;
            foreach ($monthlyCommissions as $mc) {
                $isPaid = DB::table('tbl_mitra_payouts')
                    ->where('id_user', $user->id)
                    ->where('payout_month', $mc->bulan)
                    ->where('payout_year', $mc->tahun)
                    ->exists();

                if (!$isPaid) {
                    $saldo_belum_dibayar += $mc->total_komisi;
                }
            }

            $total_payout = DB::table('tbl_mitra_payouts')
                ->where('id_user', $user->id)
                ->sum('jumlah') ?? 0;

            $komisiBulanIniQuery = DB::table('tbl_mitra_komisi_logs')
                ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tbl_mitra_komisi_logs.id_pelanggan')
                ->where('tbl_mitra_komisi_logs.id_user', $user->id)
                ->whereMonth('tbl_mitra_komisi_logs.created_at', Carbon::now()->month)
                ->whereYear('tbl_mitra_komisi_logs.created_at', Carbon::now()->year);
            $applyAccessFilter($komisiBulanIniQuery);
            $komisi_bulan_ini = $komisiBulanIniQuery->sum('tbl_mitra_komisi_logs.komisi_diterima') ?? 0;

            // Histori komisi terbaru
            $recentLogsQuery = DB::table('tbl_mitra_komisi_logs')
                ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tbl_mitra_komisi_logs.id_pelanggan')
                ->where('tbl_mitra_komisi_logs.id_user', $user->id)
                ->select('tbl_mitra_komisi_logs.*', 'tb_pelanggan.nama_pelanggan')
                ->orderBy('tbl_mitra_komisi_logs.created_at', 'desc')
                ->limit(10);
            $applyAccessFilter($recentLogsQuery);
            $recent_logs = $recentLogsQuery->get();

            // Payout logs
            $payout_logs = DB::table('tbl_mitra_payouts')
                ->where('id_user', $user->id)
                ->orderBy('tgl_payout', 'desc')
                ->get();

            return view('admin.mitra.index', compact('config', 'pelanggans', 'total_komisi', 'total_payout', 'saldo_belum_dibayar', 'komisi_bulan_ini', 'recent_logs', 'payout_logs', 'isAdmin'));
        }
    }

    public function updateConfig(Request $request)
    {
        $user = Auth::user();
        if ($user->level !== 'admin') {
            abort(403, 'Akses Ditolak');
        }

        $request->validate([
            'id_user' => 'required|integer',
            'tipe_komisi' => 'required|string|in:flat,persentase',
            'nilai_komisi' => 'required|numeric|min:0',
        ]);

        DB::table('tbl_mitra_config')->updateOrInsert(
            ['id_user' => $request->id_user],
            [
                'tipe_komisi' => $request->tipe_komisi,
                'nilai_komisi' => $request->nilai_komisi,
                'updated_at' => now()
            ]
        );

        // Recalculate historical commission logs and tb_kas expenses
        $logs = DB::table('tbl_mitra_komisi_logs')
            ->where('id_user', $request->id_user)
            ->get();

        foreach ($logs as $log) {
            $newKomisi = 0;
            if ($request->tipe_komisi === 'flat') {
                $newKomisi = $request->nilai_komisi;
            } else {
                $newKomisi = ($request->nilai_komisi / 100) * $log->jumlah_bayar;
            }

            // Update commission log
            DB::table('tbl_mitra_komisi_logs')
                ->where('id', $log->id)
                ->update([
                    'tipe_komisi' => $request->tipe_komisi,
                    'nilai_komisi' => $request->nilai_komisi,
                    'komisi_diterima' => $newKomisi
                ]);

            // Update tb_kas expense entry
            DB::table('tb_kas')
                ->where('id_tagihan', $log->id_tagihan)
                ->where('pengeluaran', '>', 0)
                ->where('keterangan', 'like', 'Bagi Hasil Mitra%')
                ->update([
                    'pengeluaran' => $newKomisi
                ]);
        }

        return redirect()->route('admin.mitra.index')->with('success', 'Konfigurasi bagi hasil mitra berhasil diperbarui!');
    }

    public function laporan(Request $request)
    {
        $user = Auth::user();
        if ($user->level !== 'admin' && !$user->hasMenuAccess('mitra')) {
            abort(403, 'Akses Ditolak');
        }

        $isAdmin = ($user->level === 'admin');
        
        // Pilih mitra target laporan
        if ($isAdmin) {
            $mitraId = $request->get('id_user');
            $mitras = User::where('level', 'mitra')->get();
            if (!$mitraId && $mitras->isNotEmpty()) {
                $mitraId = $mitras->first()->id;
            }
            $targetUser = User::find($mitraId);
        } else {
            $mitraId = $user->id;
            $mitras = collect([$user]);
            $targetUser = $user;
        }

        $tahun = $request->get('tahun', date('Y'));
        $bulan = $request->get('bulan', date('m'));

        if (!$targetUser) {
            return redirect()->route('admin.mitra.index')->withErrors(['error' => 'Mitra tidak ditemukan.']);
        }

        // Fetch branch/sub-branch access list for target partner
        $accessList = DB::table('tb_user_branch_access')->where('id_user', $mitraId)->get();

        $applyAccessFilter = function($query) use ($accessList) {
            if ($accessList->isEmpty()) {
                $query->whereNull('tb_pelanggan.id_pelanggan'); // force empty
            } else {
                $query->where(function($q) use ($accessList) {
                    foreach ($accessList as $acc) {
                        if (is_null($acc->id_sub_branch)) {
                            $q->orWhere('tb_pelanggan.id_branch', $acc->id_branch);
                        } else {
                            $q->orWhere(function($subQ) use ($acc) {
                                $subQ->where('tb_pelanggan.id_branch', $acc->id_branch)
                                     ->where('tb_pelanggan.id_sub_branch', $acc->id_sub_branch);
                            });
                        }
                    }
                });
            }
        };

        // 1. Rekap bulanan untuk tahun berjalan
        $rekapBulananQuery = DB::table('tbl_mitra_komisi_logs')
            ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tbl_mitra_komisi_logs.id_pelanggan')
            ->where('tbl_mitra_komisi_logs.id_user', $mitraId)
            ->whereYear('tbl_mitra_komisi_logs.created_at', $tahun)
            ->select(
                DB::raw('MONTH(tbl_mitra_komisi_logs.created_at) as bulan'),
                DB::raw('SUM(tbl_mitra_komisi_logs.jumlah_bayar) as total_omset'),
                DB::raw('SUM(tbl_mitra_komisi_logs.komisi_diterima) as total_komisi'),
                DB::raw('COUNT(tbl_mitra_komisi_logs.id) as total_transaksi')
            )
            ->groupBy(DB::raw('MONTH(tbl_mitra_komisi_logs.created_at)'))
            ->orderBy('bulan', 'asc');
        $applyAccessFilter($rekapBulananQuery);
        $rekapBulanan = $rekapBulananQuery->get();

        // 2. Rekap detail transaksi untuk bulan & tahun terpilih
        $detailTransaksiQuery = DB::table('tbl_mitra_komisi_logs')
            ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tbl_mitra_komisi_logs.id_pelanggan')
            ->leftJoin('tb_tagihan', 'tb_tagihan.id_tagihan', '=', 'tbl_mitra_komisi_logs.id_tagihan')
            ->where('tbl_mitra_komisi_logs.id_user', $mitraId)
            ->whereYear('tbl_mitra_komisi_logs.created_at', $tahun)
            ->whereMonth('tbl_mitra_komisi_logs.created_at', $bulan)
            ->select('tbl_mitra_komisi_logs.*', 'tb_pelanggan.nama_pelanggan', 'tb_tagihan.waktu_bayar')
            ->orderBy('tbl_mitra_komisi_logs.created_at', 'desc');
        $applyAccessFilter($detailTransaksiQuery);
        $detailTransaksi = $detailTransaksiQuery->get();

        // 3. Rekap tahunan kumulatif
        $rekapTahunanQuery = DB::table('tbl_mitra_komisi_logs')
            ->join('tb_pelanggan', 'tb_pelanggan.id_pelanggan', '=', 'tbl_mitra_komisi_logs.id_pelanggan')
            ->where('tbl_mitra_komisi_logs.id_user', $mitraId)
            ->select(
                DB::raw('YEAR(tbl_mitra_komisi_logs.created_at) as tahun_log'),
                DB::raw('SUM(tbl_mitra_komisi_logs.jumlah_bayar) as total_omset'),
                DB::raw('SUM(tbl_mitra_komisi_logs.komisi_diterima) as total_komisi'),
                DB::raw('COUNT(tbl_mitra_komisi_logs.id) as total_transaksi')
            )
            ->groupBy(DB::raw('YEAR(tbl_mitra_komisi_logs.created_at)'))
            ->orderBy('tahun_log', 'desc');
        $applyAccessFilter($rekapTahunanQuery);
        $rekapTahunan = $rekapTahunanQuery->get();

        $payouts = DB::table('tbl_mitra_payouts')
            ->where('id_user', $mitraId)
            ->orderBy('tgl_payout', 'desc')
            ->get();

        return view('admin.mitra.laporan', compact(
            'targetUser', 
            'mitras', 
            'rekapBulanan', 
            'detailTransaksi', 
            'rekapTahunan', 
            'payouts',
            'tahun', 
            'bulan', 
            'isAdmin'
        ));
    }

    public function storePayout(Request $request)
    {
        $user = Auth::user();
        if ($user->level !== 'admin') {
            abort(403, 'Akses Ditolak');
        }

        $request->validate([
            'id_user' => 'required|integer',
            'payout_month_year' => 'required|string', // format: "M-Y"
            'jumlah' => 'required|numeric|min:1',
            'tgl_payout' => 'required|date',
            'catatan' => 'nullable|string',
            'bukti_transfer' => 'required|file|image|max:2048',
        ]);

        $parts = explode('-', $request->payout_month_year);
        if (count($parts) !== 2) {
            return redirect()->back()->withErrors(['error' => 'Format bulan/tahun tidak valid.']);
        }
        $month = (int)$parts[0];
        $year = (int)$parts[1];

        // Check if already paid
        $alreadyPaid = DB::table('tbl_mitra_payouts')
            ->where('id_user', $request->id_user)
            ->where('payout_month', $month)
            ->where('payout_year', $year)
            ->exists();

        if ($alreadyPaid) {
            return redirect()->back()->withErrors(['error' => 'Payout untuk bulan tersebut sudah pernah dibayarkan dan diblokir!']);
        }

        $filename = null;
        if ($request->hasFile('bukti_transfer')) {
            $file = $request->file('bukti_transfer');
            $destinationPath = public_path('uploads/mitra_payouts');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $filename);
        }

        DB::table('tbl_mitra_payouts')->insert([
            'id_user' => $request->id_user,
            'payout_month' => $month,
            'payout_year' => $year,
            'jumlah' => $request->jumlah,
            'tgl_payout' => $request->tgl_payout,
            'catatan' => $request->catatan,
            'bukti_transfer' => $filename,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send WhatsApp notification to Mitra
        $mitra = \App\Models\User::findOrFail($request->id_user);
        $waSent = false;
        
        if (!empty($mitra->phone_number)) {
            $namaBulan = \Carbon\Carbon::create()->month($month)->translatedFormat('F');
            $formatJumlah = number_format($request->jumlah, 0, ',', '.');
            $tglPayoutIndo = \Carbon\Carbon::parse($request->tgl_payout)->translatedFormat('d F Y');
            
            $pesan = "📢 *BUKTI PEMBAYARAN BAGI HASIL MITRA*\n\n"
                   . "Halo Bapak/Ibu *{$mitra->nama_user}*,\n\n"
                   . "Pembayaran payout komisi bagi hasil Anda telah berhasil dikirimkan oleh Admin. Berikut rinciannya:\n\n"
                   . "• *Mitra:* {$mitra->nama_user}\n"
                   . "• *Periode Payout:* {$namaBulan} {$year}\n"
                   . "• *Nominal:* Rp {$formatJumlah}\n"
                   . "• *Tanggal Payout:* {$tglPayoutIndo}\n"
                   . "• *Catatan:* " . ($request->catatan ?: '-') . "\n\n"
                   . "Silakan periksa saldo dan mutasi pada rekening terdaftar Anda. Terima kasih atas kerja samanya!";
            
            $waSent = $this->sendWhatsAppNotification($mitra->phone_number, $pesan);
        }

        $successMsg = 'Pembayaran Payout berhasil dicatat dan bukti transfer telah diupload!';
        if ($waSent) {
            $successMsg .= ' Notifikasi WhatsApp telah terkirim ke Mitra.';
        }

        return redirect()->route('admin.mitra.index')->with('success', $successMsg);
    }

    private function sendWhatsAppNotification($target, $message)
    {
        if (empty($target)) {
            return false;
        }

        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
        if (!$tokenInfo || empty($tokenInfo->token)) {
            return false;
        }

        $cleanTarget = preg_replace('/[^0-9]/', '', $target);
        if (str_starts_with($cleanTarget, '0')) {
            $cleanTarget = '62' . substr($cleanTarget, 1);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $tokenInfo->token
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $cleanTarget,
                'message' => $message,
                'countryCode' => '62'
            ]);

            $resData = $response->json();
            return ($response->successful() && isset($resData['status']) && $resData['status'] === true);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Fonnte API Notification Error: ' . $e->getMessage());
            return false;
        }
    }
}

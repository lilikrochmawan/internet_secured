<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminPromoController extends Controller
{
    public function index(Request $request)
    {
        $query = Promo::with(['pelanggan', 'paket']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_promo', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', function($pq) use ($search) {
                      $pq->where('nama_pelanggan', 'like', "%{$search}%")
                         ->orWhere('kode_pelanggan', 'like', "%{$search}%");
                  });
            });
        }

        $promos = $query->paginate(10);

        return view('admin.promo.index', compact('promos'));
    }

    public function create()
    {
        $pelanggan = Pelanggan::with('paketDetail')
            ->allowedForUser()
            ->orderBy('nama_pelanggan', 'asc')
            ->get();

        return view('admin.promo.create', compact('pelanggan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_promo' => 'required|string|max:150',
            'id_pelanggan' => 'required|integer',
            'mulai_bulan' => 'required|integer|between:1,12',
            'mulai_tahun' => 'required|integer',
            'selesai_bulan' => 'required|integer|between:1,12',
            'selesai_tahun' => 'required|integer',
            'nominal_tagihan' => 'required|numeric|min:0',
        ]);

        $pelanggan = Pelanggan::with('paketDetail')->findOrFail($request->id_pelanggan);

        // Determine starting period for first bill (current month, or next month if current is already paid)
        $currMonth = date('m');
        $currYear = date('Y');
        $currPeriod = $currMonth . $currYear;

        // Check if there is already a paid bill for this month
        $isPaidThisMonth = Tagihan::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('bulan_tahun', $currPeriod)
            ->where('status_bayar', 1)
            ->exists();

        if ($isPaidThisMonth) {
            // Target is next month
            $carbon = Carbon::now()->addMonth();
            $targetMonth = $carbon->format('m');
            $targetYear = $carbon->format('Y');
        } else {
            // Target is current month
            $targetMonth = $currMonth;
            $targetYear = $currYear;
        }

        $targetPeriod = $targetMonth . $targetYear;

        DB::beginTransaction();

        try {
            // Save Promo Record
            $promo = Promo::create([
                'nama_promo' => $request->nama_promo,
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'id_paket' => $pelanggan->paket,
                'mulai_bulan' => $request->mulai_bulan,
                'mulai_tahun' => $request->mulai_tahun,
                'selesai_bulan' => $request->selesai_bulan,
                'selesai_tahun' => $request->selesai_tahun,
                'nominal_tagihan' => $request->nominal_tagihan,
            ]);

            // Delete any existing unpaid invoice for the target period to prevent duplicates
            Tagihan::where('id_pelanggan', $pelanggan->id_pelanggan)
                ->where('bulan_tahun', $targetPeriod)
                ->where(function($q) {
                    $q->whereNull('status_bayar')->orWhere('status_promo', '!=', 1)->orWhere('status_bayar', '!=', 1);
                })
                ->delete();

            // Calculate due date (jatuh tempo)
            $settings = DB::table('tb_profile')->first();
            $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
            $default_hari = $settings->hari_jatuh_tempo ?? 10;
            $sistem = $settings->sistem_billing ?? 'prabayar';

            $target_date = Carbon::create((int)$targetYear, (int)$targetMonth, 1);
            if ($sistem === 'pascabayar') {
                $target_date->addMonth();
            }
            $due_year = $target_date->year;
            $due_month = $target_date->month;
            $due_day = $default_hari;

            if ($pelanggan->jatuh_tempo) {
                $due_day = (int) date('d', strtotime($pelanggan->jatuh_tempo));
            } elseif ($tipe === 'tanggal_pasang' && !empty($pelanggan->tgl_pemasangan)) {
                $due_day = (int) date('d', strtotime($pelanggan->tgl_pemasangan));
            }

            $days_in_month = (int) date('t', strtotime($due_year . '-' . sprintf('%02d', $due_month) . '-01'));
            if ($due_day > $days_in_month) {
                $due_day = $days_in_month;
            }

            $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $due_year, $due_month, $due_day);

            // Create Paid Tagihan
            $tagihan = Tagihan::create([
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'bulan_tahun' => $targetPeriod,
                'jml_bayar' => $request->nominal_tagihan,
                'terbayar' => $request->nominal_tagihan,
                'status_bayar' => 1, // Lunas
                'waktu_bayar' => Carbon::now()->format('Y-m-d H:i:s'),
                'user_id' => Auth::id(),
                'manual_invoice' => 1,
                'item_tagihan' => "Tagihan Awal Promo: " . $request->nama_promo,
                'jatuh_tempo' => $tgl_jatuh_tempo,
            ]);

            // Update customer's due date
            $pelanggan->update([
                'jatuh_tempo' => $tgl_jatuh_tempo
            ]);

            // Log in tb_kas
            $nama_paket = $pelanggan->paketDetail->nama_paket ?? '';
            $ketKas = "Pembayaran Promo: " . $request->nama_promo . " AN. " . $pelanggan->nama_pelanggan . ", Paket " . $nama_paket;
            DB::table('tb_kas')->insert([
                'tgl_kas' => date('Y-m-d'),
                'keterangan' => $ketKas,
                'penerimaan' => $request->nominal_tagihan,
                'id_tagihan' => $tagihan->id_tagihan
            ]);

            // Unblock Mikrotik if blocked
            try {
                $user = User::where('id_pelanggan', $pelanggan->id_pelanggan)->first();
                $checkUser = DB::table('tbl_penggunamikrotik')->first();
                $id_mikrotik = $pelanggan->id_mikrotik ?: 1;
                $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();

                $ip_address = ($checkUser && ($checkUser->status == 'ya' || $checkUser->ippelanggan == 'dynamic'))
                    ? ($user ? $user->username : null)
                    : $pelanggan->ip_address;

                if ($mikrotik && $ip_address) {
                    require_once base_path('include/routeros_api.php');
                    $API = new \RouterosAPI();
                    $API->timeout = 2;
                    $API->attempts = 1;
                    $API->delay = 1;

                    if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                        if ($checkUser && $checkUser->ippelanggan == 'statik') {
                            $commentToSearch = "Blokir Bulanan " . $ip_address;
                            $API->write('/ip/firewall/address-list/print', false);
                            $API->write('?comment=' . $commentToSearch);
                            $ips = $API->read();

                            if (!empty($ips)) {
                                foreach ($ips as $ip_data) {
                                    $API->write('/ip/firewall/address-list/remove', false);
                                    $API->write('=.id=' . $ip_data['.id']);
                                    $API->read();
                                }
                            }
                        } else {
                            if ($user) {
                                $paket = DB::table('tb_paket')->where('id_paket', $pelanggan->paket)->first();
                                $profile = $paket ? $paket->id_pmikrotik : 'default';

                                $secrets = $API->comm('/ppp/secret/print', [
                                    '?name' => $ip_address,
                                ]);

                                $isCurrentlyBlocked = false;
                                if (!empty($secrets)) {
                                    $secret = $secrets[0];
                                    $currentProfile = $secret['profile'] ?? '';
                                    $isDisabled = ($secret['disabled'] ?? 'false') === 'true';
                                    if ($currentProfile === 'pppoe-isolir' || $isDisabled) {
                                        $isCurrentlyBlocked = true;
                                    }
                                }

                                if ($isCurrentlyBlocked) {
                                    $API->comm('/ppp/secret/set', [
                                        'numbers' => $ip_address,
                                        'profile' => $profile
                                    ]);
                                    $API->comm('/ppp/secret/enable', ['numbers' => $ip_address]);

                                    $activeConnections = $API->comm('/ppp/active/print', [
                                        '?name' => $ip_address,
                                    ]);
                                    foreach ($activeConnections as $conn) {
                                        $API->comm('/ppp/active/remove', [
                                            '.id' => $conn['.id'],
                                        ]);
                                    }
                                }
                            }
                        }
                        $API->disconnect();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Promo Activation Mikrotik Unblock Error: ' . $e->getMessage());
            }

            // Send Custom Promo WhatsApp Message
            try {
                $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
                $notifPromo = DB::table('tbl_notifpromo')->where('status_promo', 'aktif')->first();

                if ($tokenInfo && !empty($tokenInfo->token) && $notifPromo && !empty($notifPromo->pesan_promo) && !empty($pelanggan->no_telp)) {
                    $pesan = $notifPromo->pesan_promo;
                    $pesan = str_replace('$nama', $pelanggan->nama_pelanggan, $pesan);
                    $pesan = str_replace('$no_telp', $pelanggan->no_telp, $pesan);
                    $pesan = str_replace('$nama_promo', $request->nama_promo, $pesan);
                    $pesan = str_replace('$tagihan', number_format($request->nominal_tagihan, 0, ',', '.'), $pesan);

                    $mulaiFormat = Carbon::create((int)$request->mulai_tahun, (int)$request->mulai_bulan, 1)->translatedFormat('F Y');
                    $selesaiFormat = Carbon::create((int)$request->selesai_tahun, (int)$request->selesai_bulan, 1)->translatedFormat('F Y');

                    $pesan = str_replace('$mulai_promo', $mulaiFormat, $pesan);
                    $pesan = str_replace('$selesai_promo', $selesaiFormat, $pesan);

                    Http::timeout(10)->withHeaders([
                        'Authorization' => $tokenInfo->token
                    ])->asForm()->post('https://api.fonnte.com/send', [
                        'target' => $pelanggan->no_telp,
                        'message' => $pesan,
                        'countryCode' => '62'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Promo WhatsApp Notification Error: ' . $e->getMessage());
            }

            DB::commit();

            Log::info("Staff [" . auth()->user()->nama_user . "] (level: " . auth()->user()->level . ") MENAMBAH PROMO [" . $request->nama_promo . "] untuk pelanggan [" . $pelanggan->nama_pelanggan . "] dengan tagihan awal Rp " . number_format($request->nominal_tagihan, 0, ',', '.') . ".");

            return redirect()->route('admin.promo.index')->with('success', 'Promo berhasil disimpan dan WhatsApp notifikasi telah dikirim.');

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Promo Store Error: ' . $ex->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan promo: ' . $ex->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        $namaPromo = $promo->nama_promo;
        $pelanggan = Pelanggan::find($promo->id_pelanggan);
        $namaPelanggan = $pelanggan ? $pelanggan->nama_pelanggan : 'Unknown';

        $promo->delete();

        Log::info("Staff [" . auth()->user()->nama_user . "] (level: " . auth()->user()->level . ") MENGHAPUS PROMO [" . $namaPromo . "] pelanggan [" . $namaPelanggan . "].");

        return redirect()->route('admin.promo.index')->with('success', 'Promo berhasil dihapus.');
    }
}

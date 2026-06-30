<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AdminTransaksiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status'); // 'lunas', 'belum_bayar'
        $selectedMonth = $request->get('bulan');
        $selectedYear = $request->get('tahun');

        $query = Tagihan::with(['pelanggan'])->whereIn('id_pelanggan', Pelanggan::allowedForUser()->pluck('id_pelanggan'));

        if (!empty($selectedMonth) && !empty($selectedYear)) {
            $bulantahun = $selectedMonth . $selectedYear;
            $query->where('bulan_tahun', $bulantahun);
        } else {
            $currentMonth = date('mY');

            $query->where(function ($q) use ($currentMonth) {
                // 1. Regular bills of this month (both paid and unpaid)
                $q->where(function ($sub) use ($currentMonth) {
                    $sub->where('bulan_tahun', $currentMonth)
                        ->where(function ($inner) {
                            $inner->where('manual_invoice', 0)
                                  ->orWhereNull('manual_invoice');
                        });
                })
                // 2. Unpaid manual invoices of any period (including this month and older)
                ->orWhere(function ($sub) {
                    $sub->where('manual_invoice', 1)
                        ->where(function ($inner) {
                            $inner->whereNull('status_bayar')
                                  ->orWhereIn('status_bayar', [0, '0', 'belum', '']);
                        });
                });
            });
        }

        if (!empty($search)) {
            $query->whereHas('pelanggan', function ($q) use ($search) {
                $q->where('nama_pelanggan', 'like', '%' . $search . '%')
                  ->orWhere('kode_pelanggan', 'like', '%' . $search . '%');
            });
        }

        if ($status === 'lunas') {
            $query->where('status_bayar', 1);
        } elseif ($status === 'belum_bayar') {
            $query->where(function ($q) {
                $q->whereNull('status_bayar')
                  ->orWhereIn('status_bayar', [0, '0', 'belum', '']);
            });
        }

        $tagihan = $query->orderByRaw('CASE WHEN status_bayar = 1 THEN 1 ELSE 0 END ASC')
            ->orderBy('id_tagihan', 'desc')
            ->get();
        $pelanggan = Pelanggan::with(['paketDetail'])->allowedForUser()->orderBy('nama_pelanggan')->get();

        return view('admin.transaksi.index', compact('tagihan', 'search', 'status', 'pelanggan', 'selectedMonth', 'selectedYear'));
    }

    public function pembayaranJson(Request $request)
    {
        $startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d H:i:s');
        $endDate = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
        $search = $request->get('search');

        $allowedPelangganIds = Pelanggan::allowedForUser()->pluck('id_pelanggan');

        $query = Tagihan::with(['pelanggan', 'penerima'])
            ->whereIn('id_pelanggan', $allowedPelangganIds)
            ->where('status_bayar', 1)
            ->whereBetween('waktu_bayar', [$startDate, $endDate]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('pelanggan', function ($sub) use ($search) {
                    $sub->where('nama_pelanggan', 'like', '%' . $search . '%')
                        ->orWhere('kode_pelanggan', 'like', '%' . $search . '%');
                })
                ->orWhereHas('penerima', function ($sub) use ($search) {
                    $sub->where('nama_user', 'like', '%' . $search . '%');
                });
            });
        }

        $transactions = $query->orderBy('waktu_bayar', 'desc')
            ->paginate(10);

        return response()->json($transactions);
    }

    public function bayar(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|integer',
        ]);

        $tagihan = Tagihan::findOrFail($request->id_tagihan);
        
        if ($tagihan->status_bayar == 1) {
            return redirect()->route('admin.transaksi.index')->with('info', 'Tagihan sudah lunas.');
        }

        $pelanggan = Pelanggan::find($tagihan->id_pelanggan);

        // Catat pembayaran manual oleh staff
        $tagihan->update([
            'status_bayar' => 1,
            'waktu_bayar' => Carbon::now()->format('Y-m-d H:i:s'),
            'user_id' => Auth::id(), // ID staff yang menerima pembayaran
            'terbayar' => $tagihan->jml_bayar, // Set terbayar to jml_bayar
            'blokir_status' => null, // Bersihkan status blokir karena sudah dibayar
        ]);

        if ($pelanggan) {
            $tanggal_sekarang = date('Y-m-d');
            $jatuh_tempo = $pelanggan->jatuh_tempo;

            if ($jatuh_tempo && $tanggal_sekarang < date('Y-m-d', strtotime($jatuh_tempo))) {
                $jatuh_tempo_obj = new \DateTime($jatuh_tempo);
            } else {
                $jatuh_tempo_obj = new \DateTime($tanggal_sekarang);
            }
            $jatuh_tempo_obj->modify('+1 Month');
            $next_year = (int)$jatuh_tempo_obj->format('Y');
            $next_month = (int)$jatuh_tempo_obj->format('m');

            // Ambil pengaturan jatuh tempo global
            $settings = DB::table('tb_profile')->first();
            $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
            $default_hari = $settings->hari_jatuh_tempo ?? 10;

            $due_day = $default_hari;
            if ($pelanggan->jatuh_tempo) {
                $due_day = (int) date('d', strtotime($pelanggan->jatuh_tempo));
            } elseif ($tipe === 'tanggal_pasang' && !empty($pelanggan->tgl_pemasangan)) {
                $due_day = (int) date('d', strtotime($pelanggan->tgl_pemasangan));
            }

            // Cari jumlah hari maksimum di bulan target
            $days_in_month = (int) date('t', strtotime($next_year . '-' . sprintf('%02d', $next_month) . '-01'));
            if ($due_day > $days_in_month) {
                $due_day = $days_in_month;
            }

            $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $next_year, $next_month, $due_day);

            // Update jatuh tempo pelanggan
            $pelanggan->update([
                'jatuh_tempo' => $tgl_jatuh_tempo
            ]);

            // Otomatis unblock Mikrotik jika tagihan terbaru lunas
            $latestBill = DB::table('tb_tagihan')
                ->where('id_pelanggan', $pelanggan->id_pelanggan)
                ->orderBy('id_tagihan', 'desc')
                ->first();

            if ($latestBill && $latestBill->status_bayar == 1) {
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

                                    $API->comm('/ppp/secret/set', [
                                        'numbers' => $ip_address,
                                        'profile' => $profile
                                    ]);
                                    $API->comm('/ppp/secret/enable', ['numbers' => $ip_address]);

                                    // Putuskan koneksi aktif agar langsung dial ulang dengan profile baru
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
                            $API->disconnect();
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Manual Payment Mikrotik Unblock Error: ' . $e->getMessage());
                }
            }
            
            // Catat di tb_kas jika belum ada
            $existsInKas = DB::table('tb_kas')->where('id_tagihan', $tagihan->id_tagihan)->exists();
            if (!$existsInKas) {
                $paket = DB::table('tb_paket')->where('id_paket', $pelanggan->paket)->first();
                $nama_paket = $paket ? $paket->nama_paket : '';
                $ket = "Pembayaran Internet AN. " . $pelanggan->nama_pelanggan . ", Paket " . $nama_paket;
                DB::table('tb_kas')->insert([
                    'tgl_kas' => date('Y-m-d'),
                    'keterangan' => $ket,
                    'penerimaan' => $tagihan->jml_bayar,
                    'id_tagihan' => $tagihan->id_tagihan
                ]);
            }
        }

        return redirect()->route('admin.transaksi.index')->with('success', 'Pembayaran tagihan berhasil dicatat!');
    }

    public function batal(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|integer',
        ]);

        $tagihan = Tagihan::findOrFail($request->id_tagihan);
        
        if ($tagihan->status_bayar != 1) {
            return redirect()->route('admin.transaksi.index')->with('info', 'Tagihan belum lunas.');
        }

        $pelanggan = Pelanggan::find($tagihan->id_pelanggan);

        // Batalkan pembayaran
        $tagihan->update([
            'status_bayar' => null,
            'waktu_bayar' => null,
            'user_id' => null,
            'terbayar' => null,
        ]);

        if ($pelanggan && $pelanggan->jatuh_tempo) {
            $jatuh_tempo_obj = new \DateTime($pelanggan->jatuh_tempo);
            $jatuh_tempo_obj->modify('-1 Month');
            $prev_year = (int)$jatuh_tempo_obj->format('Y');
            $prev_month = (int)$jatuh_tempo_obj->format('m');

            // Ambil pengaturan jatuh tempo global
            $settings = DB::table('tb_profile')->first();
            $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
            $default_hari = $settings->hari_jatuh_tempo ?? 10;

            $due_day = $default_hari;
            if ($pelanggan->jatuh_tempo) {
                $due_day = (int) date('d', strtotime($pelanggan->jatuh_tempo));
            } elseif ($tipe === 'tanggal_pasang' && !empty($pelanggan->tgl_pemasangan)) {
                $due_day = (int) date('d', strtotime($pelanggan->tgl_pemasangan));
            }

            // Cari jumlah hari maksimum di bulan target
            $days_in_month = (int) date('t', strtotime($prev_year . '-' . sprintf('%02d', $prev_month) . '-01'));
            if ($due_day > $days_in_month) {
                $due_day = $days_in_month;
            }

            $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $prev_year, $prev_month, $due_day);

            // Update jatuh tempo pelanggan
            $pelanggan->update([
                'jatuh_tempo' => $tgl_jatuh_tempo
            ]);
        }

        // Hapus dari tb_kas
        DB::table('tb_kas')->where('id_tagihan', $tagihan->id_tagihan)->delete();

        return redirect()->route('admin.transaksi.index')->with('success', 'Pembayaran tagihan berhasil dibatalkan!');
    }

    public function blokir(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|integer',
        ]);

        $tagihan = Tagihan::findOrFail($request->id_tagihan);
        $pelanggan = Pelanggan::find($tagihan->id_pelanggan);
        if (!$pelanggan) {
            return back()->withErrors(['error' => 'Data pelanggan tidak ditemukan.']);
        }

        $user = User::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

        $checkUser = DB::table('tbl_penggunamikrotik')->first();
        $id_mikrotik = $pelanggan->id_mikrotik ?: 1;

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();
        if (!$mikrotik) {
            return back()->withErrors(['error' => 'Koneksi router Mikrotik tidak ditemukan.']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            if ($checkUser && $checkUser->ippelanggan == 'statik') {
                // Blokir Statik: Tambah IP ke Address-List
                $API->comm("/ip/firewall/address-list/add", [
                    "list"     => "blocked_clients",
                    "address"  => $pelanggan->ip_address,
                    "comment"  => "Blokir Bulanan " . $pelanggan->ip_address
                ]);
            } else {
                // Blokir PPPOE: Disable Secret & Disconnect Active Connection
                if ($user) {
                    $API->comm("/ppp/secret/set", [
                        "numbers" => $user->username,
                        "profile" => "pppoe-isolir",
                    ]);
                    $API->comm("/ppp/secret/enable", [
                        "numbers" => $user->username,
                    ]);

                    // Cari koneksi pppoe aktif
                    $activeConnections = $API->comm("/ppp/active/print", [
                        "?name" => $user->username,
                    ]);

                    foreach ($activeConnections as $conn) {
                        $API->comm("/ppp/active/remove", [
                            ".id" => $conn['.id'],
                        ]);
                    }
                }
            }
            $API->disconnect();
            
            // Simpan status blokir di database
            $tagihan->update(['blokir_status' => 1]);

            return redirect()->route('admin.transaksi.index')->with('success', 'Pelanggan berhasil diblokir di Mikrotik!');
        }

        return back()->withErrors(['error' => 'Gagal terhubung ke Router Mikrotik. Harap periksa jaringan router Anda.']);
    }

    public function unblokir(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|integer',
        ]);

        $tagihan = Tagihan::findOrFail($request->id_tagihan);
        $pelanggan = Pelanggan::find($tagihan->id_pelanggan);
        if (!$pelanggan) {
            return back()->withErrors(['error' => 'Data pelanggan tidak ditemukan.']);
        }

        $user = User::where('id_pelanggan', $pelanggan->id_pelanggan)->first();

        $checkUser = DB::table('tbl_penggunamikrotik')->first();
        $id_mikrotik = $pelanggan->id_mikrotik ?: 1;

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();
        if (!$mikrotik) {
            return back()->withErrors(['error' => 'Koneksi router Mikrotik tidak ditemukan.']);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            if ($checkUser && $checkUser->ippelanggan == 'statik') {
                // Buka Blokir Statik: Hapus IP dari firewall Address-List
                $commentToSearch = "Blokir Bulanan " . $pelanggan->ip_address;
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
                // Buka Blokir PPPOE: Restore Profile & Enable Secret
                if ($user) {
                    $paket = DB::table('tb_paket')->where('id_paket', $pelanggan->paket)->first();
                    $profile = $paket ? $paket->id_pmikrotik : 'default';

                    $API->comm("/ppp/secret/set", [
                        "numbers" => $user->username,
                        "profile" => $profile,
                    ]);
                    $API->comm("/ppp/secret/enable", [
                        "numbers" => $user->username,
                    ]);

                    // Putuskan koneksi aktif agar langsung dial ulang dengan profile baru
                    $activeConnections = $API->comm("/ppp/active/print", [
                        "?name" => $user->username,
                    ]);
                    foreach ($activeConnections as $conn) {
                        $API->comm("/ppp/active/remove", [
                            ".id" => $conn['.id'],
                        ]);
                    }
                }
            }
            $API->disconnect();
            
            // Update status blokir di database
            $tagihan->update(['blokir_status' => null]);

            return redirect()->route('admin.transaksi.index')->with('success', 'Blokir pelanggan berhasil dibuka di Mikrotik!');
        }

        return back()->withErrors(['error' => 'Gagal terhubung ke Router Mikrotik. Harap periksa jaringan router Anda.']);
    }

    public function sendNotif(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|integer',
        ]);

        $tagihan = Tagihan::findOrFail($request->id_tagihan);
        $pelanggan = Pelanggan::find($tagihan->id_pelanggan);
        if (!$pelanggan) {
            return back()->withErrors(['error' => 'Data pelanggan tidak ditemukan.']);
        }

        $notifSetting = DB::table('tbl_notif')->first();
        if (!$notifSetting || $notifSetting->status_notifikasi !== 'aktif') {
            return back()->withErrors(['error' => 'Fitur notifikasi WhatsApp belum diaktifkan di pengaturan.']);
        }

        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        if (!$tokenInfo || empty($tokenInfo->token)) {
            return back()->withErrors(['error' => 'Token WhatsApp Fonnte belum dikonfigurasi.']);
        }

        // Siapkan pesan notifikasi
        $pesan = $notifSetting->pesan_notifikasi;
        $pesan = str_replace('$nama', $pelanggan->nama_pelanggan, $pesan);
        $pesan = str_replace('$no_telp', $pelanggan->no_telp, $pesan);
        $pesan = str_replace('$jatuh_tempo', \Carbon\Carbon::parse($tagihan->jatuh_tempo ?? $pelanggan->jatuh_tempo)->translatedFormat('d F Y'), $pesan);
        $pesan = str_replace('$tagihan', number_format($tagihan->jml_bayar, 0, ',', '.'), $pesan);
        $pesan = str_replace('$hari_ini', \Carbon\Carbon::now()->translatedFormat('d F Y'), $pesan);

        // Kirim via Fonnte API
        $response = Http::withHeaders([
            'Authorization' => $tokenInfo->token
        ])->asForm()->post('https://api.fonnte.com/send', [
            'target' => $pelanggan->no_telp,
            'message' => $pesan,
            'countryCode' => '62'
        ]);

        $resData = $response->json();
        if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
            $tagihan->update(['terkirim' => 'terkirim']);
            return redirect()->route('admin.transaksi.index')->with('success', 'Notifikasi penagihan WhatsApp berhasil dikirim ke ' . $pelanggan->nama_pelanggan . '!');
        }

        $reason = $resData['reason'] ?? $resData['message'] ?? 'Device Fonnte tidak terhubung atau token tidak valid.';
        return back()->withErrors(['error' => 'Gagal mengirim pesan WhatsApp. Fonnte Response: ' . $reason]);
    }

    public function showGenerate(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $bulantahun = $bulan . $tahun;

        // Cek apakah PPN aktif
        $ppn_aktif = false;
        $paketSettings = DB::table('tbl_paketmikrotik')->first();
        if ($paketSettings && isset($paketSettings->ppn) && $paketSettings->ppn === 'aktif') {
            $ppn_aktif = true;
        }

        // Cari pelanggan yang belum memiliki tagihan pada bulan & tahun tersebut
        $pelanggan = Pelanggan::with('paketDetail')
            ->allowedForUser()
            ->whereNotExists(function ($query) use ($bulantahun) {
                $query->select(DB::raw(1))
                    ->from('tb_tagihan')
                    ->whereColumn('tb_tagihan.id_pelanggan', 'tb_pelanggan.id_pelanggan')
                    ->where('tb_tagihan.bulan_tahun', $bulantahun);
            })
            ->get();

        return view('admin.transaksi.generate', compact('pelanggan', 'bulan', 'tahun', 'bulantahun', 'ppn_aktif'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|array',
            'bulan2' => 'required|array',
            'harga' => 'required|array',
        ]);

        $id_pelanggan = $request->id_pelanggan;
        $bulan2 = $request->bulan2;
        $harga = $request->harga;
        $count = count($harga);
        $generated = 0;

        // Ambil pengaturan jatuh tempo global
        $settings = DB::table('tb_profile')->first();
        $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
        $default_hari = $settings->hari_jatuh_tempo ?? 10;
        $sistem = $settings->sistem_billing ?? 'prabayar';

        for ($i = 0; $i < $count; $i++) {
            $harga_oke = str_replace('.', '', $harga[$i]);
            
            // Check if exists
            $check = DB::table('tb_tagihan')
                ->where('id_pelanggan', $id_pelanggan[$i])
                ->where('bulan_tahun', $bulan2[$i])
                ->first();

            if (!$check) {
                $pelanggan_data = DB::table('tb_pelanggan')->where('id_pelanggan', $id_pelanggan[$i])->first();
                $target_year = substr($bulan2[$i], 2, 4);
                $target_month = substr($bulan2[$i], 0, 2);

                // Buat Carbon instance untuk bulan/tahun tagihan dasar
                $target_date = \Carbon\Carbon::create((int)$target_year, (int)$target_month, 1);
                
                // Jika pascabayar, jatuh tempo adalah di bulan selanjutnya (+1 bulan)
                if ($sistem === 'pascabayar') {
                    $target_date->addMonth();
                }

                $due_year = $target_date->year;
                $due_month = $target_date->month;

                $due_day = $default_hari;
                if ($pelanggan_data && !empty($pelanggan_data->jatuh_tempo)) {
                    $due_day = (int) date('d', strtotime($pelanggan_data->jatuh_tempo));
                } elseif ($tipe === 'tanggal_pasang' && $pelanggan_data && !empty($pelanggan_data->tgl_pemasangan)) {
                    $due_day = (int) date('d', strtotime($pelanggan_data->tgl_pemasangan));
                }

                // Cari jumlah hari maksimum di bulan target
                $days_in_month = (int) date('t', strtotime($due_year . '-' . sprintf('%02d', $due_month) . '-01'));
                if ($due_day > $days_in_month) {
                    $due_day = $days_in_month;
                }

                $due_day_str = str_pad($due_day, 2, '0', STR_PAD_LEFT);
                $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $due_year, $due_month, $due_day);

                // Buat tagihan baru
                DB::table('tb_tagihan')->insert([
                    'id_pelanggan' => $id_pelanggan[$i],
                    'bulan_tahun' => $bulan2[$i],
                    'jml_bayar' => $harga_oke,
                    'terbayar' => null,
                    'status_bayar' => null,
                    'manual_invoice' => 0,
                    'jatuh_tempo' => $tgl_jatuh_tempo
                ]);

                // Update tanggal jatuh tempo pelanggan
                DB::table('tb_pelanggan')
                    ->where('id_pelanggan', $id_pelanggan[$i])
                    ->update(['jatuh_tempo' => $tgl_jatuh_tempo]);

                $generated++;
            }
        }

        return redirect()->route('admin.transaksi.index')->with('success', $generated . ' tagihan baru berhasil digenerate untuk periode tersebut.');
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|integer',
            'bulan' => 'required|string|size:2',
            'tahun' => 'required|string|size:4',
            'jml_bayar' => 'required|numeric',
            'item_tagihan' => 'nullable|string',
            'jatuh_tempo' => 'required|date',
        ]);

        $bulantahun = $request->bulan . $request->tahun;

        // Check if identical manual invoice already exists to prevent duplicate submissions
        $check = Tagihan::where('id_pelanggan', $request->id_pelanggan)
            ->where('bulan_tahun', $bulantahun)
            ->where('jml_bayar', $request->jml_bayar)
            ->where('item_tagihan', $request->item_tagihan)
            ->where('manual_invoice', 1)
            ->first();

        if ($check) {
            return back()->withErrors(['error' => 'Invoice manual yang identik untuk periode tersebut sudah ada.']);
        }

        Tagihan::create([
            'id_pelanggan' => $request->id_pelanggan,
            'bulan_tahun' => $bulantahun,
            'jml_bayar' => $request->jml_bayar,
            'status_bayar' => null,
            'manual_invoice' => 1,
            'item_tagihan' => $request->item_tagihan,
            'jatuh_tempo' => $request->jatuh_tempo . ' 23:59:00',
        ]);

        return redirect()->route('admin.transaksi.index')->with('success', 'Invoice manual berhasil dibuat!');
    }

    public function sendReminder(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|integer',
        ]);

        $tagihan = Tagihan::findOrFail($request->id_tagihan);
        $pelanggan = Pelanggan::find($tagihan->id_pelanggan);
        if (!$pelanggan) {
            return back()->withErrors(['error' => 'Data pelanggan tidak ditemukan.']);
        }

        $reminderSetting = DB::table('tbl_notifreminder')->first();
        if (!$reminderSetting || $reminderSetting->status_reminder !== 'aktif') {
            return back()->withErrors(['error' => 'Fitur reminder tagihan belum diaktifkan di pengaturan custom pesan.']);
        }

        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        if (!$tokenInfo || empty($tokenInfo->token)) {
            return back()->withErrors(['error' => 'Token WhatsApp Fonnte belum dikonfigurasi.']);
        }

        // Siapkan pesan reminder menggunakan format reminder (pesan_reminder)
        $pesan = $reminderSetting->pesan_reminder;
        $pesan = str_replace('$nama', $pelanggan->nama_pelanggan, $pesan);
        $pesan = str_replace('$no_telp', $pelanggan->no_telp, $pesan);
        $pesan = str_replace('$tagihan', number_format($tagihan->jml_bayar, 0, ',', '.'), $pesan);
        $pesan = str_replace('$jatuh_tempo', Carbon::parse($tagihan->jatuh_tempo)->translatedFormat('d F Y') ?? $pelanggan->jatuh_tempo, $pesan);
        $pesan = str_replace('$sekarang_format', Carbon::now()->translatedFormat('d F Y H:i') . ' WIB', $pesan);

        // Kirim via Fonnte API
        $response = Http::withHeaders([
            'Authorization' => $tokenInfo->token
        ])->asForm()->post('https://api.fonnte.com/send', [
            'target' => $pelanggan->no_telp,
            'message' => $pesan,
            'countryCode' => '62'
        ]);

        $resData = $response->json();
        if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
            return redirect()->route('admin.transaksi.index')->with('success', 'Reminder penagihan WhatsApp berhasil dikirim ke ' . $pelanggan->nama_pelanggan . '!');
        }

        $reason = $resData['reason'] ?? $resData['message'] ?? 'Device Fonnte tidak terhubung atau token tidak valid.';
        return back()->withErrors(['error' => 'Gagal mengirim reminder WhatsApp. Fonnte Response: ' . $reason]);
    }

    public function broadcast(Request $request)
    {
        set_time_limit(1800); // Jeda 8 detik per pengiriman membutuhkan waktu eksekusi yang lebih lama

        // Ambil semua tagihan yang belum lunas (scoped by branch access)
        $tagihanUnpaid = Tagihan::with('pelanggan')
            ->whereNull('status_bayar')
            ->whereIn('id_pelanggan', Pelanggan::allowedForUser()->pluck('id_pelanggan'))
            ->get();
        
        if ($tagihanUnpaid->isEmpty()) {
            return response()->json([
                'success' => true,
                'results' => [],
                'berhasil' => 0,
                'gagal' => 0,
                'message' => 'Tidak ada tagihan belum lunas untuk dibroadcast.'
            ]);
        }

        $notifSetting = DB::table('tbl_notif')->first();
        if (!$notifSetting || $notifSetting->status_notifikasi !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Fitur notifikasi WhatsApp belum diaktifkan di pengaturan custom pesan.'
            ], 400);
        }

        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        if (!$tokenInfo || empty($tokenInfo->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token WhatsApp Fonnte belum dikonfigurasi.'
            ], 400);
        }

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($tagihanUnpaid as $index => $tx) {
            $pelanggan = $tx->pelanggan;
            if (!$pelanggan || empty($pelanggan->no_telp)) {
                $results[] = [
                    'status' => false,
                    'nama' => $pelanggan->nama_pelanggan ?? 'N/A',
                    'no_telp' => $pelanggan->no_telp ?? '-',
                    'message' => 'Nomor HP tidak valid/kosong.'
                ];
                $failCount++;
                continue;
            }

            // Jeda 10 detik untuk mencegah rate limit/spam block (kecuali indeks pertama)
            if ($index > 0) {
                sleep(10);
            }

            $pesan = $notifSetting->pesan_notifikasi;
            $pesan = str_replace('$nama', $pelanggan->nama_pelanggan, $pesan);
            $pesan = str_replace('$no_telp', $pelanggan->no_telp, $pesan);
            $pesan = str_replace('$jatuh_tempo', Carbon::parse($tx->jatuh_tempo)->translatedFormat('d F Y') ?? $pelanggan->jatuh_tempo, $pesan);
            $pesan = str_replace('$tagihan', number_format($tx->jml_bayar, 0, ',', '.'), $pesan);
            $pesan = str_replace('$hari_ini', Carbon::now()->translatedFormat('d F Y'), $pesan);

            try {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => $tokenInfo->token
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $pelanggan->no_telp,
                    'message' => $pesan,
                    'countryCode' => '62'
                ]);

                $resData = $response->json();
                if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                    $tx->update(['terkirim' => 'terkirim']);
                    $successCount++;
                    $results[] = [
                        'status' => true,
                        'nama' => $pelanggan->nama_pelanggan,
                        'no_telp' => $pelanggan->no_telp,
                        'message' => 'Terkirim'
                    ];
                } else {
                    $failCount++;
                    $reason = $resData['reason'] ?? $resData['message'] ?? 'Gagal mengirim (HTTP ' . $response->status() . ')';
                    $results[] = [
                        'status' => false,
                        'nama' => $pelanggan->nama_pelanggan,
                        'no_telp' => $pelanggan->no_telp,
                        'message' => $reason
                    ];
                }
            } catch (\Exception $e) {
                $failCount++;
                $results[] = [
                    'status' => false,
                    'nama' => $pelanggan->nama_pelanggan,
                    'no_telp' => $pelanggan->no_telp,
                    'message' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'berhasil' => $successCount,
            'gagal' => $failCount
        ]);
    }

    public function reminder(Request $request)
    {
        set_time_limit(1800); // Jeda 8 detik per pengiriman membutuhkan waktu eksekusi yang lebih lama

        // Ambil semua tagihan yang belum lunas (scoped by branch access)
        $tagihanUnpaid = Tagihan::with('pelanggan')
            ->whereNull('status_bayar')
            ->whereIn('id_pelanggan', Pelanggan::allowedForUser()->pluck('id_pelanggan'))
            ->get();
        
        if ($tagihanUnpaid->isEmpty()) {
            return response()->json([
                'success' => true,
                'results' => [],
                'berhasil' => 0,
                'gagal' => 0,
                'message' => 'Tidak ada tagihan belum lunas untuk direminder.'
            ]);
        }

        $reminderSetting = DB::table('tbl_notifreminder')->first();
        if (!$reminderSetting || $reminderSetting->status_reminder !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Fitur reminder tagihan belum diaktifkan di pengaturan custom pesan.'
            ], 400);
        }

        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        if (!$tokenInfo || empty($tokenInfo->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token WhatsApp Fonnte belum dikonfigurasi.'
            ], 400);
        }

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($tagihanUnpaid as $index => $tx) {
            $pelanggan = $tx->pelanggan;
            if (!$pelanggan || empty($pelanggan->no_telp)) {
                $results[] = [
                    'status' => false,
                    'nama' => $pelanggan->nama_pelanggan ?? 'N/A',
                    'no_telp' => $pelanggan->no_telp ?? '-',
                    'message' => 'Nomor HP tidak valid/kosong.'
                ];
                $failCount++;
                continue;
            }

            // Jeda 10 detik untuk mencegah rate limit/spam block (kecuali indeks pertama)
            if ($index > 0) {
                sleep(10);
            }

            $pesan = $reminderSetting->pesan_reminder;
            $pesan = str_replace('$nama', $pelanggan->nama_pelanggan, $pesan);
            $pesan = str_replace('$no_telp', $pelanggan->no_telp, $pesan);
            $pesan = str_replace('$tagihan', number_format($tx->jml_bayar, 0, ',', '.'), $pesan);
            $pesan = str_replace('$jatuh_tempo', Carbon::parse($tx->jatuh_tempo)->translatedFormat('d F Y') ?? $pelanggan->jatuh_tempo, $pesan);
            $pesan = str_replace('$sekarang_format', Carbon::now()->translatedFormat('d F Y H:i') . ' WIB', $pesan);

            try {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => $tokenInfo->token
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $pelanggan->no_telp,
                    'message' => $pesan,
                    'countryCode' => '62'
                ]);

                $resData = $response->json();
                if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                    $successCount++;
                    $results[] = [
                        'status' => true,
                        'nama' => $pelanggan->nama_pelanggan,
                        'no_telp' => $pelanggan->no_telp,
                        'message' => 'Terkirim'
                    ];
                } else {
                    $failCount++;
                    $reason = $resData['reason'] ?? $resData['message'] ?? 'Gagal mengirim (HTTP ' . $response->status() . ')';
                    $results[] = [
                        'status' => false,
                        'nama' => $pelanggan->nama_pelanggan,
                        'no_telp' => $pelanggan->no_telp,
                        'message' => $reason
                    ];
                }
            } catch (\Exception $e) {
                $failCount++;
                $results[] = [
                    'status' => false,
                    'nama' => $pelanggan->nama_pelanggan,
                    'no_telp' => $pelanggan->no_telp,
                    'message' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'berhasil' => $successCount,
            'gagal' => $failCount
        ]);
    }

    public function bulkBlokir(Request $request)
    {
        set_time_limit(1800); // 30 minutes execution limit

        // Ambil semua tagihan yang belum lunas bulan ini dan belum diblokir
        $tagihanUnpaid = Tagihan::with(['pelanggan'])
            ->where('bulan_tahun', date('mY'))
            ->where(function ($q) {
                $q->whereNull('status_bayar')
                  ->orWhereIn('status_bayar', [0, '0', 'belum', '']);
            })
            ->where(function ($q) {
                $q->whereNull('blokir_status')
                  ->orWhere('blokir_status', '!=', 1);
            })
            ->get();

        if ($tagihanUnpaid->isEmpty()) {
            return response()->json([
                'success' => true,
                'results' => [],
                'berhasil' => 0,
                'gagal' => 0,
                'message' => 'Tidak ada tagihan belum lunas & belum terblokir untuk diproses.'
            ]);
        }

        $blokirSetting = DB::table('tbl_blokir')->first();
        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        $checkUser = DB::table('tbl_penggunamikrotik')->first();

        require_once base_path('include/routeros_api.php');

        $successCount = 0;
        $failCount = 0;
        $results = [];

        foreach ($tagihanUnpaid as $tx) {
            $pelanggan = $tx->pelanggan;
            if (!$pelanggan) {
                $results[] = [
                    'status' => false,
                    'nama' => 'N/A',
                    'no_telp' => '-',
                    'message' => 'Data pelanggan tidak ditemukan.'
                ];
                $failCount++;
                continue;
            }

            // Hubungkan ke Mikrotik sesuai dengan router pelanggan
            $id_mikrotik = $pelanggan->id_mikrotik ?: 1;
            $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();

            if (!$mikrotik) {
                $results[] = [
                    'status' => false,
                    'nama' => $pelanggan->nama_pelanggan,
                    'no_telp' => $pelanggan->no_telp ?? '-',
                    'message' => 'Koneksi router Mikrotik tidak ditemukan.'
                ];
                $failCount++;
                continue;
            }

            $user = User::where('id_pelanggan', $pelanggan->id_pelanggan)->first();
            $API = new \RouterosAPI();

            $blockedOnMikrotik = false;
            if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                if ($checkUser && $checkUser->ippelanggan == 'statik') {
                    // Blokir Statik: Tambah IP ke Address-List
                    $API->comm("/ip/firewall/address-list/add", [
                        "list"     => "blocked_clients",
                        "address"  => $pelanggan->ip_address,
                        "comment"  => "Blokir Bulanan " . $pelanggan->ip_address
                    ]);
                    $blockedOnMikrotik = true;
                } else {
                    // Blokir PPPOE: Set profile to pppoe-isolir & enable secret
                    if ($user) {
                        $API->comm("/ppp/secret/set", [
                            "numbers" => $user->username,
                            "profile" => "pppoe-isolir",
                        ]);
                        $API->comm("/ppp/secret/enable", [
                            "numbers" => $user->username,
                        ]);

                        // Cari koneksi pppoe aktif
                        $activeConnections = $API->comm("/ppp/active/print", [
                            "?name" => $user->username,
                        ]);

                        foreach ($activeConnections as $conn) {
                            $API->comm("/ppp/active/remove", [
                                ".id" => $conn['.id'],
                            ]);
                        }
                        $blockedOnMikrotik = true;
                    } else {
                        $results[] = [
                            'status' => false,
                            'nama' => $pelanggan->nama_pelanggan,
                            'no_telp' => $pelanggan->no_telp ?? '-',
                            'message' => 'Akun PPPoE (user) tidak ditemukan di database.'
                        ];
                        $failCount++;
                        $API->disconnect();
                        continue;
                    }
                }
                $API->disconnect();
            } else {
                $results[] = [
                    'status' => false,
                    'nama' => $pelanggan->nama_pelanggan,
                    'no_telp' => $pelanggan->no_telp ?? '-',
                    'message' => 'Gagal terhubung ke Router Mikrotik.'
                ];
                $failCount++;
                continue;
            }

            if ($blockedOnMikrotik) {
                // Update status blokir di database
                $tx->update(['blokir_status' => 1]);

                // Kirim notifikasi WhatsApp jika fitur aktif dan token tersedia
                $waSent = false;
                $waMessage = 'Terblokir di Mikrotik (WA tidak aktif/token kosong)';

                if ($blokirSetting && $blokirSetting->status_blokir === 'aktif' && $tokenInfo && !empty($tokenInfo->token) && !empty($pelanggan->no_telp)) {
                    // Jeda 10 detik jika ini bukan pengiriman pertama
                    if ($successCount > 0) {
                        sleep(10);
                    }

                    $pesan = $blokirSetting->pesan_blokir;
                    $pesan = str_replace('$nama', $pelanggan->nama_pelanggan, $pesan);
                    $pesan = str_replace('$tagihan', number_format($tx->jml_bayar, 0, ',', '.'), $pesan);

                    try {
                        $response = Http::timeout(10)->withHeaders([
                            'Authorization' => $tokenInfo->token
                        ])->asForm()->post('https://api.fonnte.com/send', [
                            'target' => $pelanggan->no_telp,
                            'message' => $pesan,
                            'countryCode' => '62'
                        ]);

                        $resData = $response->json();
                        if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                            $waSent = true;
                            $waMessage = 'Terblokir & Notifikasi WA Terkirim';
                        } else {
                            $reason = $resData['reason'] ?? $resData['message'] ?? 'Device Fonnte tidak aktif.';
                            $waMessage = 'Terblokir & Gagal Kirim WA: ' . $reason;
                        }
                    } catch (\Exception $e) {
                        $waMessage = 'Terblokir & Gagal Kirim WA: ' . $e->getMessage();
                    }
                }

                $results[] = [
                    'status' => true,
                    'nama' => $pelanggan->nama_pelanggan,
                    'no_telp' => $pelanggan->no_telp ?? '-',
                    'message' => $waMessage
                ];
                $successCount++;
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'berhasil' => $successCount,
            'gagal' => $failCount
        ]);
    }

    public function printInvoice($id)
    {
        $tagihan = Tagihan::with(['pelanggan.paketDetail'])->findOrFail($id);
        
        $profile = DB::table('tb_profile')->first();
        if ($profile && !isset($profile->telepon)) {
            $profile->telepon = $profile->telpon ?? '';
        }

        if (empty($tagihan->no_invoice)) {
            $tagihan->no_invoice = 'INV/' . $tagihan->bulan_tahun . '/' . str_pad($tagihan->id_tagihan, 4, '0', STR_PAD_LEFT);
        }

        return view('admin.transaksi.print_invoice', compact('tagihan', 'profile'));
    }

    public function printReceipt($id)
    {
        $tagihan = Tagihan::with(['pelanggan.paketDetail', 'penerima'])->findOrFail($id);
        
        if ($tagihan->status_bayar != 1) {
            return back()->withErrors(['error' => 'Bukti bayar hanya dapat dicetak untuk tagihan yang sudah lunas.']);
        }

        $profile = DB::table('tb_profile')->first();
        if ($profile && !isset($profile->telepon)) {
            $profile->telepon = $profile->telpon ?? '';
        }

        if (empty($tagihan->no_invoice)) {
            $tagihan->no_invoice = 'INV/' . $tagihan->bulan_tahun . '/' . str_pad($tagihan->id_tagihan, 4, '0', STR_PAD_LEFT);
        }

        return view('admin.transaksi.print_receipt', compact('tagihan', 'profile'));
    }
}


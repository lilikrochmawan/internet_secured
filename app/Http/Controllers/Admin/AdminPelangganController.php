<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Paket;
use App\Models\User;
use App\Models\Branch;
use App\Models\SubBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AdminPelangganController extends Controller
{
    public function index(Request $request)
    {
        $selected_device_id = $request->get('device_id', 1);
        $user = auth()->user();

        // 1. Build Query with automatic scoping
        $query = Pelanggan::with(['paketDetail', 'users', 'branch', 'subBranch'])->allowedForUser();

        // Apply filters if present
        if ($request->filled('branch_id')) {
            $query->where('id_branch', $request->branch_id);
        }
        if ($request->filled('sub_branch_id')) {
            $query->where('id_sub_branch', $request->sub_branch_id);
        }

        $pelanggan = $query->orderBy('id_pelanggan', 'desc')->get();
        
        // 2. Fetch allowed Branches and Sub-Branches for filters/forms
        if ($user->level === 'admin') {
            $branches = Branch::orderBy('nama_branch')->get();
            $subBranches = SubBranch::orderBy('nama_sub_branch')->get();
        } else {
            $allowedBranchIds = DB::table('tb_user_branch_access')
                ->where('id_user', $user->id)
                ->pluck('id_branch')
                ->unique();
                
            $branches = Branch::whereIn('id', $allowedBranchIds)->orderBy('nama_branch')->get();

            $allowedSubBranchIds = DB::table('tb_user_branch_access')
                ->where('id_user', $user->id)
                ->whereNotNull('id_sub_branch')
                ->pluck('id_sub_branch')
                ->unique();
                
            $fullyAllowedBranchIds = DB::table('tb_user_branch_access')
                ->where('id_user', $user->id)
                ->whereNull('id_sub_branch')
                ->pluck('id_branch')
                ->unique();

            $subBranches = SubBranch::whereIn('id', $allowedSubBranchIds)
                ->orWhereIn('id_branch', $fullyAllowedBranchIds)
                ->orderBy('nama_sub_branch')
                ->get();
        }
        
        // Fetch support data for modal forms
        $pakets = Paket::orderBy('id_paket')->get();
        $perangkats = DB::table('tb_perangkat')->orderBy('id_perangkat')->get();
        $odps = DB::table('tbl_odp')->orderBy('id_odp')->get();
        $mikrotiks = DB::table('tbl_mikrotik')->get();
        
        $checkUser = DB::table('tbl_penggunamikrotik')->first();

        // Data Mikrotik diset kosong untuk diload secara asinkron (AJAX) di halaman depan
        $pppSecret = [];
        $mikrotikError = null;

        return view('admin.pelanggan.index', compact(
            'pelanggan',
            'pakets',
            'perangkats',
            'odps',
            'mikrotiks',
            'branches',
            'subBranches',
            'pppSecret',
            'selected_device_id',
            'checkUser',
            'mikrotikError'
        ));
    }

    public function getMikrotikSecrets(Request $request)
    {
        $selected_device_id = $request->get('device_id', 1);
        $checkUser = DB::table('tbl_penggunamikrotik')->first();

        if (!$checkUser || $checkUser->status != 'ya') {
            return response()->json([
                'success' => false,
                'message' => 'Integrasi Mikrotik tidak aktif.'
            ]);
        }

        $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $selected_device_id)->first();
        if (!$mikrotik) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi router Mikrotik tidak ditemukan.'
            ]);
        }

        require_once base_path('include/routeros_api.php');
        $API = new \RouterosAPI();
        $API->timeout = 5;

        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
            $API->write('/ppp/secret/print');
            $allSecrets = $API->read();
            $API->disconnect();

            $pppSecret = [];
            if (is_array($allSecrets)) {
                $usedUsernames = DB::table('tb_user')
                    ->pluck('username')
                    ->map(fn($u) => strtolower($u))
                    ->toArray();

                foreach ($allSecrets as $secret) {
                    if (isset($secret['name'])) {
                        $nameLower = strtolower($secret['name']);
                        if (!in_array($nameLower, $usedUsernames)) {
                            $pppSecret[] = [
                                'name' => $secret['name'],
                                'profile' => $secret['profile'] ?? '',
                                'password' => $secret['password'] ?? '',
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'secrets' => $pppSecret
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Gagal terhubung ke router Mikrotik \"" . ($mikrotik->nama_mikrotik ?? 'Router') . " (" . $mikrotik->ip . ")\"."
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'nama' => 'required|string',
            'no_telp' => 'required|string',
            'paket' => 'required|integer',
            'id_mikrotik' => 'required|integer',
            'id_branch' => 'nullable|integer',
            'id_sub_branch' => 'nullable|integer',
        ]);

        // Cek limit maksimum pelanggan berdasarkan lisensi
        $profile = DB::table('tb_profile')->where('id_profile', 1)->first();
        if ($profile && $profile->license_status === 'active' && $profile->license_max_clients > 0) {
            $currentCustomersCount = DB::table('tb_pelanggan')->count();
            if ($currentCustomersCount >= $profile->license_max_clients) {
                return back()->withErrors(['error' => "Batas maksimum pelanggan untuk paket lisensi Anda ({$profile->license_plan_name}) telah tercapai ({$profile->license_max_clients} pelanggan). Silakan tingkatkan paket lisensi Anda."])->withInput();
            }
        }

        $username = htmlspecialchars(strip_tags($request->username));
        $password = htmlspecialchars(strip_tags($request->password));
        $nik = htmlspecialchars(strip_tags($request->nik ?? ''));
        $nama = htmlspecialchars(strip_tags($request->nama));
        $alamat = htmlspecialchars(strip_tags($request->alamat ?? ''));
        $no_telp = htmlspecialchars(strip_tags($request->no_telp));
        $paketId = $request->paket;
        $id_mikrotik = intval($request->id_mikrotik);
        $odpId = $request->odp;
        $perangkatId = ($request->nama_perangkat !== 'NULL') ? $request->nama_perangkat : null;
        $ip_address = $request->ip_address;
        $mapping = $request->mapping;
        $id_branch = $request->id_branch ? intval($request->id_branch) : null;
        $id_sub_branch = $request->id_sub_branch ? intval($request->id_sub_branch) : null;

        $tgl_pemasangan = Carbon::now()->format('Y-m-d H:i');

        // Ambil pengaturan jatuh tempo global
        $settings = DB::table('tb_profile')->first();
        $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
        $default_hari = $settings->hari_jatuh_tempo ?? 10;
        $sistem = $settings->sistem_billing ?? 'prabayar';

        // Jika pascabayar, jatuh tempo pertama adalah bulan depan. Jika prabayar, jatuh tempo pertama adalah bulan ini.
        $targetBilling = ($sistem === 'pascabayar') ? Carbon::now()->addMonth() : Carbon::now();
        $next_year = $targetBilling->year;
        $next_month = $targetBilling->month;

        $due_day = $default_hari;
        if ($tipe === 'tanggal_pasang') {
            // Mengikuti tanggal pemasangan (hari ini)
            $due_day = (int) Carbon::now()->day;
        }

        // Cari jumlah hari maksimum di bulan target
        $days_in_month = (int) date('t', strtotime($next_year . '-' . sprintf('%02d', $next_month) . '-01'));
        if ($due_day > $days_in_month) {
            $due_day = $days_in_month;
        }

        $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $next_year, $next_month, $due_day);

        // Check if phone number already exists in pelanggan
        $existingPhone = Pelanggan::where('no_telp', $no_telp)->first();
        if ($existingPhone) {
            return back()->withErrors(['no_telp' => 'Nomor telepon sudah tercatat di database. Silakan gunakan nomor lain.'])->withInput();
        }

        // Check if username already exists in users
        $existingUser = User::where('username', $username)->first();
        if ($existingUser) {
            return back()->withErrors(['username' => 'Username ini sudah digunakan. Silakan cari username lain.'])->withInput();
        }

        // Check ODP port capacity
        if ($odpId && $odpId !== 'NULL') {
            $odpInfo = DB::table('tbl_odp')->where('id_odp', $odpId)->first();
            if ($odpInfo) {
                $maxPort = $odpInfo->port_odp;
                $currentPortUsage = Pelanggan::where('odp', $odpId)->count();
                if ($currentPortUsage >= $maxPort) {
                    return back()->withErrors(['odp' => 'Batas port ODP tersebut sudah penuh.'])->withInput();
                }
            }
        }

        // Generate customer code
        $lastId = Pelanggan::max('id_pelanggan') ?? 0;
        $newId = $lastId + 1;
        $kode_pelanggan = "WNG03100" . $newId;

        // Insert into tb_pelanggan
        $pelangganId = DB::table('tb_pelanggan')->insertGetId([
            'nik' => $nik,
            'kode_pelanggan' => $kode_pelanggan,
            'nama_pelanggan' => $nama,
            'alamat' => $alamat,
            'no_telp' => $no_telp,
            'paket' => $paketId,
            'ip_address' => $ip_address,
            'tgl_pemasangan' => $tgl_pemasangan,
            'jatuh_tempo' => $tgl_jatuh_tempo,
            'location' => $mapping,
            'id_perangkat' => $perangkatId,
            'odp' => ($odpId !== 'NULL') ? $odpId : null,
            'id_mikrotik' => $id_mikrotik,
            'id_branch' => $id_branch,
            'id_sub_branch' => $id_sub_branch,
        ]);

        // Insert into tb_user
        DB::table('tb_user')->insert([
            'username' => $username,
            'nama_user' => $nama,
            'password' => $password, // Plain text for legacy support
            'level' => 'user',
            'foto' => 'admin.png',
            'id_pelanggan' => $pelangganId,
        ]);

        // Sync with Mikrotik if enabled
        $checkUser = DB::table('tbl_penggunamikrotik')->first();
        if ($checkUser && $checkUser->addppsecret == 'ya') {
            $paket = Paket::find($paketId);
            $id_profile = $paket ? $paket->id_pmikrotik : '';

            $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();
            if ($mikrotik) {
                require_once base_path('include/routeros_api.php');
                $API = new \RouterosAPI();
                if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                    $API->comm("/ppp/secret/add", [
                        "name" => $username,
                        "password" => $password,
                        "service" => 'pppoe',
                        "profile" => $id_profile,
                    ]);
                    $API->disconnect();
                }
            }
        }

        // WhatsApp notification if enabled
        $notifikasi = DB::table('tbl_npemasangan')->first();
        if ($notifikasi && $notifikasi->status_notif == 'aktif') {
            $paket = Paket::find($paketId);
            $namaPaket = $paket ? $paket->nama_paket : '';

            $pesan = $notifikasi->pesan_notif;
            $pesan = str_replace('$nama', $nama, $pesan);
            $pesan = str_replace('$alamat', $alamat, $pesan);
            $pesan = str_replace('$no_telp', $no_telp, $pesan);
            $pesan = str_replace('$paket', $namaPaket, $pesan);
            $pesan = str_replace('$tgl_pemasangan', $tgl_pemasangan, $pesan);
            $pesan = str_replace('$username', $username, $pesan);
            $pesan = str_replace('$password', $password, $pesan);

            $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
            if ($tokenInfo) {
                Http::withHeaders([
                    'Authorization' => $tokenInfo->token
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $no_telp,
                    'message' => $pesan,
                ]);
            }
        }

        return redirect()->route('admin.pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|integer',
            'nama_pelanggan' => 'required|string',
            'no_telp' => 'required|string',
            'paket' => 'required|integer',
            'id_mikrotik' => 'required|integer',
            'id_branch' => 'nullable|integer',
            'id_sub_branch' => 'nullable|integer',
        ]);

        $id = $request->id_pelanggan;
        $pelanggan = Pelanggan::findOrFail($id);

        $nik = htmlspecialchars(strip_tags($request->nik ?? ''));
        $nama = htmlspecialchars(strip_tags($request->nama_pelanggan));
        $alamat = htmlspecialchars(strip_tags($request->alamat ?? ''));
        $no_telp = htmlspecialchars(strip_tags($request->no_telp));
        $paketId = $request->paket;
        $id_mikrotik = intval($request->id_mikrotik);
        $odpId = $request->odp;
        $perangkatId = ($request->nama_perangkat !== 'NULL') ? $request->nama_perangkat : null;
        $ip_address = $request->ip_address;
        $mapping = $request->mapping;
        $id_branch = $request->id_branch ? intval($request->id_branch) : null;
        $id_sub_branch = $request->id_sub_branch ? intval($request->id_sub_branch) : null;

        // Update tb_pelanggan
        DB::table('tb_pelanggan')->where('id_pelanggan', $id)->update([
            'nik' => $nik,
            'nama_pelanggan' => $nama,
            'alamat' => $alamat,
            'no_telp' => $no_telp,
            'paket' => $paketId,
            'ip_address' => $ip_address,
            'location' => $mapping,
            'id_perangkat' => $perangkatId,
            'odp' => ($odpId !== 'NULL') ? $odpId : null,
            'id_mikrotik' => $id_mikrotik,
            'id_branch' => $id_branch,
            'id_sub_branch' => $id_sub_branch,
        ]);

        // Update tb_user name associated
        DB::table('tb_user')->where('id_pelanggan', $id)->update([
            'nama_user' => $nama,
        ]);

        // Sync package change to Mikrotik if package changed and Mikrotik integration is active
        $mikrotikMessage = '';
        $oldPaketId = $pelanggan->paket;
        $paketChanged = intval($oldPaketId) !== intval($paketId);

        if ($paketChanged) {
            $user = User::where('id_pelanggan', $id)->first();
            $checkUser = DB::table('tbl_penggunamikrotik')->first();
            if ($checkUser && $checkUser->addppsecret == 'ya' && $user) {
                $paket = Paket::find($paketId);
                $id_profile = $paket ? $paket->id_pmikrotik : '';

                if ($id_profile) {
                    $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $id_mikrotik)->first();
                    if ($mikrotik) {
                        require_once base_path('include/routeros_api.php');
                        $API = new \RouterosAPI();
                        if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                            // 1. Update secret profile
                            $secrets = $API->comm('/ppp/secret/print', ['?name' => $user->username]);
                            if (!empty($secrets)) {
                                $API->comm('/ppp/secret/set', [
                                    '.id' => $secrets[0]['.id'],
                                    'profile' => $id_profile
                                ]);
                            } else {
                                $API->comm('/ppp/secret/add', [
                                    'name' => $user->username,
                                    'password' => $user->password,
                                    'service' => 'pppoe',
                                    'profile' => $id_profile
                                ]);
                            }

                            // 2. Disconnect active connection
                            $activeConnections = $API->comm('/ppp/active/print', ['?name' => $user->username]);
                            if (!empty($activeConnections)) {
                                foreach ($activeConnections as $connection) {
                                    $API->comm('/ppp/active/remove', [
                                        '.id' => $connection['.id']
                                    ]);
                                }
                            }
                            $API->disconnect();
                        } else {
                            $mikrotikMessage = ' Namun, gagal terhubung ke router Mikrotik untuk memperbarui profil PPPoE.';
                        }
                    } else {
                        $mikrotikMessage = ' Namun, konfigurasi router Mikrotik tidak ditemukan.';
                    }
                }
            }

            // Adjust unpaid monthly bill/invoice for the current month
            $currentMonth = date('mY');
            $unpaidTagihan = DB::table('tb_tagihan')
                ->where('id_pelanggan', $id)
                ->where('bulan_tahun', $currentMonth)
                ->where(function ($q) {
                    $q->whereNull('status_bayar')
                      ->orWhereIn('status_bayar', [0, '0', 'belum', '']);
                })
                ->where(function ($q) {
                    $q->where('manual_invoice', 0)
                      ->orWhereNull('manual_invoice');
                })
                ->first();

            if ($unpaidTagihan) {
                // Calculate new bill amount (taking into account the PPN settings)
                $ppn_aktif = false;
                $paketSettings = DB::table('tbl_paketmikrotik')->first();
                if ($paketSettings && isset($paketSettings->ppn) && $paketSettings->ppn === 'aktif') {
                    $ppn_aktif = true;
                }

                $newPaket = Paket::find($paketId);
                $harga_paket = $newPaket ? $newPaket->harga : 0;
                $ppn_rate = $newPaket ? $newPaket->ppn : 0;

                if ($ppn_aktif) {
                    $newJmlBayar = $harga_paket + ($harga_paket * $ppn_rate);
                } else {
                    $newJmlBayar = $harga_paket;
                }

                DB::table('tb_tagihan')
                    ->where('id_tagihan', $unpaidTagihan->id_tagihan)
                    ->update(['jml_bayar' => $newJmlBayar]);

                $mikrotikMessage .= ' Tagihan bulan ini yang belum terbayar juga telah disesuaikan dengan nominal paket baru.';
            }
        }

        return redirect()->route('admin.pelanggan.index')->with('success', 'Detail pelanggan berhasil diubah!' . $mikrotikMessage);
    }

    public function destroy(Request $request)
    {
        $id = $request->id_pelanggan;
        $pelanggan = Pelanggan::findOrFail($id);
        $user = User::where('id_pelanggan', $id)->first();

        // Delete from Mikrotik if enabled
        $checkUser = DB::table('tbl_penggunamikrotik')->first();
        if ($checkUser && $checkUser->addppsecret == 'ya' && $user) {
            $mikrotik = DB::table('tbl_mikrotik')->where('id_mikrotik', $pelanggan->id_mikrotik)->first();
            if ($mikrotik) {
                require_once base_path('include/routeros_api.php');
                $API = new \RouterosAPI();
                if ($API->connect($mikrotik->ip, $mikrotik->username, $mikrotik->password)) {
                    // Find secret
                    $API->write('/ppp/secret/print', false);
                    $API->write('?name=' . $user->username);
                    $secrets = $API->read();
                    if (!empty($secrets)) {
                        $API->write('/ppp/secret/remove', false);
                        $API->write('=.id=' . $secrets[0]['.id']);
                        $API->read();
                    }
                    $API->disconnect();
                }
            }
        }

        // Delete from DB tables
        DB::table('tb_user')->where('id_pelanggan', $id)->delete();
        DB::table('tb_pelanggan')->where('id_pelanggan', $id)->delete();

        return redirect()->route('admin.pelanggan.index')->with('success', 'Pelanggan berhasil dihapus!');
    }
}

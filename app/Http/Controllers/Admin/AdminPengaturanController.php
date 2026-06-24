<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;

class AdminPengaturanController extends Controller
{
    public function index()
    {
        $profile = DB::table('tb_profile')->first();
        if ($profile) {
            $profile->telepon = $profile->telpon ?? '';
        }
        $mikrotik_devices = DB::table('tbl_mikrotik')->orderBy('id_mikrotik', 'desc')->get();
        $token = DB::table('tbl_token')->where('id_token', 1)->first();
        $midtrans = DB::table('tbl_pgate')->where('id_pgat', 1)->first();

        return view('admin.pengaturan.index', compact('profile', 'mikrotik_devices', 'token', 'midtrans'));
    }

    public function updateMidtrans(Request $request)
    {
        $request->validate([
            'tclientkey' => 'required|string',
            'tserverkey' => 'required|string',
            'mode' => 'required|string|in:sandbox,live',
        ]);

        DB::table('tbl_pgate')->where('id_pgat', 1)->update([
            'tclientkey' => htmlspecialchars(strip_tags($request->tclientkey)),
            'tserverkey' => htmlspecialchars(strip_tags($request->tserverkey)),
            'mode' => htmlspecialchars(strip_tags($request->mode)),
        ]);

        return redirect()->route('admin.pengaturan.index')->with('success', 'Kredensial Midtrans berhasil diperbarui!');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'required|string',
            'telepon' => 'required|string',
            'email' => 'required|email',
            'alamat' => 'required|string',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $profile = DB::table('tb_profile')->where('id_profile', 1)->first();
        $filename = $profile->foto ?? 'ion.png';

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            
            $uploadDir = public_path('images');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $file->move($uploadDir, $filename);

            if ($profile && !empty($profile->foto) && $profile->foto !== 'ion.png' && file_exists(public_path('images/' . $profile->foto))) {
                @unlink(public_path('images/' . $profile->foto));
            }
        }

        DB::table('tb_profile')->where('id_profile', 1)->update([
            'nama_sekolah' => htmlspecialchars(strip_tags($request->nama_sekolah)),
            'telpon' => htmlspecialchars(strip_tags($request->telepon)),
            'email' => htmlspecialchars(strip_tags($request->email)),
            'alamat' => htmlspecialchars(strip_tags($request->alamat)),
            'foto' => $filename,
        ]);

        return redirect()->route('admin.pengaturan.index')->with('success', 'Informasi profil usaha dan logo berhasil diperbarui!');
    }

    public function updateMikrotik(Request $request)
    {
        $request->validate([
            'ip' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'port_mikrotik' => 'required|integer',
            'nama_mikrotik' => 'required|string',
        ]);

        $id = $request->id_mikrotik;
        $data = [
            'ip' => htmlspecialchars(strip_tags($request->ip)),
            'username' => htmlspecialchars(strip_tags($request->username)),
            'password' => $request->password, // Simpan password Winbox
            'port_mikrotik' => $request->port_mikrotik,
            'nama_mikrotik' => htmlspecialchars(strip_tags($request->nama_mikrotik)),
        ];

        if (!empty($id)) {
            DB::table('tbl_mikrotik')->where('id_mikrotik', $id)->update($data);
            $msg = 'Konfigurasi router Mikrotik berhasil diperbarui!';
        } else {
            DB::table('tbl_mikrotik')->insert($data);
            $msg = 'Device Mikrotik baru berhasil ditambahkan!';
        }

        return redirect()->route('admin.pengaturan.index')->with('success', $msg);
    }

    public function deleteMikrotik(Request $request)
    {
        $request->validate([
            'id_mikrotik' => 'required|integer',
        ]);

        // Cek jika ID Mikrotik ini masih digunakan oleh pelanggan
        $isInUse = DB::table('tb_pelanggan')->where('id_mikrotik', $request->id_mikrotik)->exists();
        if ($isInUse) {
            return back()->withErrors(['error' => 'Device Mikrotik ini tidak bisa dihapus karena masih dikaitkan dengan beberapa data pelanggan.']);
        }

        DB::table('tbl_mikrotik')->where('id_mikrotik', $request->id_mikrotik)->delete();

        return redirect()->route('admin.pengaturan.index')->with('success', 'Device Mikrotik berhasil dihapus!');
    }

    public function updateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        DB::table('tbl_token')->where('id_token', 1)->update([
            'token' => htmlspecialchars(strip_tags($request->token)),
        ]);

        return redirect()->route('admin.pengaturan.index')->with('success', 'Token WhatsApp Fonnte berhasil diperbarui!');
    }

    /**
     * Portable Database Exporter (Generates a clean SQL backup and downloads it)
     */
    public function backupDb()
    {
        // Hubungkan semua tabel di database
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE', 'tagihan_lotus');
        
        $sqlDump = "-- Indotel Billing SQL Database Backup\n";
        $sqlDump .= "-- Generated on: " . now()->toDateTimeString() . "\n";
        $sqlDump .= "-- Database Name: " . $dbName . "\n\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $array = (array)$table;
            $tableName = current($array);
            
            // Skip Laravel session & job tables if they are too large
            if (in_array($tableName, ['sessions', 'job_batches'])) {
                continue;
            }

            // Get Create Table structure
            $createTableQuery = DB::select("SHOW CREATE TABLE `{$tableName}`");
            if (!empty($createTableQuery)) {
                $createTableArray = (array)$createTableQuery[0];
                $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sqlDump .= $createTableArray['Create Table'] . ";\n\n";
            }

            // Get Rows data
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $keys = array_keys($rowArray);
                    $values = array_values($rowArray);

                    // Escaped values
                    $escapedValues = array_map(function ($value) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        return "'" . addslashes((string)$value) . "'";
                    }, $values);

                    $keysStr = "`" . implode("`, `", $keys) . "`";
                    $valuesStr = implode(", ", $escapedValues);

                    $sqlDump .= "INSERT INTO `{$tableName}` ({$keysStr}) VALUES ({$valuesStr});\n";
                }
                $sqlDump .= "\n";
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Return file download
        $filename = "backup_billing_" . date('Y_m_d_His') . ".sql";
        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ];

        return Response::make($sqlDump, 200, $headers);
    }

    public function updateJatuhTempo(Request $request)
    {
        $request->validate([
            'tipe_jatuh_tempo' => 'required|string|in:tanggal_pasang,tanggal_tetap',
            'hari_jatuh_tempo' => 'required|integer|min:1|max:31',
            'sistem_billing' => 'required|string|in:prabayar,pascabayar',
            'auto_send_billing' => 'required|integer|in:0,1',
            'auto_send_date' => 'required|integer|min:1|max:31',
            'auto_send_h_minus' => 'required|integer|min:0|max:30',
        ]);

        $tipe = htmlspecialchars(strip_tags($request->tipe_jatuh_tempo));
        $default_hari = intval($request->hari_jatuh_tempo);
        $sistem = htmlspecialchars(strip_tags($request->sistem_billing));
        $auto_send = intval($request->auto_send_billing);
        $auto_date = intval($request->auto_send_date);
        $auto_h_minus = intval($request->auto_send_h_minus);

        DB::beginTransaction();
        try {
            DB::table('tb_profile')->where('id_profile', 1)->update([
                'tipe_jatuh_tempo' => $tipe,
                'hari_jatuh_tempo' => $default_hari,
                'sistem_billing' => $sistem,
                'auto_send_billing' => $auto_send,
                'auto_send_date' => $auto_date,
                'auto_send_h_minus' => $auto_h_minus,
            ]);

            // Sinkronisasi jatuh_tempo pelanggan dan tagihan yang belum lunas
            $pelanggans = DB::table('tb_pelanggan')->get();
            $current_month_code = date('mY'); // format 'mY' e.g. '062026'

            foreach ($pelanggans as $pelanggan) {
                // Cari apakah ada tagihan belum dibayar
                $unpaidBill = DB::table('tb_tagihan')
                    ->where('id_pelanggan', $pelanggan->id_pelanggan)
                    ->whereNull('status_bayar')
                    ->orderBy('id_tagihan', 'desc')
                    ->first();

                if ($unpaidBill) {
                    // Ada tagihan belum dibayar. Hitung jatuh tempo berdasarkan bulan_tahun tagihan tersebut.
                    $bulan_tahun = $unpaidBill->bulan_tahun;
                    if (is_string($bulan_tahun) && strlen($bulan_tahun) === 6) {
                        $target_month = (int) substr($bulan_tahun, 0, 2);
                        $target_year = (int) substr($bulan_tahun, 2, 4);
                    } else {
                        // Fallback jika format salah
                        $target_month = (int) date('m');
                        $target_year = (int) date('Y');
                    }

                    // Tentukan bulan berdasarkan prabayar/pascabayar
                    $date = \Carbon\Carbon::create($target_year, $target_month, 1);
                    if ($sistem === 'pascabayar') {
                        $date->addMonth();
                    }
                    $year = $date->year;
                    $month = $date->month;
                } else {
                    // Tidak ada tagihan belum dibayar. Maka jatuh tempo berikutnya di bulan depan.
                    $date = \Carbon\Carbon::now()->addMonth();
                    $year = $date->year;
                    $month = $date->month;
                }

                // Hitung hari jatuh tempo
                $due_day = $default_hari;
                if ($tipe === 'tanggal_pasang' && !empty($pelanggan->tgl_pemasangan)) {
                    $due_day = (int) date('d', strtotime($pelanggan->tgl_pemasangan));
                }

                // Proteksi batas hari maksimum di bulan target
                $days_in_month = (int) date('t', strtotime($year . '-' . sprintf('%02d', $month) . '-01'));
                if ($due_day > $days_in_month) {
                    $due_day = $days_in_month;
                }

                // Pukul diatur ke 23:59:00 sesuai request user
                $new_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $year, $month, $due_day);

                // Update data pelanggan
                DB::table('tb_pelanggan')
                    ->where('id_pelanggan', $pelanggan->id_pelanggan)
                    ->update(['jatuh_tempo' => $new_jatuh_tempo]);

                // Update data tagihan yang belum dibayar
                DB::table('tb_tagihan')
                    ->where('id_pelanggan', $pelanggan->id_pelanggan)
                    ->whereNull('status_bayar')
                    ->update(['jatuh_tempo' => $new_jatuh_tempo]);
            }

            DB::commit();
            $msg = 'Pengaturan jatuh tempo berhasil diperbarui dan disinkronkan ke seluruh data pelanggan!';
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui pengaturan jatuh tempo: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.pengaturan.index')->with('success', $msg);
    }

    public function showUnlicensed()
    {
        $profile = DB::table('tb_profile')->where('id_profile', 1)->first();
        return view('errors.unlicensed', compact('profile'));
    }

    public function activateLicense(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $result = $this->verifyLicenseWithServer($request->license_key);

        if ($result['success']) {
            return redirect()->route('admin.dashboard')->with('success', 'Lisensi berhasil diaktifkan! Aplikasi billing kini aktif.');
        }

        return back()->withErrors(['license_key' => $result['message']]);
    }

    public function updateLicense(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $result = $this->verifyLicenseWithServer($request->license_key);

        if ($result['success']) {
            return redirect()->route('admin.pengaturan.index')->with('success', 'License Key berhasil diperbarui dan divalidasi!');
        }

        return back()->withErrors(['license_key' => $result['message']]);
    }

    private function verifyLicenseWithServer($licenseKey)
    {
        $serverUrl = env('LICENSE_SERVER_URL', 'http://localhost:8000');
        $appUrl = config('app.url', 'localhost');
        $domain = parse_url($appUrl, PHP_URL_HOST) ?: 'localhost';

        $ipAddress = '127.0.0.1';
        try {
            $responseIp = \Illuminate\Support\Facades\Http::timeout(3)->get('https://api.ipify.org');
            if ($responseIp->successful()) {
                $ipAddress = trim($responseIp->body());
            }
        } catch (\Exception $e) {
            $ipAddress = gethostbyname(gethostname()) ?: '127.0.0.1';
        }

        try {
            $apiUrl = rtrim($serverUrl, '/') . '/api/license/verify';
            $response = \Illuminate\Support\Facades\Http::timeout(10)->post($apiUrl, [
                'license_key' => $licenseKey,
                'domain' => $domain,
                'ip_address' => $ipAddress,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['status']) && $data['status'] === 'active') {
                $expiresAt = $data['expires_at'] !== 'lifetime' 
                    ? \Illuminate\Support\Carbon::parse($data['expires_at']) 
                    : null;

                DB::table('tb_profile')->where('id_profile', 1)->update([
                    'license_key' => $licenseKey,
                    'license_status' => 'active',
                    'license_expires_at' => $expiresAt,
                    'license_plan_name' => $data['plan_name'] ?? 'Lite',
                    'license_max_clients' => intval($data['max_clients'] ?? 250),
                    'license_client_name' => $data['client_name'] ?? null,
                    'license_last_checked' => \Illuminate\Support\Carbon::now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Lisensi aktif.'
                ];
            }

            $status = $data['status'] ?? 'invalid';
            $msg = $data['message'] ?? 'Lisensi tidak valid.';
            
            DB::table('tb_profile')->where('id_profile', 1)->update([
                'license_key' => $licenseKey,
                'license_status' => $status,
                'license_last_checked' => \Illuminate\Support\Carbon::now(),
            ]);

            return [
                'success' => false,
                'message' => $msg
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server lisensi untuk verifikasi: ' . $e->getMessage()
            ];
        }
    }
}

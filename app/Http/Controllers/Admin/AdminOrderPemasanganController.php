<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderPemasangan;
use App\Models\Pelanggan;
use App\Models\User;
use App\Models\Paket;
use App\Models\Odp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminOrderPemasanganController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Fetch options for the approval modal (Admin only)
        $pakets = Paket::all();
        $mikrotiks = [];
        $odps = [];
        $branches = [];
        $subBranches = [];
        $teknisis = [];
        $topTeknisi = [];

        if ($user->level === 'admin' || $user->level === 'noc') {
            $mikrotiks = DB::table('tbl_mikrotik')->get();
            $odps = Odp::withCount('pelanggans')->get();
            $branches = DB::table('tb_branch')->get();
            $subBranches = DB::table('tb_sub_branch')->get();
            $teknisis = User::where('level', 'teknisi')->get()->map(function($tek) {
                $tek->has_active_order = OrderPemasangan::where('id_teknisi', $tek->id)
                    ->whereIn('status', ['approved', 'installed'])
                    ->exists();
                return $tek;
            });

            // Query top 10 technicians for the current month
            $currentMonthStart = Carbon::now()->startOfMonth()->toDateTimeString();
            $currentMonthEnd = Carbon::now()->endOfMonth()->toDateTimeString();

            $topTeknisi = DB::table('tbl_order_pemasangan')
                ->join('tb_user', 'tbl_order_pemasangan.id_teknisi', '=', 'tb_user.id')
                ->select('tb_user.nama_user', DB::raw('count(tbl_order_pemasangan.id) as total'))
                ->where('tbl_order_pemasangan.status', 'installed')
                ->whereBetween('tbl_order_pemasangan.updated_at', [$currentMonthStart, $currentMonthEnd])
                ->groupBy('tbl_order_pemasangan.id_teknisi', 'tb_user.nama_user')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();
        }

        // Fetch orders based on role
        if ($user->level === 'sales') {
            // Sales sees only their own uploaded orders
            $orders = OrderPemasangan::with(['sales', 'teknisi', 'paketDetail'])
                ->where('id_sales', $user->id)
                ->orderBy('id', 'desc')
                ->get();
        } elseif ($user->level === 'teknisi') {
            // Technician sees orders assigned to them or assigned to All (0) that are approved or installed
            $orders = OrderPemasangan::with(['sales', 'teknisi', 'paketDetail'])
                ->where(function($q) use ($user) {
                    $q->where('id_teknisi', $user->id)
                      ->orWhere('id_teknisi', 0);
                })
                ->whereIn('status', ['approved', 'installed'])
                ->orderBy('id', 'desc')
                ->get();
        } else {
            // Admin and others see all orders
            $orders = OrderPemasangan::with(['sales', 'teknisi', 'paketDetail'])
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('admin.order_pemasangan.index', compact(
            'orders', 'pakets', 'mikrotiks', 'odps', 'branches', 'subBranches', 'teknisis', 'topTeknisi'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'paket' => 'required|integer|exists:tb_paket,id_paket',
            'alamat_ktp' => 'required|string',
            'alamat_pemasangan' => 'required|string',
            'koordinat_pemasangan' => 'required|string|max:100',
            'jadwal_pemasangan' => 'nullable|string',
            'foto_ktp' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ], [
            'foto_ktp.required' => 'Foto KTP wajib diunggah.',
            'foto_ktp.image' => 'File harus berupa gambar.',
            'foto_ktp.max' => 'Ukuran file maksimal 5 MB.',
            'paket.required' => 'Paket internet wajib dipilih.',
        ]);

        $fotoName = '';
        if ($request->hasFile('foto_ktp')) {
            $uploadDir = base_path('administrator/page/order/images');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $request->file('foto_ktp');
            $fotoName = uniqid() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fotoName);
        }

        $jadwal = null;
        if (!empty($request->jadwal_pemasangan)) {
            try {
                $jadwal = Carbon::parse($request->jadwal_pemasangan)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $jadwal = null;
            }
        }

        OrderPemasangan::create([
            'nik' => htmlspecialchars(strip_tags($request->nik)),
            'nama' => htmlspecialchars(strip_tags($request->nama)),
            'no_telp' => htmlspecialchars(strip_tags($request->no_telp)),
            'paket' => intval($request->paket),
            'alamat_ktp' => htmlspecialchars(strip_tags($request->alamat_ktp)),
            'alamat_pemasangan' => htmlspecialchars(strip_tags($request->alamat_pemasangan)),
            'koordinat_pemasangan' => htmlspecialchars(strip_tags($request->koordinat_pemasangan)),
            'jadwal_pemasangan' => $jadwal,
            'foto_ktp' => $fotoName,
            'status' => 'pending',
            'id_sales' => Auth::id(),
        ]);

        return redirect()->route('admin.order_pemasangan.index')->with('success', 'Order pemasangan baru berhasil dikirim dan menunggu persetujuan admin!');
    }

    public function assign(Request $request)
    {
        $request->validate([
            'id_order' => 'required|integer|exists:tbl_order_pemasangan,id',
            'id_teknisi' => 'required|integer', // Allow 0 for "Semua Teknisi"
        ]);

        if ($request->id_teknisi > 0) {
            $hasActive = OrderPemasangan::where('id_teknisi', $request->id_teknisi)
                ->whereIn('status', ['approved', 'installed'])
                ->exists();
            if ($hasActive) {
                return back()->withErrors(['error' => 'Teknisi tersebut masih memiliki order pemasangan yang aktif atau menunggu verifikasi admin!']);
            }
        }

        $order = OrderPemasangan::findOrFail($request->id_order);
        $order->update([
            'id_teknisi' => $request->id_teknisi,
        ]);

        return redirect()->route('admin.order_pemasangan.index')->with('success', 'Teknisi berhasil ditugaskan untuk order ini!');
    }

    public function approve(Request $request)
    {
        $request->validate([
            'id_order' => 'required|integer|exists:tbl_order_pemasangan,id',
        ]);

        $order = OrderPemasangan::findOrFail($request->id_order);
        if (is_null($order->id_teknisi)) {
            return back()->withErrors(['error' => 'Wajib menugaskan teknisi terlebih dahulu sebelum melakukan ACC!'])->withInput();
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'no_telp' => 'required|string',
            'paket' => 'required|integer',
            'id_mikrotik' => 'required|integer',
            'id_branch' => 'nullable|integer',
            'id_sub_branch' => 'nullable|integer',
            'odp' => 'nullable|integer',
        ]);

        $order = OrderPemasangan::findOrFail($request->id_order);

        $username = htmlspecialchars(strip_tags($request->username));
        $password = htmlspecialchars(strip_tags($request->password));
        $no_telp = htmlspecialchars(strip_tags($request->no_telp));
        $paketId = $request->paket;
        $id_mikrotik = intval($request->id_mikrotik);
        $odpId = $request->odp;
        $id_branch = $request->id_branch ? intval($request->id_branch) : null;
        $id_sub_branch = $request->id_sub_branch ? intval($request->id_sub_branch) : null;

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
        if ($odpId) {
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

        // Date calculation for billing
        $tgl_pemasangan = Carbon::now()->format('Y-m-d H:i');
        $settings = DB::table('tb_profile')->first();
        $tipe = $settings->tipe_jatuh_tempo ?? 'tanggal_tetap';
        $default_hari = $settings->hari_jatuh_tempo ?? 10;
        $sistem = $settings->sistem_billing ?? 'prabayar';

        $targetBilling = ($sistem === 'pascabayar') ? Carbon::now()->addMonth() : Carbon::now();
        $next_year = $targetBilling->year;
        $next_month = $targetBilling->month;

        $due_day = $default_hari;
        if ($tipe === 'tanggal_pasang') {
            $due_day = (int) Carbon::now()->day;
        }

        $days_in_month = (int) date('t', strtotime($next_year . '-' . sprintf('%02d', $next_month) . '-01'));
        if ($due_day > $days_in_month) {
            $due_day = $days_in_month;
        }

        $tgl_jatuh_tempo = sprintf('%04d-%02d-%02d 23:59:00', $next_year, $next_month, $due_day);

        // Transaction DB
        DB::transaction(function() use (
            $order, $kode_pelanggan, $username, $password, $no_telp, $paketId, $id_mikrotik, 
            $odpId, $id_branch, $id_sub_branch, $tgl_pemasangan, $tgl_jatuh_tempo
        ) {
            // 1. Insert into tb_pelanggan
            $pelangganId = DB::table('tb_pelanggan')->insertGetId([
                'nik' => $order->nik,
                'kode_pelanggan' => $kode_pelanggan,
                'nama_pelanggan' => $order->nama,
                'alamat' => $order->alamat_pemasangan,
                'no_telp' => $no_telp,
                'paket' => $paketId,
                'ip_address' => null,
                'tgl_pemasangan' => $tgl_pemasangan,
                'jatuh_tempo' => $tgl_jatuh_tempo,
                'location' => $order->koordinat_pemasangan,
                'id_perangkat' => null,
                'odp' => $odpId,
                'id_mikrotik' => $id_mikrotik,
                'id_branch' => $id_branch,
                'id_sub_branch' => $id_sub_branch,
            ]);

            // 2. Insert into tb_user
            DB::table('tb_user')->insert([
                'username' => $username,
                'nama_user' => $order->nama,
                'password' => $password, // Plain text for legacy support
                'level' => 'user',
                'foto' => 'admin.png',
                'id_pelanggan' => $pelangganId,
            ]);

            // 3. Update order status to approved
            $order->update([
                'status' => 'approved',
            ]);

            // 4. Sync with Mikrotik if enabled
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

            // 5. WhatsApp notification if enabled
            $notifikasi = DB::table('tbl_npemasangan')->first();
            if ($notifikasi && $notifikasi->status_notif == 'aktif') {
                $paket = Paket::find($paketId);
                $namaPaket = $paket ? $paket->nama_paket : '';

                $pesan = $notifikasi->pesan_notif;
                $pesan = str_replace('$nama', $order->nama, $pesan);
                $pesan = str_replace('$alamat', $order->alamat_pemasangan, $pesan);
                $pesan = str_replace('$no_telp', $no_telp, $pesan);
                $pesan = str_replace('$paket', $namaPaket, $pesan);
                $pesan = str_replace('$tgl_pemasangan', $tgl_pemasangan, $pesan);
                $pesan = str_replace('$username', $username, $pesan);
                $pesan = str_replace('$password', $password, $pesan);

                $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
                if ($tokenInfo && !empty($tokenInfo->token)) {
                    try {
                        \Illuminate\Support\Facades\Http::withHeaders([
                            'Authorization' => $tokenInfo->token
                        ])->asForm()->post('https://api.fonnte.com/send', [
                            'target' => $no_telp,
                            'message' => $pesan,
                            'countryCode' => '62'
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Fonnte API Error Order Approved: ' . $e->getMessage());
                    }
                }
            }
        });

        return redirect()->route('admin.order_pemasangan.index')->with('success', 'Order berhasil disetujui (ACC) dan data pelanggan telah terdaftar!');
    }

    public function complete(Request $request)
    {
        $request->validate([
            'id_order' => 'required|integer|exists:tbl_order_pemasangan,id',
            'foto_dokumentasi' => 'required|array|min:1|max:3',
            'foto_dokumentasi.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ], [
            'foto_dokumentasi.required' => 'Wajib melampirkan foto bukti dokumentasi pemasangan!',
            'foto_dokumentasi.array' => 'Format file tidak valid.',
            'foto_dokumentasi.min' => 'Wajib melampirkan minimal 1 foto bukti dokumentasi!',
            'foto_dokumentasi.max' => 'Maksimal hanya boleh mengunggah 3 foto bukti dokumentasi!',
            'foto_dokumentasi.*.image' => 'File harus berupa gambar.',
            'foto_dokumentasi.*.max' => 'Ukuran setiap file gambar maksimal 5 MB.',
        ]);

        $uploadedFiles = [];
        if ($request->hasFile('foto_dokumentasi')) {
            $uploadDir = base_path('administrator/page/order/images');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            foreach ($request->file('foto_dokumentasi') as $index => $file) {
                $fotoName = 'doc_' . $request->id_order . '_' . $index . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $fotoName);
                $uploadedFiles[] = $fotoName;
            }
        }

        $order = OrderPemasangan::findOrFail($request->id_order);
        $order->update([
            'status' => 'installed',
            'foto_dokumentasi' => json_encode($uploadedFiles),
        ]);

        return redirect()->route('admin.order_pemasangan.index')->with('success', 'Pemasangan selesai dipasang dan bukti foto berhasil diunggah. Menunggu verifikasi admin!');
    }

    public function claim(Request $request)
    {
        $request->validate([
            'id_order' => 'required|integer|exists:tbl_order_pemasangan,id',
        ]);

        // Make sure technician doesn't have active order
        $hasActive = OrderPemasangan::where('id_teknisi', Auth::id())
            ->whereIn('status', ['approved', 'installed'])
            ->exists();
        if ($hasActive) {
            return back()->withErrors(['error' => 'Anda masih memiliki order pemasangan yang aktif atau menunggu verifikasi admin!']);
        }

        $order = OrderPemasangan::findOrFail($request->id_order);

        // Make sure it is currently assigned to All (0)
        if ($order->id_teknisi !== 0) {
            return back()->withErrors(['error' => 'Order ini sudah diambil atau ditugaskan ke teknisi lain.']);
        }

        $order->update([
            'id_teknisi' => Auth::id(),
        ]);

        return redirect()->route('admin.order_pemasangan.index')->with('success', 'Order pemasangan berhasil Anda ambil!');
    }

    public function showKtp($filename)
    {
        $path = base_path('administrator/page/order/images/' . $filename);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'id_order' => 'required|integer|exists:tbl_order_pemasangan,id',
        ]);

        $order = OrderPemasangan::findOrFail($request->id_order);
        
        // Make sure it is currently 'installed'
        if ($order->status !== 'installed') {
            return back()->withErrors(['error' => 'Order ini belum dipasang oleh teknisi atau status tidak valid untuk konfirmasi.']);
        }

        $order->update([
            'status' => 'completed',
        ]);

        return redirect()->route('admin.order_pemasangan.index')->with('success', 'Pemasangan berhasil dikonfirmasi selesai dan aktif!');
    }

    public function showDokumentasi($filename)
    {
        $path = base_path('administrator/page/order/images/' . $filename);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path);
    }
}

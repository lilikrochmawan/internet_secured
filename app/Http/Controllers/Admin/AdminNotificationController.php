<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Odp;
use App\Models\Pelanggan;
use App\Models\Informasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $odps = Odp::orderBy('nama_odp', 'asc')->get();
        $odcs = DB::table('tbl_odc')->orderBy('nama_odc', 'asc')->get();
        $announcements = Informasi::orderBy('id_informasi', 'desc')->take(5)->get();
        return view('admin.notification.index', compact('odps', 'announcements', 'odcs'));
    }

    public function getOdpClients($id)
    {
        $clients = Pelanggan::where('odp', $id)->get(['id_pelanggan', 'kode_pelanggan', 'nama_pelanggan', 'no_telp']);
        return response()->json($clients);
    }

    public function getOdcClients($id)
    {
        $odpIds = Odp::where('odc', $id)->pluck('id_odp');
        $clients = Pelanggan::with('odpDetail')
            ->whereIn('odp', $odpIds)
            ->get(['id_pelanggan', 'kode_pelanggan', 'nama_pelanggan', 'no_telp', 'odp']);
        
        $formattedClients = $clients->map(function($client) {
            return [
                'id_pelanggan' => $client->id_pelanggan,
                'kode_pelanggan' => $client->kode_pelanggan,
                'nama_pelanggan' => $client->nama_pelanggan,
                'no_telp' => $client->no_telp,
                'nama_odp' => $client->odpDetail->nama_odp ?? 'N/A'
            ];
        });
        return response()->json($formattedClients);
    }

    public function sendGeneral(Request $request)
    {
        $request->validate([
            'pesan' => 'required|string',
            'channels' => 'required|array',
            'judul' => 'required_if:channels.*,app|nullable|string',
        ]);

        $channels = $request->input('channels');
        $judul = $request->input('judul');
        $pesan = $request->input('pesan');

        // 1. Tampilkan di Aplikasi Login Pelanggan (tbl_informasi)
        if (in_array('app', $channels)) {
            Informasi::create([
                'judul_informasi' => $judul ?? 'Pengumuman Resmi',
                'isi_informasi' => $pesan,
            ]);
        }

        // 2. Kirim via WhatsApp ke Semua Pelanggan
        $results = [];
        $berhasil = 0;
        $gagal = 0;

        if (in_array('wa', $channels)) {
            set_time_limit(1800);

            $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
            if (!$tokenInfo || empty($tokenInfo->token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token WhatsApp Fonnte belum dikonfigurasi di pengaturan.'
                ], 400);
            }

            // Ambil semua pelanggan dengan nomor telepon
            $pelangganList = Pelanggan::whereNotNull('no_telp')
                ->where('no_telp', '!=', '')
                ->get();

            if ($pelangganList->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'results' => [],
                    'berhasil' => 0,
                    'gagal' => 0,
                    'message' => 'Tidak ada pelanggan dengan nomor telepon aktif.'
                ]);
            }

            foreach ($pelangganList as $index => $pelanggan) {
                // Jeda pengiriman 10 detik (kecuali pesan pertama)
                if ($index > 0) {
                    sleep(10);
                }

                // Format dinamis jika menggunakan tag nama
                $customPesan = str_replace(
                    ['$nama', '$pelanggan'],
                    [$pelanggan->nama_pelanggan, $pelanggan->nama_pelanggan],
                    $pesan
                );

                try {
                    $response = Http::timeout(10)->withHeaders([
                        'Authorization' => $tokenInfo->token
                    ])->asForm()->post('https://api.fonnte.com/send', [
                        'target' => $pelanggan->no_telp,
                        'message' => $customPesan,
                        'countryCode' => '62'
                    ]);

                    $resData = $response->json();
                    if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                        $berhasil++;
                        $results[] = [
                            'status' => true,
                            'nama' => $pelanggan->nama_pelanggan,
                            'no_telp' => $pelanggan->no_telp,
                            'message' => 'Terkirim'
                        ];
                    } else {
                        $gagal++;
                        $reason = $resData['reason'] ?? $resData['message'] ?? 'Fonnte error atau device offline.';
                        $results[] = [
                            'status' => false,
                            'nama' => $pelanggan->nama_pelanggan,
                            'no_telp' => $pelanggan->no_telp,
                            'message' => $reason
                        ];
                    }
                } catch (\Exception $e) {
                    $gagal++;
                    Log::error("Broadcast WA error to {$pelanggan->nama_pelanggan}: " . $e->getMessage());
                    $results[] = [
                        'status' => false,
                        'nama' => $pelanggan->nama_pelanggan,
                        'no_telp' => $pelanggan->no_telp,
                        'message' => 'Koneksi API Gagal: ' . $e->getMessage()
                    ];
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'results' => $results,
                'berhasil' => $berhasil,
                'gagal' => $gagal,
                'message' => 'Pengiriman notifikasi umum selesai.'
            ]);
        }

        return redirect()->route('admin.broadcast.index')->with('success', 'Pengumuman aplikasi berhasil diterbitkan!');
    }

    public function sendOdp(Request $request)
    {
        $request->validate([
            'id_odp' => 'required|integer',
            'pesan' => 'required|string',
            'client_ids' => 'required|array',
        ]);

        set_time_limit(1800);

        $id_odp = $request->input('id_odp');
        $pesan = $request->input('pesan');
        $client_ids = $request->input('client_ids');

        $odp = Odp::findOrFail($id_odp);

        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        if (!$tokenInfo || empty($tokenInfo->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token WhatsApp Fonnte belum dikonfigurasi di pengaturan.'
            ], 400);
        }

        $pelangganList = Pelanggan::whereIn('id_pelanggan', $client_ids)
            ->whereNotNull('no_telp')
            ->where('no_telp', '!=', '')
            ->get();

        if ($pelangganList->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada pelanggan terpilih yang memiliki nomor telepon aktif.'
            ], 400);
        }

        $results = [];
        $berhasil = 0;
        $gagal = 0;

        foreach ($pelangganList as $index => $pelanggan) {
            // Jeda pengiriman 10 detik (kecuali pesan pertama)
            if ($index > 0) {
                sleep(10);
            }

            // Format dinamis
            $customPesan = str_replace(
                ['$nama', '$pelanggan', '$odp'],
                [$pelanggan->nama_pelanggan, $pelanggan->nama_pelanggan, $odp->nama_odp],
                $pesan
            );

            try {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => $tokenInfo->token
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $pelanggan->no_telp,
                    'message' => $customPesan,
                    'countryCode' => '62'
                ]);

                $resData = $response->json();
                if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                    $berhasil++;
                    $results[] = [
                        'status' => true,
                        'nama' => $pelanggan->nama_pelanggan,
                        'no_telp' => $pelanggan->no_telp,
                        'message' => 'Terkirim'
                    ];
                } else {
                    $gagal++;
                    $reason = $resData['reason'] ?? $resData['message'] ?? 'Fonnte error atau device offline.';
                    $results[] = [
                        'status' => false,
                        'nama' => $pelanggan->nama_pelanggan,
                        'no_telp' => $pelanggan->no_telp,
                        'message' => $reason
                    ];
                }
            } catch (\Exception $e) {
                $gagal++;
                Log::error("Broadcast WA ODP error to {$pelanggan->nama_pelanggan}: " . $e->getMessage());
                $results[] = [
                    'status' => false,
                    'nama' => $pelanggan->nama_pelanggan,
                    'no_telp' => $pelanggan->no_telp,
                    'message' => 'Koneksi API Gagal: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'message' => 'Pengiriman notifikasi ODP selesai.'
        ]);
    }

    public function sendOdc(Request $request)
    {
        $request->validate([
            'id_odc' => 'required|integer',
            'pesan' => 'required|string',
            'client_ids' => 'required|array',
        ]);

        set_time_limit(1800);

        $id_odc = $request->input('id_odc');
        $pesan = $request->input('pesan');
        $client_ids = $request->input('client_ids');

        $odc = DB::table('tbl_odc')->where('id_odc', $id_odc)->first();
        if (!$odc) {
            return response()->json([
                'success' => false,
                'message' => 'ODC tidak ditemukan.'
            ], 404);
        }

        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        if (!$tokenInfo || empty($tokenInfo->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token WhatsApp Fonnte belum dikonfigurasi di pengaturan.'
            ], 400);
        }

        $pelangganList = Pelanggan::with('odpDetail')
            ->whereIn('id_pelanggan', $client_ids)
            ->whereNotNull('no_telp')
            ->where('no_telp', '!=', '')
            ->get();

        if ($pelangganList->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada pelanggan terpilih yang memiliki nomor telepon aktif.'
            ], 400);
        }

        $results = [];
        $berhasil = 0;
        $gagal = 0;

        foreach ($pelangganList as $index => $pelanggan) {
            // Jeda pengiriman 10 detik (kecuali pesan pertama)
            if ($index > 0) {
                sleep(10);
            }

            // Format dinamis
            $odpName = $pelanggan->odpDetail->nama_odp ?? 'N/A';
            $customPesan = str_replace(
                ['$nama', '$pelanggan', '$odp', '$odc'],
                [$pelanggan->nama_pelanggan, $pelanggan->nama_pelanggan, $odpName, $odc->nama_odc],
                $pesan
            );

            try {
                $response = Http::timeout(10)->withHeaders([
                    'Authorization' => $tokenInfo->token
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $pelanggan->no_telp,
                    'message' => $customPesan,
                    'countryCode' => '62'
                ]);

                $resData = $response->json();
                if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                    $berhasil++;
                    $results[] = [
                        'status' => true,
                        'nama' => $pelanggan->nama_pelanggan,
                        'no_telp' => $pelanggan->no_telp,
                        'message' => 'Terkirim'
                    ];
                } else {
                    $gagal++;
                    $reason = $resData['reason'] ?? $resData['message'] ?? 'Fonnte error atau device offline.';
                    $results[] = [
                        'status' => false,
                        'nama' => $pelanggan->nama_pelanggan,
                        'no_telp' => $pelanggan->no_telp,
                        'message' => $reason
                    ];
                }
            } catch (\Exception $e) {
                $gagal++;
                Log::error("Broadcast WA ODC error to {$pelanggan->nama_pelanggan}: " . $e->getMessage());
                $results[] = [
                    'status' => false,
                    'nama' => $pelanggan->nama_pelanggan,
                    'no_telp' => $pelanggan->no_telp,
                    'message' => 'Koneksi API Gagal: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'message' => 'Pengiriman notifikasi ODC selesai.'
        ]);
    }

    public function deleteAnnouncement($id)
    {
        $info = Informasi::findOrFail($id);
        $info->delete();
        return redirect()->route('admin.broadcast.index')->with('success', 'Pengumuman aplikasi berhasil dihapus!');
    }

    public function fetchNotifications()
    {
        $notifications = [];

        // 1. Pending installation orders (permintaan baru dari sales)
        $pendingOrders = \App\Models\OrderPemasangan::with('sales')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        foreach ($pendingOrders as $order) {
            $notifications[] = [
                'id' => 'order_pending_' . $order->id,
                'type' => 'order_pending',
                'title' => 'Permintaan Pasang Baru',
                'description' => 'Sales ' . ($order->sales->nama_user ?? 'Sales') . ' mengajukan pemasangan baru untuk ' . $order->nama,
                'url' => route('admin.order_pemasangan.index'),
                'timestamp' => $order->created_at ? $order->created_at->toISOString() : now()->toISOString(),
                'time_human' => $order->created_at ? $order->created_at->diffForHumans() : 'Baru saja'
            ];
        }

        // 2. Installed orders waiting for admin approval (acc selesai pemasangan oleh teknisi)
        $installedOrders = \App\Models\OrderPemasangan::with('teknisi')
            ->where('status', 'installed')
            ->orderBy('updated_at', 'desc')
            ->get();
        foreach ($installedOrders as $order) {
            $notifications[] = [
                'id' => 'order_installed_' . $order->id,
                'type' => 'order_installed',
                'title' => 'Konfirmasi Selesai Pemasangan',
                'description' => 'Teknisi ' . ($order->teknisi->nama_user ?? 'Teknisi') . ' menyelesaikan pemasangan untuk ' . $order->nama,
                'url' => route('admin.order_pemasangan.index'),
                'timestamp' => $order->updated_at ? $order->updated_at->toISOString() : now()->toISOString(),
                'time_human' => $order->updated_at ? $order->updated_at->diffForHumans() : 'Baru saja'
            ];
        }

        // 3. New complaints (keluhan baru dari pelanggan)
        $complaints = \App\Models\Keluhan::with('pelanggan')
            ->where('status_keluhan', 'menunggu')
            ->orderBy('tanggal', 'desc')
            ->get();
        foreach ($complaints as $complaint) {
            $timestamp = \Carbon\Carbon::parse($complaint->tanggal);
            $notifications[] = [
                'id' => 'complaint_' . $complaint->id_keluhan,
                'type' => 'complaint',
                'title' => 'Keluhan Baru',
                'description' => ($complaint->pelanggan->nama_pelanggan ?? 'Pelanggan') . ': ' . $complaint->judul_keluhan,
                'url' => route('admin.keluhan.index'),
                'timestamp' => $timestamp->toISOString(),
                'time_human' => $timestamp->diffForHumans()
            ];
        }

        // Sort by timestamp descending
        usort($notifications, function ($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }
}

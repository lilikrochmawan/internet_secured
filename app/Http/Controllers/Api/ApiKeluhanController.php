<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Keluhan;
use App\Models\Pelanggan;
use App\Services\FonnteWhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiKeluhanController extends Controller
{
    public function __construct(
        private FonnteWhatsAppService $fonnteWhatsApp
    ) {
    }

    public function index()
    {
        $pelanggan = Auth::user()->pelanggan;

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan.'
            ], 404);
        }

        $keluhanList = Keluhan::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderByDesc('tanggal')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $keluhanList
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $pelanggan = $user->pelanggan;

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Pelanggan tidak ditemukan.'
            ], 404);
        }

        // Validasi input
        $request->validate([
            'judul_keluhan' => ['required', 'string', 'max:50'],
            'isi_keluhan' => ['required', 'string'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ], [
            'judul_keluhan.required' => 'Keluhan wajib diisi.',
            'judul_keluhan.max' => 'Keluhan maksimal 50 karakter.',
            'isi_keluhan.required' => 'Detail keluhan wajib diisi.',
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        // Cek jika ada keluhan aktif
        $keluhanAktif = Keluhan::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->whereIn('status_keluhan', ['menunggu', 'proses'])
            ->orderByDesc('tanggal')
            ->exists();

        if ($keluhanAktif) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada laporan yang menunggu atau sedang diproses. Harap tunggu hingga selesai.'
            ], 422);
        }

        $gambarName = '';
        if ($request->hasFile('gambar')) {
            $uploadDir = $this->keluhanImageDirectory();
            $file = $request->file('gambar');
            $gambarName = uniqid() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $gambarName);
        }

        $nomorTiket = Keluhan::generateNomorTiket('pelanggan', $pelanggan->id_pelanggan);

        $keluhan = Keluhan::create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'judul_keluhan' => $request->judul_keluhan,
            'nomor_tiket' => $nomorTiket,
            'isi_keluhan' => $request->isi_keluhan,
            'gambar' => $gambarName,
            'no_wa' => preg_replace('/[^0-9]/', '', $pelanggan->no_telp ?? ''),
            'status_keluhan' => 'menunggu',
            'tanggal' => now()->format('Y-m-d H:i:s'),
            'user_id' => $user->id,
        ]);

        // Kirim WhatsApp notification ke pelanggan
        $waTerkirim = $this->fonnteWhatsApp->send(
            $pelanggan->no_telp ?? '',
            $this->buildPelangganWhatsAppMessage($pelanggan, $nomorTiket)
        );

        // Kirim WhatsApp notification ke Admin / NOC
        $staffList = \App\Models\User::whereIn('level', ['admin', 'noc'])->get();
        $pesanAdmin = "🔔 *TIKET GANGGUAN BARU DARI PELANGGAN*\n\n"
                    . "Halo Rekan Admin/NOC,\n"
                    . "Ada laporan tiket keluhan baru dari pelanggan:\n\n"
                    . "• *Nomor Tiket:* #{$nomorTiket}\n"
                    . "• *Nama Pelanggan:* {$pelanggan->nama_pelanggan} (" . ($pelanggan->kode_pelanggan ?? 'N/A') . ")\n"
                    . "• *Keluhan:* {$request->judul_keluhan}\n"
                    . "• *Detail:* {$request->isi_keluhan}\n\n"
                    . "Silakan login ke panel untuk menugaskan teknisi atau memproses tiket ini. Terima kasih!";

        foreach ($staffList as $staff) {
            if (!empty($staff->phone_number)) {
                $this->fonnteWhatsApp->send($staff->phone_number, $pesanAdmin);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan keluhan berhasil dikirim.',
            'data' => $keluhan,
            'wa_notification' => $waTerkirim
        ]);
    }

    private function keluhanImageDirectory(): string
    {
        $path = base_path('administrator/page/keluhan/images');

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    private function buildPelangganWhatsAppMessage(Pelanggan $pelanggan, string $nomorTiket): string
    {
        return "Hai Pelanggan {$pelanggan->nama_pelanggan}\n"
            . "Nomor pengaduan anda sudah dibuat dengan nomor ticket {$nomorTiket} mohon menunggu admin akan membalas secepatnya. Mohon untuk tunggu informasi lebih lanjut, Terima Kasih.\n\n"
            . 'Jangan Balas Pesan Ini Pesan Otomatis';
    }
}

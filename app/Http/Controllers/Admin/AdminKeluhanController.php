<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keluhan;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminKeluhanController extends Controller
{
    public function index()
    {
        // Ambil data keluhan beserta data pelanggan terkait (scoped by branch access)
        $keluhan = Keluhan::with(['pelanggan'])
            ->whereIn('id_pelanggan', Pelanggan::allowedForUser()->pluck('id_pelanggan'))
            ->orderBy('id_keluhan', 'desc')
            ->get();
        return view('admin.keluhan.index', compact('keluhan'));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'id_keluhan' => 'required|integer',
        ]);

        $keluhan = Keluhan::findOrFail($request->id_keluhan);
        
        // Ubah status keluhan menjadi proses
        $keluhan->update([
            'status_keluhan' => 'proses',
            'user_id' => Auth::id(), // Staff yang memproses keluhan
        ]);

        return redirect()->route('admin.keluhan.index')->with('success', 'Keluhan berhasil diperbarui ke status: Proses!');
    }

    public function selesai(Request $request)
    {
        $request->validate([
            'id_keluhan' => 'required|integer',
            'masalah' => 'required|string',
        ]);

        $keluhan = Keluhan::with('pelanggan')->findOrFail($request->id_keluhan);
        
        // Ubah status keluhan menjadi selesai dan catat masalah/penyebab
        $keluhan->update([
            'status_keluhan' => 'selesai',
            'masalah' => htmlspecialchars(strip_tags($request->masalah)),
            'user_id' => Auth::id(), // Staff yang menyelesaikan keluhan
        ]);

        // Kirim Notifikasi WhatsApp ke Client via Fonnte API
        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->first();
        $waSent = false;
        
        if ($tokenInfo && !empty($tokenInfo->token) && !empty($keluhan->no_wa)) {
            $nama_pelanggan = $keluhan->pelanggan->nama_pelanggan ?? 'Pelanggan';
            $pesan = "Halo Bapak/Ibu *{$nama_pelanggan}*\n\n"
                   . "Laporan gangguan Anda dengan Nomor Tiket *#{$keluhan->nomor_tiket}* dan keluhan *{$keluhan->judul_keluhan}* telah berhasil diselesaikan oleh petugas kami.\n\n"
                   . "*Detail Penyebab / Solusi:*\n"
                   . "{$keluhan->masalah}\n\n"
                   . "Terimakasih atas kepercayaan Anda menggunakan layanan internet kami.";

            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => $tokenInfo->token
                ])->asForm()->post('https://api.fonnte.com/send', [
                    'target' => $keluhan->no_wa,
                    'message' => $pesan,
                    'countryCode' => '62'
                ]);
                
                $resData = $response->json();
                if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                    $waSent = true;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Fonnte API Error Keluhan Selesai: ' . $e->getMessage());
            }
        }

        $successMsg = 'Keluhan berhasil diselesaikan dan dicatat penyebab/masalahnya!';
        if ($waSent) {
            $successMsg .= ' Notifikasi WhatsApp telah dikirim ke pelanggan.';
        }

        return redirect()->route('admin.keluhan.index')->with('success', $successMsg);
    }

    public function showGambar($filename)
    {
        $path = base_path('administrator/page/keluhan/images/' . $filename);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->file($path);
    }

    public function printReport(Request $request)
    {
        $tipe = $request->get('tipe', 'bulanan');
        $status = $request->get('status', 'semua');
        
        $query = Keluhan::with('pelanggan')
            ->whereIn('id_pelanggan', Pelanggan::allowedForUser()->pluck('id_pelanggan'))
            ->orderBy('tanggal', 'asc');
        $title = 'Laporan Keluhan Pelanggan';

        // Filter Status
        if ($status !== 'semua') {
            $query->where('status_keluhan', $status);
        }

        // Filter Period
        if ($tipe === 'harian') {
            $tanggal = $request->get('tanggal', date('Y-m-d'));
            $query->whereDate('tanggal', $tanggal);
            $title = 'Laporan Keluhan Harian (' . ($status === 'semua' ? 'Semua Status' : ucfirst($status)) . ') - ' . \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($tipe === 'mingguan') {
            $tgl_mulai = $request->get('tgl_mulai', date('Y-m-d', strtotime('-6 days')));
            $tgl_selesai = $request->get('tgl_selesai', date('Y-m-d'));
            $query->whereBetween('tanggal', [$tgl_mulai . ' 00:00:00', $tgl_selesai . ' 23:59:59']);
            $title = 'Laporan Keluhan Mingguan (' . ($status === 'semua' ? 'Semua Status' : ucfirst($status)) . ') - ' . \Carbon\Carbon::parse($tgl_mulai)->translatedFormat('d F Y') . ' s/d ' . \Carbon\Carbon::parse($tgl_selesai)->translatedFormat('d F Y');
        } elseif ($tipe === 'bulanan') {
            $bulan = $request->get('bulan', date('m'));
            $tahun = $request->get('tahun_bulan', date('Y'));
            $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            $title = 'Laporan Keluhan Bulanan (' . ($status === 'semua' ? 'Semua Status' : ucfirst($status)) . ') - ' . \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') . ' ' . $tahun;
        } elseif ($tipe === 'tahunan') {
            $tahun = $request->get('tahun', date('Y'));
            $query->whereYear('tanggal', $tahun);
            $title = 'Laporan Keluhan Tahunan (' . ($status === 'semua' ? 'Semua Status' : ucfirst($status)) . ') - ' . $tahun;
        }

        $keluhan = $query->get();
        
        $profile = DB::table('tb_profile')->first();
        if ($profile && !isset($profile->telepon)) {
            $profile->telepon = $profile->telpon ?? '';
        }

        return view('admin.keluhan.print', compact('keluhan', 'title', 'profile', 'tipe', 'status'));
    }
}


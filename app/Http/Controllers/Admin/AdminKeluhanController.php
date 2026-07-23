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
        $user = Auth::user();
        
        $query = Keluhan::with(['pelanggan', 'teknisi']);
        
        if (in_array($user->level, ['mitra', 'sales'])) {
            $query->where(function($q) use ($user) {
                // Rule 1: Tiket yang dibuatnya sendiri (termasuk maintenance / id_pelanggan null)
                $q->where('tbl_keluhan.user_id', $user->id)
                // Rule 2: Tiket yang dibuat oleh pelanggan di branch/sub branch sesuai aksesnya
                ->orWhere(function($subQ) {
                    $subQ->whereIn('tbl_keluhan.id_pelanggan', Pelanggan::allowedForUser()->pluck('id_pelanggan'))
                         ->whereIn('tbl_keluhan.user_id', function($uq) {
                             $uq->select('id')->from('tb_user')->where('level', 'user');
                         });
                });
            });
        } else {
            $query->where(function($q) {
                $q->whereIn('id_pelanggan', Pelanggan::allowedForUser()->pluck('id_pelanggan'))
                  ->orWhereNull('id_pelanggan');
            });
        }
            
        // Filter tickets if user is a technician
        if ($user->level === 'teknisi') {
            $query->where(function($q) use ($user) {
                $q->where('teknisi_id', $user->id)
                  ->orWhere('assign_to_all', 1);
            })->where('status_keluhan', '!=', 'selesai');
        }
        
        $keluhan = $query->orderBy('id_keluhan', 'desc')->get();
        
        // Fetch list of technicians and pelanggan (for admin/noc/mitra to create tickets)
        $teknisiList = [];
        $pelangganList = [];
        if (in_array($user->level, ['admin', 'noc', 'mitra'])) {
            $teknisiList = \App\Models\User::where('level', 'teknisi')->get();
            $pelangganList = \App\Models\Pelanggan::allowedForUser()->orderBy('nama_pelanggan')->get();
        }
        
        return view('admin.keluhan.index', compact('keluhan', 'teknisiList', 'pelangganList'));
    }

    public function proses(Request $request)
    {
        $request->validate([
            'id_keluhan' => 'required|integer',
            'assign_type' => 'required|in:all,specific,self',
            'teknisi_id' => 'required_if:assign_type,specific|nullable|integer',
        ]);

        $keluhan = Keluhan::with('pelanggan')->findOrFail($request->id_keluhan);
        
        $waNotificationsSent = 0;
        
        if ($request->assign_type === 'all') {
            $keluhan->update([
                'status_keluhan' => 'proses',
                'assign_to_all' => 1,
                'teknisi_id' => null,
            ]);
            $msg = 'Tiket berhasil diproses dan ditugaskan ke semua teknisi!';
            
            // Notify all technicians
            $teknisiList = \App\Models\User::where('level', 'teknisi')->get();
            $pesan = "📢 *TUGAS TIKET BARU (SEMUA TEKNISI)*\n\n"
                   . "Halo Rekan Teknisi,\n"
                   . "Ada tiket gangguan baru yang ditugaskan untuk *Semua Teknisi*:\n\n"
                   . "• *Nomor Tiket:* #{$keluhan->nomor_tiket}\n"
                   . "• *Keluhan/Masalah:* {$keluhan->judul_keluhan}\n"
                   . "• *Detail:* {$keluhan->isi_keluhan}\n"
                   . "• *Pelanggan/Lokasi:* " . ($keluhan->id_pelanggan ? ($keluhan->pelanggan->nama_pelanggan ?? 'N/A') : 'Internal / Maintenance') . "\n\n"
                   . "Silakan segera diambil tindakan dan laporkan jika sudah selesai. Terima kasih!";
            
            foreach ($teknisiList as $t) {
                if (!empty($t->phone_number)) {
                    if ($this->sendWhatsAppNotification($t->phone_number, $pesan)) {
                        $waNotificationsSent++;
                    }
                }
            }
            if ($waNotificationsSent > 0) {
                $msg .= " Notifikasi WhatsApp terkirim ke {$waNotificationsSent} teknisi.";
            }
        } elseif ($request->assign_type === 'self') {
            $keluhan->update([
                'status_keluhan' => 'proses',
                'assign_to_all' => 0,
                'teknisi_id' => Auth::id(),
            ]);
            $msg = 'Tiket berhasil diproses oleh Anda sendiri!';
        } else {
            $keluhan->update([
                'status_keluhan' => 'proses',
                'assign_to_all' => 0,
                'teknisi_id' => $request->teknisi_id,
            ]);
            $msg = 'Teknisi berhasil ditugaskan dan status tiket diubah ke Proses!';
            
            // Notify specific technician
            $teknisi = \App\Models\User::find($request->teknisi_id);
            if ($teknisi && !empty($teknisi->phone_number)) {
                $pesan = "📢 *TUGAS TIKET BARU*\n\n"
                       . "Halo *{$teknisi->nama_user}*,\n"
                       . "Ada tiket gangguan baru yang ditugaskan khusus kepada Anda:\n\n"
                       . "• *Nomor Tiket:* #{$keluhan->nomor_tiket}\n"
                       . "• *Keluhan/Masalah:* {$keluhan->judul_keluhan}\n"
                       . "• *Detail:* {$keluhan->isi_keluhan}\n"
                       . "• *Pelanggan/Lokasi:* " . ($keluhan->id_pelanggan ? ($keluhan->pelanggan->nama_pelanggan ?? 'N/A') : 'Internal / Maintenance') . "\n\n"
                       . "Silakan segera diproses dan laporkan jika sudah selesai. Terima kasih!";
                
                if ($this->sendWhatsAppNotification($teknisi->phone_number, $pesan)) {
                    $msg .= " Notifikasi WhatsApp terkirim ke teknisi.";
                }
            }
        }

        return redirect()->route('admin.keluhan.index')->with('success', $msg);
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
        ]);

        // Kirim Notifikasi WhatsApp ke Client via Fonnte API
        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
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
        
        $user = Auth::user();
        $query = Keluhan::with('pelanggan')
            ->orderBy('tanggal', 'asc');

        if ($user->level === 'teknisi') {
            $query->where(function($q) use ($user) {
                $q->where('teknisi_id', $user->id)
                  ->orWhere('assign_to_all', 1);
            });
            $title = 'Laporan Keluhan & Tiket Anda (' . $user->nama_user . ')';
        } elseif (in_array($user->level, ['mitra', 'sales'])) {
            $query->where(function($q) use ($user) {
                // Rule 1: Tiket yang dibuatnya sendiri (termasuk maintenance / id_pelanggan null)
                $q->where('tbl_keluhan.user_id', $user->id)
                // Rule 2: Tiket yang dibuat oleh pelanggan di branch/sub branch sesuai aksesnya
                ->orWhere(function($subQ) {
                    $subQ->whereIn('tbl_keluhan.id_pelanggan', Pelanggan::allowedForUser()->pluck('id_pelanggan'))
                         ->whereIn('tbl_keluhan.user_id', function($uq) {
                             $uq->select('id')->from('tb_user')->where('level', 'user');
                         });
                });
            });
            $title = 'Laporan Keluhan & Tiket';
        } else {
            $query->where(function($q) {
                $q->whereIn('id_pelanggan', Pelanggan::allowedForUser()->pluck('id_pelanggan'))
                  ->orWhereNull('id_pelanggan');
            });
            $title = 'Laporan Keluhan & Tiket';
        }

        // Filter Status
        if ($status !== 'semua') {
            $query->where('status_keluhan', $status);
        }

        // Filter Period
        if ($tipe === 'harian') {
            $tanggal = $request->get('tanggal', date('Y-m-d'));
            $query->whereDate('tanggal', $tanggal);
            $title = 'Laporan Keluhan & Tiket Harian (' . ($status === 'semua' ? 'Semua Status' : ucfirst($status)) . ') - ' . \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($tipe === 'mingguan') {
            $tgl_mulai = $request->get('tgl_mulai', date('Y-m-d', strtotime('-6 days')));
            $tgl_selesai = $request->get('tgl_selesai', date('Y-m-d'));
            $query->whereBetween('tanggal', [$tgl_mulai . ' 00:00:00', $tgl_selesai . ' 23:59:59']);
            $title = 'Laporan Keluhan & Tiket Mingguan (' . ($status === 'semua' ? 'Semua Status' : ucfirst($status)) . ') - ' . \Carbon\Carbon::parse($tgl_mulai)->translatedFormat('d F Y') . ' s/d ' . \Carbon\Carbon::parse($tgl_selesai)->translatedFormat('d F Y');
        } elseif ($tipe === 'bulanan') {
            $bulan = $request->get('bulan', date('m'));
            $tahun = $request->get('tahun_bulan', date('Y'));
            $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            $title = 'Laporan Keluhan & Tiket Bulanan (' . ($status === 'semua' ? 'Semua Status' : ucfirst($status)) . ') - ' . \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') . ' ' . $tahun;
        } elseif ($tipe === 'tahunan') {
            $tahun = $request->get('tahun', date('Y'));
            $query->whereYear('tanggal', $tahun);
            $title = 'Laporan Keluhan & Tiket Tahunan (' . ($status === 'semua' ? 'Semua Status' : ucfirst($status)) . ') - ' . $tahun;
        }

        $keluhan = $query->get();
        
        $profile = DB::table('tb_profile')->first();
        if ($profile && !isset($profile->telepon)) {
            $profile->telepon = $profile->telpon ?? '';
        }

        return view('admin.keluhan.print', compact('keluhan', 'title', 'profile', 'tipe', 'status'));
    }

    public function teknisiSelesai(Request $request)
    {
        $request->validate([
            'id_keluhan' => 'required|integer',
            'tindakan' => 'required|string',
            'bukti_foto' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ], [
            'tindakan.required' => 'Tindakan wajib diisi.',
            'bukti_foto.required' => 'Bukti foto wajib diunggah.',
            'bukti_foto.image' => 'File harus berupa gambar.',
            'bukti_foto.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        $keluhan = Keluhan::findOrFail($request->id_keluhan);
        $user = Auth::user();

        // Check permission: must be assigned specifically or as "all"
        if ($user->level === 'teknisi') {
            if ($keluhan->teknisi_id != $user->id && !$keluhan->assign_to_all) {
                return redirect()->route('admin.keluhan.index')->with('error', 'Anda tidak ditugaskan untuk tiket ini.');
            }
        }

        $gambarName = '';
        if ($request->hasFile('bukti_foto')) {
            $path = base_path('administrator/page/keluhan/images');
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $file = $request->file('bukti_foto');
            $gambarName = 'bukti_' . uniqid() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($path, $gambarName);
        }

        // If it was assigned to "All", now bind it to the technician who completed it
        $updateData = [
            'status_keluhan' => 'perlu_verifikasi',
            'tindakan' => htmlspecialchars(strip_tags($request->tindakan)),
            'bukti_foto' => $gambarName,
        ];

        if ($keluhan->assign_to_all) {
            $updateData['teknisi_id'] = $user->id;
            $updateData['assign_to_all'] = 0; // set back to 0 now that specific technician did it
        }

        $keluhan->update($updateData);

        return redirect()->route('admin.keluhan.index')->with('success', 'Pekerjaan selesai dilaporkan. Menunggu verifikasi admin/noc.');
    }

    public function verifikasi(Request $request)
    {
        $request->validate([
            'id_keluhan' => 'required|integer',
            'masalah' => 'required|string',
        ], [
            'masalah.required' => 'Penyebab Keluhan / Solusi wajib diisi.',
        ]);

        $keluhan = Keluhan::with('pelanggan')->findOrFail($request->id_keluhan);

        // Ubah status keluhan menjadi selesai dan catat masalah/penyebab
        $keluhan->update([
            'status_keluhan' => 'selesai',
            'masalah' => htmlspecialchars(strip_tags($request->masalah)),
        ]);

        // Kirim Notifikasi WhatsApp ke Client via Fonnte API
        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
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
                \Illuminate\Support\Facades\Log::error('Fonnte API Error Keluhan Verifikasi Selesai: ' . $e->getMessage());
            }
        }

        $successMsg = 'Tiket berhasil diverifikasi dan diselesaikan!';
        if ($waSent) {
            $successMsg .= ' Notifikasi WhatsApp telah dikirim ke pelanggan.';
        }

        return redirect()->route('admin.keluhan.index')->with('success', $successMsg);
    }

    public function storeTicket(Request $request)
    {
        $request->validate([
            'tipe_tiket' => 'required|in:pelanggan,internal',
            'id_pelanggan' => 'required_if:tipe_tiket,pelanggan|nullable|integer',
            'judul_keluhan' => 'required|string|max:50',
            'isi_keluhan' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ], [
            'tipe_tiket.required' => 'Tipe tiket wajib dipilih.',
            'id_pelanggan.required_if' => 'Pelanggan wajib dipilih jika tipe tiket adalah pelanggan.',
            'judul_keluhan.required' => 'Keluhan/Masalah wajib diisi.',
            'judul_keluhan.max' => 'Keluhan maksimal 50 karakter.',
            'isi_keluhan.required' => 'Detail keluhan/pekerjaan wajib diisi.',
            'gambar.image' => 'File harus berupa gambar.',
            'gambar.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        $pelanggan = null;
        $idPelanggan = null;
        $noWa = null;

        if ($request->tipe_tiket === 'pelanggan') {
            $pelanggan = \App\Models\Pelanggan::findOrFail($request->id_pelanggan);
            $idPelanggan = $pelanggan->id_pelanggan;
            $noWa = preg_replace('/[^0-9]/', '', $pelanggan->no_telp ?? '');
        }

        // Upload photo
        $gambarName = '';
        if ($request->hasFile('gambar')) {
            $path = base_path('administrator/page/keluhan/images');
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            $file = $request->file('gambar');
            $gambarName = uniqid() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move($path, $gambarName);
        }

        // Generate nomor tiket
        $nomorTiket = Keluhan::generateNomorTiket($request->tipe_tiket, $idPelanggan);

        Keluhan::create([
            'id_pelanggan' => $idPelanggan,
            'judul_keluhan' => $request->judul_keluhan,
            'nomor_tiket' => $nomorTiket,
            'isi_keluhan' => $request->isi_keluhan,
            'gambar' => $gambarName,
            'no_wa' => $noWa,
            'status_keluhan' => 'menunggu',
            'tanggal' => now()->format('Y-m-d H:i:s'),
            'user_id' => Auth::id(), // Staff who created the ticket
        ]);

        $waSent = false;
        // Kirim WhatsApp notification ke pelanggan
        if ($pelanggan && !empty($pelanggan->no_telp)) {
            $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
            
            if ($tokenInfo && !empty($tokenInfo->token)) {
                $nama_pelanggan = $pelanggan->nama_pelanggan ?? 'Pelanggan';
                $pesan = "Hai Pelanggan {$nama_pelanggan}\n"
                    . "Nomor pengaduan anda sudah dibuat oleh petugas NOC kami dengan nomor ticket {$nomorTiket} mohon menunggu teknisi akan segera memproses. Terima Kasih.\n\n"
                    . "Jangan Balas Pesan Ini Pesan Otomatis";

                try {
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'Authorization' => $tokenInfo->token
                    ])->asForm()->post('https://api.fonnte.com/send', [
                        'target' => $pelanggan->no_telp,
                        'message' => $pesan,
                        'countryCode' => '62'
                    ]);
                    $resData = $response->json();
                    if ($response->successful() && isset($resData['status']) && $resData['status'] === true) {
                        $waSent = true;
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Fonnte API Error Keluhan Dibuat NOC: ' . $e->getMessage());
                }
            }
        }

        $successMsg = $request->tipe_tiket === 'internal' 
            ? 'Tiket internal/maintenance berhasil dibuat!' 
            : 'Tiket pengaduan pelanggan berhasil dibuat!';
            
        if ($waSent) {
            $successMsg .= ' Notifikasi WhatsApp telah dikirim ke pelanggan.';
        }

        return redirect()->route('admin.keluhan.index')->with('success', $successMsg);
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


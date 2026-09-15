<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WabaChat;
use App\Models\Pelanggan;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AdminWabaChatController extends Controller
{
    public function index(Request $request)
    {
        // Get unique contacts with their latest message
        $contacts = DB::table('tbl_waba_chat')
            ->select(
                'no_telp', 
                'nama', 
                DB::raw('MAX(created_at) as last_message_time'),
                DB::raw("SUM(CASE WHEN tipe = 'incoming' AND status = 'received' THEN 1 ELSE 0 END) as unread_count")
            )
            ->groupBy('no_telp', 'nama')
            ->orderBy('last_message_time', 'desc')
            ->get();
            
        // For each contact, we might want the latest message text
        foreach ($contacts as $contact) {
            $latestMsg = WabaChat::where('no_telp', $contact->no_telp)
                ->orderBy('created_at', 'desc')
                ->first();
            $contact->latest_pesan = $latestMsg ? $latestMsg->pesan : '';
        }

        return view('admin.waba_chat.index', compact('contacts'));
    }
    
    public function loadMessages($no_telp)
    {
        // Mark messages as read
        WabaChat::where('no_telp', $no_telp)
            ->where('tipe', 'incoming')
            ->where('status', 'received')
            ->update(['status' => 'read']);
            
        $messages = WabaChat::where('no_telp', $no_telp)
            ->orderBy('created_at', 'asc') // chronological order
            ->get();
            
        $nama = $messages->first()->nama ?? 'Tidak Dikenal';
        if ($nama === 'Tidak Dikenal') {
            $shortPhone = substr($no_telp, -9);
            $pelanggan = Pelanggan::where('no_telp', 'like', "%{$shortPhone}%")->first();
            if ($pelanggan) $nama = $pelanggan->nama_pelanggan;
        }
            
        return response()->json([
            'status' => 'success',
            'contact' => [
                'no_telp' => $no_telp,
                'nama' => $nama
            ],
            'messages' => $messages
        ]);
    }
    
    public function reply(Request $request)
    {
        $request->validate([
            'no_telp' => 'required|string',
            'pesan' => 'nullable|string',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,pdf,mp4|max:10240'
        ]);
        
        $no_telp = $request->input('no_telp');
        $pesan = $request->input('pesan') ?? '';
        
        // Find name if possible
        $shortPhone = substr($no_telp, -9);
        $pelanggan = Pelanggan::where('no_telp', 'like', "%{$shortPhone}%")->first();
        $nama = $pelanggan ? $pelanggan->nama_pelanggan : 'Tidak Dikenal';
        
        $mediaUrl = null;
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/chat'), $filename);
            $mediaUrl = asset('uploads/chat/' . $filename);
        }

        // Send using WA Service
        $waService = app(WhatsAppService::class);
        $response = $waService->sendMessage($no_telp, $pesan, $mediaUrl);
        
        // Save to DB
        $chat = WabaChat::create([
            'no_telp' => $no_telp,
            'nama' => $nama,
            'pesan' => $pesan,
            'media_url' => $mediaUrl,
            'tipe' => 'outgoing',
            'status' => 'sent',
            'read_status' => 1
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Pesan terkirim',
            'data' => $chat
        ]);
    }

    public function fetchMedia(Request $request)
    {
        $url = $request->query('url');
        if (!$url) {
            return $this->returnPlaceholderSvg('URL media tidak ditemukan.');
        }

        if (!str_starts_with($url, 'http')) {
            return $this->returnPlaceholderSvg('Format Media WABA Terkunci');
        }

        $tokenInfo = DB::table('tbl_token')->where('id_token', 1)->where('status', 'aktif')->first();
        if (!$tokenInfo) {
            return $this->returnPlaceholderSvg('Token Gateway Belum Dikonfigurasi');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $tokenInfo->token
            ])->get($url);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', $response->header('Content-Type'));
            } else if ($response->status() == 401) {
                return $this->returnPlaceholderSvg('Akses Ditolak (Token Meta Tidak Valid)');
            } else {
                return $this->returnPlaceholderSvg('Gagal Mengunduh Media (Status: ' . $response->status() . ')');
            }
        } catch (\Exception $e) {
            return $this->returnPlaceholderSvg('Koneksi Gagal: ' . substr($e->getMessage(), 0, 50));
        }
    }

    private function returnPlaceholderSvg($message)
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="400" height="200" xmlns="http://www.w3.org/2000/svg">
  <rect width="100%" height="100%" fill="#f8d7da"/>
  <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="16" fill="#721c24" text-anchor="middle" dominant-baseline="middle">
    ' . htmlspecialchars($message) . '
  </text>
  <text x="50%" y="70%" font-family="Arial, sans-serif" font-size="12" fill="#721c24" text-anchor="middle" dominant-baseline="middle">
    (Hubungi Penyedia Layanan / Bablast)
  </text>
</svg>';

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }
}

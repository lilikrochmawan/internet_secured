<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use App\Models\WabaChat;
use App\Models\WebhookAutoreply;
use App\Services\WhatsAppService;

class BablastWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Tangkap seluruh payload dari Bablast
        $payload = $request->all();
        
        // Cek Event
        $event = $payload['event'] ?? null;
        
        if (in_array($event, ['messages_incoming', 'incoming_message'])) {
            return $this->handleIncomingMessage($payload);
        } elseif (in_array($event, ['messages_status', 'message_status'])) {
            return $this->handleMessageStatus($payload);
        }
        
        // Log event lain yang mungkin terjadi
        Log::info('Bablast Webhook (Other Event):', $payload);
        return response()->json(['status' => 'ignored']);
    }

    private function handleIncomingMessage(array $payload)
    {
        // LOG FULL PAYLOAD FOR DEBUGGING MEDIA
        file_put_contents(storage_path('logs/bablast_payload.log'), json_encode($payload) . "\n\n", FILE_APPEND);

        // Ekstrak pengirim dan pesan
        // Bablast WABA payload format
        $from = $payload['data']['from_phone'] ?? $payload['data']['from'] ?? $payload['from'] ?? $payload['phone'] ?? null;
        $messageText = $payload['data']['content'] ?? $payload['data']['message']['text']['body'] ?? $payload['data']['message'] ?? $payload['message'] ?? $payload['text'] ?? '';
        
        if (!$from) {
            Log::warning('Bablast Webhook: Pengirim tidak ditemukan', $payload);
            return response()->json(['error' => 'No sender phone']);
        }
        
        // Standarisasi nomor telepon
        $from = preg_replace('/[^0-9]/', '', $from);
        
        // Extract Media URL if present. Prioritize HTTP links.
        $mediaUrls = [
            $payload['data']['media_url'] ?? null,
            $payload['data']['file_url'] ?? null,
            $payload['data']['message']['image']['link'] ?? null,
            $payload['data']['message']['document']['link'] ?? null,
            $payload['url'] ?? null,
            $payload['media'] ?? null,
            $payload['data']['url'] ?? null,
        ];
        
        $mediaUrl = null;
        foreach ($mediaUrls as $url) {
            if ($url && is_string($url) && str_starts_with(strtolower($url), 'http')) {
                $mediaUrl = $url;
                break;
            }
        }
        
        // Fallback if no HTTP url found
        if (!$mediaUrl) {
            $mediaUrl = $payload['data']['url'] ?? $payload['data']['media_url'] ?? null;
        }

        if (empty($messageText) && $mediaUrl) {
            $messageText = '[Gambar/Media]';
        }

        Log::info("Bablast Incoming dari {$from}: {$messageText}");
        
        $shortPhone = substr($from, -9);
        $pelanggan = Pelanggan::where('no_telp', 'like', "%{$shortPhone}%")->first();
        
        // Simpan ke database
        WabaChat::create([
            'no_telp' => $from,
            'nama' => $pelanggan ? $pelanggan->nama_pelanggan : 'Tidak Dikenal',
            'pesan' => $messageText,
            'media_url' => $mediaUrl,
            'tipe' => 'incoming',
            'status' => 'received',
        ]);
        
        // Deteksi Kata Kunci
        $messageUpper = strtoupper(trim($messageText));
        
        $templates = WebhookAutoreply::where('status_aktif', true)->get();
        $matchedTipe = null;
        
        foreach ($templates as $template) {
            if (empty($template->keyword)) continue;
            
            $keywords = array_map('trim', explode(',', strtoupper($template->keyword)));
            foreach ($keywords as $kw) {
                if (empty($kw)) continue;
                if (str_contains($messageUpper, $kw)) {
                    $matchedTipe = $template->tipe;
                    // For tagihan, regardless of lunas/tunggak, the keyword is matched to tagihan_lunas
                    if ($matchedTipe === 'tagihan_lunas' || $matchedTipe === 'tagihan_tunggak') {
                        $matchedTipe = 'tagihan';
                    }
                    break 2;
                }
            }
        }
        
        if ($matchedTipe === 'halo') {
            $this->autoReplyHalo($from, $pelanggan);
        } elseif ($matchedTipe === 'tagihan') {
            $this->autoReplyTagihan($from, $pelanggan);
        } elseif ($matchedTipe === 'paket_internet') {
            $this->autoReplyPaket($from, $pelanggan);
        } elseif ($matchedTipe && str_starts_with($matchedTipe, 'custom_')) {
            $this->autoReplyCustom($from, $pelanggan, $matchedTipe);
        }
        
        return response()->json(['status' => 'success']);
    }
    
    private function handleMessageStatus(array $payload)
    {
        // Log status pengiriman pesan (sent, delivered, read, failed)
        // Tidak perlu membalas apapun
        $status = $payload['data']['status'] ?? 'unknown';
        $messageId = $payload['data']['messageId'] ?? 'unknown';
        
        Log::info("Bablast Message Status [{$messageId}]: {$status}");
        
        return response()->json(['status' => 'success']);
    }
    
    private function autoReplyTagihan(string $from, ?Pelanggan $pelanggan)
    {
        if (!$pelanggan) {
            $this->sendAndLogReply($from, "Maaf, nomor Anda tidak terdaftar dalam sistem kami. Silakan hubungi admin untuk bantuan.");
            return;
        }
        
        $tagihanBelumBayar = Tagihan::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where(function ($q) {
                $q->whereNull('status_bayar')
                  ->orWhereIn('status_bayar', [0, '0', 'belum', '']);
            })
            ->orderBy('bulan_tahun', 'asc')
            ->get();
            
        if ($tagihanBelumBayar->isEmpty()) {
            $template = WebhookAutoreply::where('tipe', 'tagihan_lunas')->first();
            if ($template && $template->status_aktif) {
                $pesan = str_replace('{nama}', $pelanggan->nama_pelanggan, $template->pesan);
                $this->sendAndLogReply($from, $pesan, $pelanggan->nama_pelanggan);
            }
            return;
        }
        
        $template = WebhookAutoreply::where('tipe', 'tagihan_tunggak')->first();
        if ($template && $template->status_aktif) {
            $listTagihan = "";
            $totalTunggakan = 0;
            
            foreach ($tagihanBelumBayar as $idx => $tgh) {
                $bulanTahun = $tgh->bulan_tahun ?? '-';
                $nominal = "Rp " . number_format($tgh->jml_bayar, 0, ',', '.');
                $listTagihan .= ($idx + 1) . ". Bulan: {$bulanTahun} - Tagihan: {$nominal}\n";
                $totalTunggakan += $tgh->jml_bayar;
            }
            
            $totalFormatted = "Rp " . number_format($totalTunggakan, 0, ',', '.');
            
            $pesan = $template->pesan;
            $pesan = str_replace('{nama}', $pelanggan->nama_pelanggan, $pesan);
            $pesan = str_replace('{list_tagihan}', $listTagihan, $pesan);
            $pesan = str_replace('{total_tunggakan}', $totalFormatted, $pesan);
            
            sleep(1);
            $this->sendAndLogReply($from, $pesan, $pelanggan->nama_pelanggan);
        }
    }
    
    private function autoReplyHalo(string $from, ?Pelanggan $pelanggan)
    {
        $template = WebhookAutoreply::where('tipe', 'halo')->first();
        if ($template && $template->status_aktif) {
            $nama = $pelanggan ? $pelanggan->nama_pelanggan : 'Pelanggan';
            $pesan = str_replace('{nama}', $nama, $template->pesan);
            $this->sendAndLogReply($from, $pesan, $nama);
        }
    }

    private function autoReplyPaket(string $from, ?Pelanggan $pelanggan)
    {
        $template = WebhookAutoreply::where('tipe', 'paket_internet')->first();
        if ($template && $template->status_aktif) {
            $nama = $pelanggan ? $pelanggan->nama_pelanggan : 'Kak';
            $pesan = str_replace('{nama}', $nama, $template->pesan);
            
            $mediaUrl = null;
            if ($template->media_path) {
                $mediaUrl = asset($template->media_path);
            }
            
            $this->sendAndLogReply($from, $pesan, $nama, $mediaUrl);
        }
    }

    private function autoReplyCustom(string $from, ?Pelanggan $pelanggan, string $tipe)
    {
        $template = WebhookAutoreply::where('tipe', $tipe)->first();
        if ($template && $template->status_aktif) {
            $nama = $pelanggan ? $pelanggan->nama_pelanggan : 'Kak';
            $pesan = str_replace('{nama}', $nama, $template->pesan);
            
            $mediaUrl = null;
            if ($template->media_path) {
                $mediaUrl = asset($template->media_path);
            }
            
            $this->sendAndLogReply($from, $pesan, $nama, $mediaUrl);
        }
    }

    private function sendAndLogReply(string $no_telp, string $pesan, string $nama = 'Tidak Dikenal', ?string $mediaUrl = null)
    {
        $waService = app(WhatsAppService::class);
        $waService->sendMessage($no_telp, $pesan, $mediaUrl);
        
        WabaChat::create([
            'no_telp' => $no_telp,
            'nama' => $nama,
            'pesan' => $pesan,
            'media_url' => $mediaUrl,
            'tipe' => 'outgoing',
            'status' => 'sent',
        ]);
    }
}

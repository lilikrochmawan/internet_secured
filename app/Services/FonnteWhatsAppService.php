<?php

namespace App\Services;

use App\Models\WaToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteWhatsAppService
{
    public function send(string $target, string $message): bool
    {
        $token = WaToken::find(1)?->token;

        if (!$token) {
            Log::warning('WA Fonnte: token tidak ditemukan di tbl_token.');

            return false;
        }

        $target = preg_replace('/[^0-9]/', '', $target);

        if ($target === '') {
            Log::warning('WA Fonnte: nomor tujuan kosong.');

            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->asMultipart()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            if (!$response->successful()) {
                Log::warning('WA Fonnte gagal dikirim.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::error('WA Fonnte error: ' . $exception->getMessage());

            return false;
        }
    }
}

<?php

namespace App\Services;

use App\Models\WaToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendMessage(string $target, string $message, ?string $mediaUrl = null): bool
    {
        $tokenInfo = WaToken::find(1);

        if (!$tokenInfo) {
            Log::warning('WhatsApp Gateway: Konfigurasi tidak ditemukan di tbl_token.');
            return false;
        }

        // Clean target number
        $target = preg_replace('/[^0-9]/', '', $target);
        if (str_starts_with($target, '0')) {
            $target = '62' . substr($target, 1);
        }

        if ($target === '') {
            Log::warning('WhatsApp Gateway: Nomor tujuan kosong.');
            return false;
        }

        $gateway = $tokenInfo->wa_gateway ?? 'fonnte';

        if ($gateway === 'bablast') {
            return $this->sendViaBablast($target, $message, $tokenInfo->bablast_token, $mediaUrl);
        } else {
            return $this->sendViaFonnte($target, $message, $tokenInfo->token, $mediaUrl);
        }
    }

    public function sendTemplateMessage(string $target, string $fallbackMessage, ?string $templateName = null, array $parameters = [], string $language = 'id', ?string $mediaUrl = null): bool
    {
        $tokenInfo = WaToken::find(1);

        if (!$tokenInfo) {
            Log::warning('WhatsApp Gateway: Konfigurasi tidak ditemukan di tbl_token.');
            return false;
        }

        // Clean target number
        $target = preg_replace('/[^0-9]/', '', $target);
        if (str_starts_with($target, '0')) {
            $target = '62' . substr($target, 1);
        }

        if ($target === '') {
            return false;
        }

        $gateway = $tokenInfo->wa_gateway ?? 'fonnte';
        
        if ($gateway === 'bablast' && !empty($templateName)) {
            // Trim all parameters to remove accidental spaces
            foreach ($parameters as $key => $val) {
                if (is_string($val)) {
                    $parameters[$key] = trim($val);
                }
            }

            if ($mediaUrl) {
                array_unshift($parameters, $mediaUrl);
            }

            return $this->sendTemplateViaBablast($target, $templateName, $parameters, $tokenInfo->bablast_token, $language);
        } else {
            // Fallback to normal text message for Fonnte or if template name is empty
            return $this->sendViaFonnte($target, $fallbackMessage, $tokenInfo->token, $mediaUrl);
        }
    }

    private function sendViaFonnte(string $target, string $message, ?string $token, ?string $mediaUrl = null): bool
    {
        if (!$token) {
            Log::warning('WA Fonnte: Token Fonnte belum dikonfigurasi.');
            return false;
        }

        try {
            $payload = [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62'
            ];

            if ($mediaUrl) {
                $payload['url'] = $mediaUrl;
            }

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->asForm()->post('https://api.fonnte.com/send', $payload);

            $resData = $response->json();
            
            if (!$response->successful() || (isset($resData['status']) && $resData['status'] === false)) {
                $reason = $resData['reason'] ?? $resData['message'] ?? 'Fonnte error atau device offline.';
                Log::warning("WA Fonnte gagal dikirim ke {$target}. Reason: {$reason}");
                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::error('WA Fonnte error: ' . $exception->getMessage());
            return false;
        }
    }

    private function sendViaBablast(string $target, string $message, ?string $token, ?string $mediaUrl = null): bool
    {
        if (!$token) {
            Log::warning('WA Bablast: Token Bablast belum dikonfigurasi.');
            return false;
        }

        try {
            $payload = [
                'phone' => $target,
                'message' => empty(trim($message)) ? "\u{200B}" : $message, // Use zero-width space if empty to satisfy API requirement
            ];
            
            if ($mediaUrl) {
                // Ensure mediaUrl is absolute. If it starts with / , we prepend the request host or env APP_URL.
                // It should already be absolute because we used asset(), but just in case:
                
                // For Bablast WABA, media is usually sent with specific types
                $payload['media_url'] = $mediaUrl;
                $payload['url'] = $mediaUrl; // some WABA gateways use 'url' instead
                $payload['type'] = 'image'; // assumption for Bablast
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post('https://api.bablast.id/waba/send', $payload);

            $resData = $response->json();

            if (!$response->successful() || (isset($resData['success']) && $resData['success'] === false)) {
                $reason = $resData['message'] ?? 'Bablast error atau device offline.';
                Log::warning("WA Bablast gagal dikirim ke {$target}. Reason: {$reason}");
                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::error('WA Bablast error: ' . $exception->getMessage());
            return false;
        }
    }

    private function sendTemplateViaBablast(string $target, string $templateName, array $parameters, ?string $token, string $language = 'id'): bool
    {
        if (!$token) {
            Log::warning('WA Bablast: Token Bablast belum dikonfigurasi (Template).');
            return false;
        }

        try {
            $components = [];
            $bodyStartIndex = 0;

            if (isset($parameters[0]) && is_string($parameters[0]) && filter_var($parameters[0], FILTER_VALIDATE_URL)) {
                $ext = strtolower(pathinfo(parse_url($parameters[0], PHP_URL_PATH), PATHINFO_EXTENSION));
                $type = 'image';
                if (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx'])) {
                    $type = 'document';
                } elseif (in_array($ext, ['mp4', 'avi', 'mov'])) {
                    $type = 'video';
                }
                
                $components[] = [
                    'type' => 'header',
                    'parameters' => [
                        [
                            'type' => $type,
                            $type => ['link' => $parameters[0]]
                        ]
                    ]
                ];
                $bodyStartIndex = 1;
            }

            $bodyParams = [];
            for ($i = $bodyStartIndex; $i < count($parameters); $i++) {
                if (is_string($parameters[$i])) {
                    $bodyParams[] = ['type' => 'text', 'text' => $parameters[$i]];
                }
            }

            if (count($bodyParams) > 0) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => $bodyParams
                ];
            }

            $payload = [
                'phone' => $target,
                'template_name' => $templateName,
                'language' => $language,
            ];

            if (!empty($components)) {
                $payload['components'] = $components;
            } else {
                $payload['parameters'] = $parameters;
            }
            
            \Illuminate\Support\Facades\Log::info("Sending to Bablast: " . json_encode($payload));

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post('https://api.bablast.id/waba/send-template', $payload);

            $resData = $response->json();

            if (!$response->successful() || (isset($resData['success']) && $resData['success'] === false)) {
                $reason = $resData['message'] ?? 'Bablast Template error.';
                Log::warning("WA Bablast Template gagal dikirim ke {$target}. Reason: {$reason}");
                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            Log::error('WA Bablast Template error: ' . $exception->getMessage());
            return false;
        }
    }
}

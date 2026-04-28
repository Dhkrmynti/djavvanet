<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $token;
    protected $baseUrl = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->token = config('services.fonnte.token');
    }

    public function formatNumber($number)
    {
        $number = preg_replace('/\D/', '', $number);
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }
        elseif (str_starts_with($number, '8')) {
            $number = '62' . $number;
        }
        return $number;
    }

    public function sendMessage($target, $message, $url = null, $saveToHistory = false)
    {
        $formattedTarget = $this->formatNumber($target);
        
        $data = [
            'target' => $formattedTarget,
            'message' => $message,
        ];

        if ($url) {
            $data['url'] = $url;
            $data['type'] = 'image';
        }

        $result = $this->callFonnte('send', $data);

        // Jika berhasil, otomatis rekam pesan ke riwayat chat web jika diizinkan
        if ($result === true && $saveToHistory) {
            $this->saveToChatHistory($formattedTarget, $message, $url);
        }

        return $result;
    }

    protected function saveToChatHistory($targetPhone, $messageText, $url = null)
    {
        try {
            $customer = \App\Models\Customer::where('whatsapp_number', $targetPhone)
                ->orWhere('whatsapp_number', '0' . substr($targetPhone, 2))
                ->first();
            
            $metadata = [];
            if ($url) {
                $metadata['attachment_url'] = $url;
            }

            $conversation = \App\Models\ChatConversation::firstOrCreate(
                ['guest_phone' => $targetPhone],
                [
                    'source_type' => 'whatsapp',
                    'status' => 'open',
                    'customer_id' => $customer ? $customer->id : null,
                    'guest_name' => $customer ? $customer->full_name : 'Unknown User'
                ]
            );

            // Simpan sebagai 'admin' (sistem)
            $dbMessage = $conversation->messages()->create([
                'sender_type' => 'admin',
                'sender_admin_id' => auth()->id() ?? null, 
                'message' => $messageText,
                'metadata' => empty($metadata) ? null : $metadata,
                'is_read' => true,
            ]);

            $conversation->forceFill([
                'status' => 'open',
                'last_message_at' => now(),
                'unread_for_customer_count' => $conversation->unread_for_customer_count + 1,
            ])->save();

            if (class_exists(\App\Events\ChatMessageCreated::class)) {
                broadcast(new \App\Events\ChatMessageCreated($conversation, $dbMessage))->toOthers();
            }
        } catch (\Exception $e) {
            Log::warning("Failed to save outgoing WA to chat history: " . $e->getMessage());
        }
    }

    public function validateNumber($target)
    {
        return $this->callFonnte('validate', [
            'target' => $this->formatNumber($target) . ',',
        ]);
    }

    protected function callFonnte($endpoint, $data)
    {
        if (!$this->token) {
            Log::error('Fonnte token is not set in .env');
            return false;
        }

        $url = "https://api.fonnte.com/{$endpoint}";

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])
                ->withoutVerifying()
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
                ->asMultipart()
                ->post($url, $this->buildMultipartData($data));

            $body = $response->json();
            Log::info("Fonnte API Request: " . json_encode($data));
            Log::info("Fonnte API {$endpoint} Output: " . json_encode($body));

            if ($response->successful()) {
                if (isset($body['status']) && $body['status'] == true) {
                    if ($endpoint === 'validate') {
                        if (isset($body['registered']) && !empty($body['registered'])) {
                            return ['valid' => true, 'message' => 'Terhubung ke WhatsApp'];
                        }
                        return ['valid' => false, 'message' => 'Fonnte: Nomor tidak terdaftar atau API terbatas'];
                    }

                    // Tambahkan cache lock untuk mencegah Echo Webhook (Deduplikasi)
                    if ($endpoint === 'send' && isset($data['target']) && isset($data['message'])) {
                        $contentLockKey = 'fonnte_content_lock_' . md5($this->formatNumber($data['target']) . $data['message']);
                        \Illuminate\Support\Facades\Cache::put($contentLockKey, true, 15);
                    }

                    return true;
                }

                return ['valid' => false, 'message' => $body['reason'] ?? 'Fonnte: Gagal (Cek Token/Quota)'];
            }

            return ['valid' => false, 'message' => 'Fonnte Error: ' . $response->status()];
        }
        catch (\Exception $e) {
            Log::error("Error calling Fonnte {$endpoint}: " . $e->getMessage());
            return ['valid' => false, 'message' => 'Sistem WA tidak merespon'];
        }
    }

    /**
     * Convert associative array to multipart form data format.
     */
    protected function buildMultipartData(array $data): array
    {
        $multipart = [];
        foreach ($data as $key => $value) {
            $multipart[] = [
                'name' => $key,
                'contents' => (string)$value,
            ];
        }
        return $multipart;
    }
}

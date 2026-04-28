<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageCreated;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FonnteWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->isMethod('get')) {
            return response()->json(['status' => 'success', 'message' => 'Fonnte Webhook is active']);
        }

        Log::info('--- Fonnte Webhook Chaining (Capture All) ---');
        Log::info('Raw Content: ' . $request->getContent());
        
        $payload = $request->all();
        $deviceNumber = $this->formatNumber($payload['device'] ?? '');
        
        $sender = $payload['sender'] ?? $payload['from'] ?? $payload['pengirim'] ?? null;
        $messageText = $payload['message'] ?? $payload['text'] ?? $payload['pesan'] ?? '';
        $target = $payload['target'] ?? $payload['to'] ?? $payload['receiver'] ?? null;

        if (str_contains($messageText, 'Sent via fonnte.com')) {
            Log::info('Fonnte: Ignored API echo message');
            return response()->json(['status' => 'success']);
        }

        $inboxId = $payload['inboxid'] ?? null;
        if ($inboxId) {
            $lockKey = 'fonnte_webhook_inbox_' . $inboxId;
            if (!Cache::add($lockKey, true, 600)) {
                Log::info('Fonnte: Ignored duplicate webhook for inboxid: ' . $inboxId);
                return response()->json(['status' => 'success', 'duplicated' => true]);
            }
        }

        $formattedDevice = $this->formatNumber($payload['device'] ?? '');
        $formattedSender = $sender ? $this->formatNumber($sender) : null;
        
        $isManualAdminReply = (
            (!empty($formattedSender) && $formattedSender === $formattedDevice) || 
            (!empty($target) && empty($payload['inboxid']))
        );

        $customerPhone = null;
        if ($isManualAdminReply) {
            $customerPhone = $this->formatNumber($target ?: ($formattedSender ?? ''));
        } else {
            $customerPhone = $formattedSender;
        }

        // De-duplikasi berdasarkan konten untuk mencegah API Echo tersimpan ganda
        // Jika pesan dengan konten yang sama baru saja dikirim ke target ini via API, abaikan.
        $contentLockKey = 'fonnte_content_lock_' . md5(($customerPhone ?? 'none') . $messageText);
        if (!Cache::add($contentLockKey, true, 10)) {
            Log::info('Fonnte: Ignored global content-based duplicate/echo: ' . $messageText);
            return response()->json(['status' => 'success', 'echo' => true]);
        }

        Log::info('--- Fonnte Detection ---', [
            'device' => $formattedDevice,
            'sender' => $formattedSender,
            'customer' => $customerPhone ?: 'N/A',
            'is_outgoing' => $isManualAdminReply
        ]);

        if (empty($customerPhone) || empty($messageText)) {
            Log::info('Fonnte: Ignored due to missing customer or message');
            return response()->json(['status' => 'success']);
        }

        return $this->processIncomingMessage($payload, $customerPhone, $messageText, $isManualAdminReply);
    }

    public function status(Request $request)
    {
        Log::info('--- Fonnte Webhook Message Status ---');
        Log::info('Payload: ', $request->all());
        
        return response()->json(['status' => 'success']);
    }

    public function connect(Request $request)
    {
        Log::info('--- Fonnte Webhook Device Connect ---');
        Log::info('Payload: ', $request->all());
        return response()->json(['status' => 'success']);
    }

    protected function processIncomingMessage($payload, $customerPhone, $messageText, $isManualAdminReply = false)
    {
        $rawNumber = preg_replace('/\D/', '', $customerPhone);
        $alternatives = [$rawNumber];
        if (str_starts_with($rawNumber, '62')) {
            $alternatives[] = '0' . substr($rawNumber, 2);
        } elseif (str_starts_with($rawNumber, '0')) {
            $alternatives[] = '62' . substr($rawNumber, 1);
        }
        
        $customer = Customer::whereIn('whatsapp_number', $alternatives)->first();

        $inboxId = $payload['inboxid'] ?? null;
        $senderName = $payload['name'] ?? null;
        $attachmentUrl = $payload['url'] ?? $payload['file'] ?? null;

        $conversation = ChatConversation::where('guest_phone', $customerPhone)
            ->where('source_type', 'whatsapp')
            ->where('status', 'open')
            ->first();

        if (!$conversation) {
            $conversation = ChatConversation::create([
                'customer_id' => $customer?->id,
                'source_type' => 'whatsapp',
                'guest_name' => $customer?->full_name ?: ($senderName ?: 'WA: ' . $customerPhone),
                'guest_phone' => $customerPhone,
                'subject' => 'WhatsApp Chat',
                'status' => 'open',
                'priority' => 'normal',
                'assigned_admin_id' => $this->resolveAssignedAdminId(),
                'last_message_at' => now(),
            ]);
        } else {
            $updateData = [
                'last_message_at' => now(),
                'unread_for_admin_count' => $isManualAdminReply ? $conversation->unread_for_admin_count : ($conversation->unread_for_admin_count + 1),
            ];

            // Aggressively sync customer if found
            if ($customer) {
                if (!$conversation->customer_id) {
                    $updateData['customer_id'] = $customer->id;
                }
                // Update guest_name to customer's full_name to ensure proper display
                if ($conversation->guest_name !== $customer->full_name) {
                    $updateData['guest_name'] = $customer->full_name;
                }
            }

            $conversation->update($updateData);
        }

        $senderType = $isManualAdminReply ? 'admin' : 'customer';

        $message = $conversation->messages()->create([
            'sender_type' => $senderType,
            'sender_admin_id' => $isManualAdminReply ? $this->resolveAssignedAdminId($conversation) : null,
            'sender_customer_id' => !$isManualAdminReply ? $customer?->id : null,
            'message' => $messageText,
            'is_read' => $isManualAdminReply,
            'metadata' => [
                'inboxid' => $inboxId,
                'sender_name' => $senderName,
                'manual_reply' => $isManualAdminReply,
                'raw_payload' => $payload,
                'attachment_url' => $attachmentUrl,
            ]
        ]);

        try {
            broadcast(new ChatMessageCreated($conversation->load(['customer', 'assignedAdmin']), $message->load(['senderAdmin', 'senderCustomer'])))->toOthers();
        } catch (\Exception $e) {
            Log::error('Fonnte Webhook Broadcast Error: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success']);
    }

    protected function formatNumber($number)
    {
        $number = preg_replace('/\D/', '', $number);
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        } elseif (str_starts_with($number, '8')) {
            $number = '62' . $number;
        }
        return $number;
    }

    protected function resolveAssignedAdminId(?ChatConversation $conversation = null): ?int
    {
        if ($conversation && $conversation->assigned_admin_id) {
            return $conversation->assigned_admin_id;
        }

        $user = User::query()
            ->withCount(['assignedChatConversations as active_chat_count' => function ($query) {
                $query->where('status', 'open');
            }])
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('active_chat_count')
            ->orderBy('id')
            ->first();

        return $user ? $user->id : null;
    }
}

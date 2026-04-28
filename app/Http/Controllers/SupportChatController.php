<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageCreated;
use App\Events\ChatTypingUpdated;
use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function publicBootstrap(Request $request): JsonResponse
    {
        $conversation = $this->findGuestConversationFromSession($request);

        if ($conversation) {
            $this->markCustomerSideAsRead($conversation);
        }

        return response()->json([
            'conversation' => $conversation ? $this->conversationPayload($conversation->load(['messages.senderAdmin', 'messages.senderCustomer'])) : null,
        ]);
    }

    public function publicStart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'message' => 'required|string|max:4000',
        ]);

        $conversation = $this->findGuestConversationFromSession($request);

        if (!$conversation) {
            $conversation = ChatConversation::create([
                'assigned_admin_id' => $this->resolveAssignedAdminId(),
                'source_type' => 'guest',
                'guest_name' => $validated['name'],
                'guest_email' => $validated['email'] ?? null,
                'guest_phone' => $validated['phone'],
                'subject' => 'Website visitor support',
                'status' => 'open',
                'priority' => 'normal',
                'last_message_at' => now(),
                'unread_for_admin_count' => 0,
                'unread_for_customer_count' => 0,
            ]);
        } else {
            $conversation->update([
                'guest_name' => $validated['name'],
                'guest_email' => $validated['email'] ?? $conversation->guest_email,
                'guest_phone' => $validated['phone'],
            ]);
        }

        $request->session()->put('support_chat_token', $conversation->public_token);

        $message = $conversation->messages()->create([
            'sender_type' => 'customer',
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->forceFill([
            'status' => 'open',
            'last_message_at' => now(),
            'customer_last_seen_at' => now(),
            'unread_for_admin_count' => $conversation->unread_for_admin_count + 1,
        ])->save();

        $conversation->load(['customer', 'assignedAdmin']);
        $message->load(['senderAdmin', 'senderCustomer']);

        broadcast(new ChatMessageCreated($conversation, $message))->toOthers();

        return response()->json([
            'message' => 'Percakapan live chat berhasil dimulai.',
            'conversation' => $this->conversationPayload($conversation->load(['messages.senderAdmin', 'messages.senderCustomer'])),
            'chat_message' => $this->messagePayload($message),
        ]);
    }

    public function publicSend(Request $request): JsonResponse
    {
        $conversation = $this->findGuestConversationFromSession($request);

        abort_unless($conversation, 404);

        $validated = $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'customer',
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->forceFill([
            'status' => 'open',
            'last_message_at' => now(),
            'customer_last_seen_at' => now(),
            'unread_for_admin_count' => $conversation->unread_for_admin_count + 1,
        ])->save();

        $conversation->load(['customer', 'assignedAdmin']);
        $message->load(['senderAdmin', 'senderCustomer']);

        broadcast(new ChatMessageCreated($conversation, $message))->toOthers();

        return response()->json([
            'message' => 'Pesan berhasil dikirim.',
            'conversation' => $this->conversationPayload($conversation->load(['messages' => fn ($query) => $query->oldest(), 'messages.senderAdmin', 'messages.senderCustomer'])),
            'chat_message' => $this->messagePayload($message),
        ]);
    }

    public function customerSend(Request $request): JsonResponse
    {
        $customer = auth()->guard('customer')->user();
        abort_unless($customer, 403);

        $validated = $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        $conversation = ChatConversation::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'source_type' => 'customer',
                'status' => 'open',
            ],
            [
                'assigned_admin_id' => $this->resolveAssignedAdminId(),
                'subject' => 'Customer support portal',
                'last_message_at' => now(),
            ]
        );

        $message = $conversation->messages()->create([
            'sender_type' => 'customer',
            'sender_customer_id' => $customer->id,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->forceFill([
            'status' => 'open',
            'last_message_at' => now(),
            'customer_last_seen_at' => now(),
            'unread_for_admin_count' => $conversation->unread_for_admin_count + 1,
        ])->save();

        $conversation->load(['customer', 'assignedAdmin', 'messages.senderAdmin', 'messages.senderCustomer']);
        $message->load(['senderAdmin', 'senderCustomer']);

        broadcast(new ChatMessageCreated($conversation, $message))->toOthers();

        return response()->json([
            'message' => 'Pesan berhasil dikirim.',
            'conversation' => $this->conversationPayload($conversation),
            'chat_message' => $this->messagePayload($message),
        ]);
    }

    public function customerRead(ChatConversation $conversation): JsonResponse
    {
        $customer = auth()->guard('customer')->user();
        abort_unless($customer && $conversation->customer_id === $customer->id, 403);

        $this->markCustomerSideAsRead($conversation);

        return response()->json(['ok' => true]);
    }

    protected function findGuestConversationFromSession(Request $request): ?ChatConversation
    {
        $token = $request->session()->get('support_chat_token');

        if (!$token) {
            return null;
        }

        return ChatConversation::with(['messages' => fn ($query) => $query->oldest(), 'messages.senderAdmin', 'messages.senderCustomer'])
            ->where('public_token', $token)
            ->first();
    }

    protected function markCustomerSideAsRead(ChatConversation $conversation): void
    {
        $conversation->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($conversation->unread_for_customer_count > 0) {
            $conversation->forceFill([
                'unread_for_customer_count' => 0,
                'customer_last_seen_at' => now(),
            ])->save();
        } else {
            $conversation->forceFill([
                'customer_last_seen_at' => now(),
            ])->save();
        }
    }

    public function publicTyping(Request $request): JsonResponse
    {
        $conversation = $this->findGuestConversationFromSession($request);
        abort_unless($conversation, 404);

        $validated = $request->validate([
            'is_typing' => 'required|boolean',
        ]);

        broadcast(new ChatTypingUpdated(
            $conversation,
            'customer',
            $conversation->display_name,
            (bool) $validated['is_typing']
        ))->toOthers();

        return response()->json(['ok' => true]);
    }

    public function customerTyping(Request $request): JsonResponse
    {
        $customer = auth()->guard('customer')->user();
        abort_unless($customer, 403);

        $conversation = ChatConversation::where('customer_id', $customer->id)->firstOrFail();
        $validated = $request->validate([
            'is_typing' => 'required|boolean',
        ]);

        broadcast(new ChatTypingUpdated(
            $conversation,
            'customer',
            $customer->full_name,
            (bool) $validated['is_typing']
        ))->toOthers();

        return response()->json(['ok' => true]);
    }

    protected function conversationPayload(ChatConversation $conversation): array
    {
        $messages = $conversation->messages->map(fn ($message) => $this->messagePayload($message))->values();

        return [
            'id' => $conversation->id,
            'public_token' => $conversation->public_token,
            'display_name' => $conversation->display_name,
            'display_code' => $conversation->display_code,
            'source_type' => $conversation->source_type,
            'source_label' => $conversation->source_label,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'status_label' => $conversation->status_label,
            'priority' => $conversation->priority,
            'priority_label' => $conversation->priority_label,
            'messages' => $messages,
        ];
    }

    protected function messagePayload($message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_type' => $message->sender_type,
            'sender_name' => $message->senderAdmin->name ?? $message->senderCustomer->full_name ?? $message->conversation?->display_name ?? 'Customer',
            'message' => $message->message,
            'is_read' => $message->is_read,
            'created_at' => $message->created_at->toIso8601String(),
            'created_at_label' => $message->created_at->format('d M Y H:i'),
        ];
    }

    protected function resolveAssignedAdminId(): ?int
    {
        $user = User::query()
            ->withCount(['assignedChatConversations as active_chat_count' => function ($query) {
                $query->where('status', 'open');
            }])
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('active_chat_count')
            ->orderBy('id')
            ->first();

        return $user?->id;
    }
}

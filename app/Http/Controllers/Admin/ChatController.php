<?php

namespace App\Http\Controllers\Admin;

use App\Events\ChatMessageCreated;
use App\Events\ChatTypingUpdated;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Customer;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = ChatConversation::with(['customer', 'assignedAdmin', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        $selectedConversation = null;
        $selectedId = $request->integer('conversation');

        if ($selectedId) {
            $selectedConversation = $conversations->firstWhere('id', $selectedId);
        }

        if (!$selectedConversation && $conversations->isNotEmpty()) {
            $selectedConversation = $conversations->first();
        }

        if ($selectedConversation) {
            $selectedConversation->load([
                'customer',
                'assignedAdmin',
                'messages' => fn ($query) => $query->oldest(),
                'messages.senderAdmin',
                'messages.senderCustomer',
            ]);

            $selectedConversation->messages()
                ->where('sender_type', 'customer')
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            if ($selectedConversation->unread_for_admin_count > 0) {
                $selectedConversation->forceFill([
                    'unread_for_admin_count' => 0,
                    'admin_last_seen_at' => now(),
                ])->save();
            } else {
                $selectedConversation->forceFill([
                    'admin_last_seen_at' => now(),
                ])->save();
            }
        }

        $agents = User::orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'customer_code', 'status']);
        $stats = [
            'total' => $conversations->count(),
            'open' => $conversations->where('status', 'open')->count(),
            'pending' => $conversations->where('status', 'pending')->count(),
            'closed' => $conversations->where('status', 'closed')->count(),
            'unread' => $conversations->sum('unread_for_admin_count'),
        ];

        return view('admin.chat.index', compact('conversations', 'selectedConversation', 'customers', 'stats', 'agents'));
    }

    public function storeConversation(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subject' => 'nullable|string|max:255',
            'opening_message' => 'nullable|string|max:2000',
        ]);

        $conversation = ChatConversation::firstOrCreate(
            [
                'customer_id' => $validated['customer_id'],
                'status' => 'open',
            ],
            [
                'assigned_admin_id' => auth()->id(),
                'source_type' => 'customer',
                'subject' => $validated['subject'] ?: null,
                'last_message_at' => now(),
            ]
        );

        if (!$conversation->assigned_admin_id) {
            $conversation->assigned_admin_id = auth()->id() ?: $this->resolveAssignedAdminId();
        }

        if (!$conversation->subject && !empty($validated['subject'])) {
            $conversation->subject = $validated['subject'];
        }

        if (!$conversation->last_message_at) {
            $conversation->last_message_at = now();
        }

        $conversation->save();

        if (!empty($validated['opening_message'])) {
            $message = $conversation->messages()->create([
                'sender_type' => 'admin',
                'sender_admin_id' => auth()->id(),
                'message' => $validated['opening_message'],
                'is_read' => false,
            ]);

            $conversation->forceFill([
                'last_message_at' => now(),
                'unread_for_customer_count' => $conversation->unread_for_customer_count + 1,
            ])->save();

            broadcast(new ChatMessageCreated($conversation->fresh(), $message->fresh()))->toOthers();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Percakapan berhasil dibuat.',
                'redirect_url' => route('admin.chat.index', ['conversation' => $conversation->id])
            ]);
        }

        return redirect()->route('admin.chat.index', ['conversation' => $conversation->id])
            ->with('success', 'Percakapan live chat berhasil disiapkan.');
    }

    public function storeMessage(Request $request, ChatConversation $conversation): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:4000',
            'attachment' => 'nullable|image|max:2048',
        ]);

        if (empty($validated['message']) && !$request->hasFile('attachment')) {
            return response()->json(['message' => 'Pesan atau gambar harus diisi.'], 422);
        }

        if (!$conversation->assigned_admin_id) {
            $conversation->assigned_admin_id = auth()->id() ?: $this->resolveAssignedAdminId();
        }

        $metadata = [];
        $attachmentUrl = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Simpan ke storage/app/public/chat
            $path = $file->storeAs('chat', $filename, 'public');
            
            $attachmentUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            $metadata['attachment_url'] = $attachmentUrl;
        }

        $message = $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_admin_id' => auth()->id(),
            'message' => $validated['message'] ?? '',
            'metadata' => $metadata,
            'is_read' => false,
        ]);

        if (!$conversation->first_response_at) {
            $conversation->first_response_at = now();
            $conversation->first_response_admin_id = auth()->id();
        }

        $conversation->forceFill([
            'status' => 'open',
            'assigned_admin_id' => $conversation->assigned_admin_id,
            'last_message_at' => now(),
            'admin_last_seen_at' => now(),
            'unread_for_customer_count' => $conversation->unread_for_customer_count + 1,
        ])->save();

        $message->load(['senderAdmin', 'senderCustomer']);
        $conversation->load(['customer', 'assignedAdmin']);

        // Jika source adalah WhatsApp, kirim lewat Fonnte
        if ($conversation->source_type === 'whatsapp' && !empty($conversation->guest_phone)) {
            $waService = app(WhatsAppService::class);
            
            // Jika ada gambar, kirim sebagai pesan media
            if ($attachmentUrl) {
                // Fonnte bisa kirim caption di parameter message
                $waService->sendMessage($conversation->guest_phone, $validated['message'] ?? '', $attachmentUrl);
            } else {
                $waService->sendMessage($conversation->guest_phone, $validated['message']);
            }
        }

        try {
            broadcast(new ChatMessageCreated($conversation, $message))->toOthers();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Broadcasting failed: " . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Pesan berhasil dikirim.',
                'conversation' => $this->conversationPayload($conversation),
                'chat_message' => $this->messagePayload($message),
            ]);
        }

        return redirect()->route('admin.chat.index', ['conversation' => $conversation->id])
            ->with('success', 'Pesan berhasil dikirim.');
    }

    public function markAsRead(Request $request, ChatConversation $conversation): JsonResponse
    {
        $conversation->messages()
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $conversation->forceFill([
            'unread_for_admin_count' => 0,
            'admin_last_seen_at' => now(),
        ])->save();

        return response()->json([
            'ok' => true,
        ]);
    }

    public function updateConversation(Request $request, ChatConversation $conversation): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'status' => 'nullable|in:open,pending,closed',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'assigned_admin_id' => 'nullable|exists:users,id',
        ]);

        if (array_key_exists('status', $validated)) {
            $conversation->status = $validated['status'];
            $conversation->closed_at = $validated['status'] === 'closed' ? now() : null;
        }

        if (array_key_exists('priority', $validated)) {
            $conversation->priority = $validated['priority'];
        }

        if (array_key_exists('assigned_admin_id', $validated) && $validated['assigned_admin_id']) {
            $conversation->assigned_admin_id = (int) $validated['assigned_admin_id'];
        }

        $conversation->save();
        $conversation->load(['customer', 'assignedAdmin']);

        broadcast(new \App\Events\ChatConversationUpdated($conversation))->toOthers();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Percakapan berhasil diperbarui.',
                'conversation' => $this->conversationPayload($conversation),
            ]);
        }

        return redirect()->route('admin.chat.index', ['conversation' => $conversation->id])
            ->with('success', 'Percakapan berhasil diperbarui.');
    }

    public function destroyConversation(ChatConversation $conversation): JsonResponse
    {
        $conversation->messages()->delete();
        $conversation->delete();

        return response()->json([
            'message' => 'Percakapan berhasil dihapus selamanya.',
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        // Mark all conversations as read for admin
        ChatConversation::where('unread_for_admin_count', '>', 0)
            ->update(['unread_for_admin_count' => 0]);

        // Mark all messages as read
        ChatMessage::where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'message' => 'Semua percakapan ditandai sebagai terbaca.',
        ]);
    }

    public function typing(Request $request, ChatConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'is_typing' => 'required|boolean',
        ]);

        broadcast(new ChatTypingUpdated(
            $conversation,
            'admin',
            auth()->user()->name ?? 'Admin',
            (bool) $validated['is_typing']
        ))->toOthers();

        return response()->json(['ok' => true]);
    }

    protected function conversationPayload(ChatConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'customer_name' => $conversation->display_name,
            'customer_code' => $conversation->display_code,
            'source_type' => $conversation->source_type,
            'source_label' => $conversation->source_label,
            'public_token' => $conversation->public_token,
            'subject' => $conversation->subject,
            'status' => $conversation->status,
            'status_label' => $conversation->status_label,
            'priority' => $conversation->priority,
            'priority_label' => $conversation->priority_label,
            'assigned_admin_name' => $conversation->assignedAdmin->name ?? null,
            'assigned_admin_id' => $conversation->assigned_admin_id,
            'unread_for_admin_count' => $conversation->unread_for_admin_count,
            'unread_for_customer_count' => $conversation->unread_for_customer_count,
            'last_message_at' => optional($conversation->last_message_at)->toIso8601String(),
            'last_message_human' => optional($conversation->last_message_at)->diffForHumans(),
            'first_response_at' => optional($conversation->first_response_at)->toIso8601String(),
        ];
    }

    protected function messagePayload(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_type' => $message->sender_type,
            'sender_admin_id' => $message->sender_admin_id,
            'sender_customer_id' => $message->sender_customer_id,
            'sender_name' => $message->senderAdmin->name ?? $message->senderCustomer->full_name ?? $message->conversation?->display_name ?? 'System',
            'message' => $message->message,
            'attachment_url' => $message->metadata['attachment_url'] ?? null,
            'is_read' => $message->is_read,
            'created_at' => $message->created_at->toIso8601String(),
            'created_at_label' => $message->created_at->format('d M Y H:i'),
            'created_at_label_short' => $message->created_at->format('H:i'),
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

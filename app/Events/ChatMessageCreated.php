<?php

namespace App\Events;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatConversation $conversation,
        public ChatMessage $message,
    ) {
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.chat'),
            new PrivateChannel('admin.chat.conversation.'.$this->conversation->id),
        ];

        if ($this->conversation->source_type === 'customer' && $this->conversation->customer_id) {
            $channels[] = new PrivateChannel('customer.chat.'.$this->conversation->id);
        } else {
            $channels[] = new Channel('chat.conversation.'.$this->conversation->public_token);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'chat.message.created';
    }

    public function broadcastWith(): array
    {
        $conversation = $this->conversation->loadMissing(['customer', 'assignedAdmin']);
        $message = $this->message->loadMissing(['senderAdmin', 'senderCustomer']);

        return [
            'conversation' => [
                'id' => $conversation->id,
                'customer_id' => $conversation->customer_id,
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
                'assigned_admin_id' => $conversation->assigned_admin_id,
                'assigned_admin_name' => $conversation->assignedAdmin->name ?? null,
                'unread_for_admin_count' => $conversation->unread_for_admin_count,
                'unread_for_customer_count' => $conversation->unread_for_customer_count,
                'last_message_at' => optional($conversation->last_message_at)->toIso8601String(),
                'last_message_human' => optional($conversation->last_message_at)->diffForHumans(),
                'first_response_at' => optional($conversation->first_response_at)->toIso8601String(),
            ],
            'message' => [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_type' => $message->sender_type,
                'sender_admin_id' => $message->sender_admin_id,
                'sender_customer_id' => $message->sender_customer_id,
                'sender_name' => $message->sender_type === 'admin' 
                    ? ($conversation->assignedAdmin->name ?? $message->senderAdmin->name ?? 'Admin')
                    : ($message->senderCustomer->full_name ?? $conversation->display_name),
                'message' => $message->message,
                'attachment_url' => $message->metadata['attachment_url'] ?? null,
                'is_read' => $message->is_read,
                'created_at' => $message->created_at->toIso8601String(),
                'created_at_label' => $message->created_at->format('d M Y H:i'),
                'created_at_label_short' => $message->created_at->format('H:i'),
            ],
        ];
    }
}

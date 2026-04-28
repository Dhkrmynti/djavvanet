<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatConversationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatConversation $conversation
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.chat'),
            new PrivateChannel('admin.chat.conversation.' . $this->conversation->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.conversation.updated';
    }

    public function broadcastWith(): array
    {
        $conversation = $this->conversation->loadMissing(['customer', 'assignedAdmin']);

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
            ],
        ];
    }
}

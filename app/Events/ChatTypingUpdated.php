<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTypingUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatConversation $conversation,
        public string $actorType,
        public string $actorName,
        public bool $isTyping,
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
        return 'chat.typing.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'public_token' => $this->conversation->public_token,
            'actor_type' => $this->actorType,
            'actor_name' => $this->actorName,
            'is_typing' => $this->isTyping,
        ];
    }
}

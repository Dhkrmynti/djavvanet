<?php

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.chat', function ($user) {
    return $user instanceof User;
});

Broadcast::channel('admin.chat.conversation.{conversationId}', function ($user, $conversationId) {
    return $user instanceof User;
});

Broadcast::channel('customer.chat.{conversationId}', function ($customer, $conversationId) {
    $conversation = ChatConversation::find($conversationId);

    return $conversation && (int) $conversation->customer_id === (int) $customer->id;
}, ['guards' => ['customer']]);

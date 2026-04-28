<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'assigned_admin_id',
        'source_type',
        'guest_name',
        'guest_email',
        'guest_phone',
        'public_token',
        'subject',
        'status',
        'priority',
        'unread_for_admin_count',
        'unread_for_customer_count',
        'last_message_at',
        'admin_last_seen_at',
        'customer_last_seen_at',
        'closed_at',
        'first_response_at',
        'first_response_admin_id',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'admin_last_seen_at' => 'datetime',
        'customer_last_seen_at' => 'datetime',
        'closed_at' => 'datetime',
        'first_response_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $conversation) {
            if (!$conversation->public_token) {
                $conversation->public_token = Str::random(40);
            }

            if (!$conversation->source_type) {
                $conversation->source_type = $conversation->customer_id ? 'customer' : 'guest';
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->customer_id && $this->customer) {
            return $this->customer->full_name;
        }

        if ($this->source_type === 'whatsapp') {
            return $this->guest_name ?: $this->guest_phone ?: 'WhatsApp User';
        }

        return $this->guest_name ?: 'Pengunjung Website';
    }

    public function getDisplayCodeAttribute(): string
    {
        if ($this->customer_id && $this->customer) {
            return $this->customer->customer_code;
        }

        if ($this->source_type === 'whatsapp') {
            return $this->guest_phone ?: 'WA';
        }

        return $this->guest_phone ?: 'Guest';
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            'guest' => 'Website',
            'whatsapp' => 'WhatsApp',
            default => 'Portal',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'closed' => 'Closed',
            default => 'Open',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'Urgent',
            'high' => 'High',
            'low' => 'Low',
            default => 'Normal',
        };
    }
}

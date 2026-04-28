<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'package_id',
        'mikrotik_profile',
        'customer_code',
        'username',
        'code_prefix',
        'full_name',
        'whatsapp_number',
        'email',
        'password',
        'password_plain',
        'address',
        'status',
        'active_since',
        'billing_cycle',
        'is_synced',
    ];

    protected $casts = [
        'active_since'   => 'date',
        'latitude'       => 'decimal:8',
        'longitude'      => 'decimal:8',
        'is_synced'      => 'boolean',
        'password_plain' => 'encrypted',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function chatConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_customer_id');
    }
}

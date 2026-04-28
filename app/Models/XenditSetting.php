<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XenditSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_key',
        'callback_token',
        'webhook_url',
        'is_active',
        'mode',
        'admin_fee',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'callback_token' => 'encrypted',
        'is_active' => 'boolean',
    ];
}

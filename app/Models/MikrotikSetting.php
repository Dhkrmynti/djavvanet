<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikrotikSetting extends Model
{
    protected $fillable = [
        'label', 'host', 'port', 'username', 'password',
        'default_profile', 'isolir_profile', 'default_service', 'is_active', 'last_connected_at',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'last_connected_at'  => 'datetime',
        'password'           => 'encrypted',
    ];

    protected $hidden = ['password'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'profile',
        'monthly_price',
        'installation_fee',
        'description',
        'features',
        'badge',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features'         => 'array',
        'monthly_price'    => 'decimal:2',
        'installation_fee' => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    public function getInstallationFreeAttribute(): bool
    {
        return is_null($this->installation_fee);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XenditFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'value'     => 'float',
    ];

    public function calculateFee($amount)
    {
        $baseFee = 0;
        if ($this->type === 'fixed') {
            $baseFee = $this->value;
        } else {
            $baseFee = ($this->value / 100) * $amount;
        }

        $totalFee = $baseFee * 1.11;

        return round($totalFee);
    }
}

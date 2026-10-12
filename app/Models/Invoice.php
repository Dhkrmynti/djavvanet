<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'period_start',
        'period_end',
        'amount',
        'installation_fee',
        'tax_amount',
        'total_amount',
        'due_date',
        'is_wa_sent',
        'wa_sent_at',
        'notes',
        'payment_hash',
        'status',
        'paid_at',
        'payment_method',
        'payment_channel',
    ];

    protected $casts = [
        'period_start'     => 'date',
        'period_end'       => 'date',
        'due_date'         => 'date',
        'is_wa_sent'       => 'boolean',
        'wa_sent_at'       => 'datetime',
        'amount'           => 'decimal:2',
        'installation_fee' => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'total_amount'     => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->payment_hash = \Illuminate\Support\Str::random(40);
        });
    }
}

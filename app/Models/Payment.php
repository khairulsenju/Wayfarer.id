<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'transaction_status',
        'payment_type',
        'gross_amount',
        'raw_response',
        'signature_valid',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'integer',
            'raw_response' => 'array',
            'signature_valid' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
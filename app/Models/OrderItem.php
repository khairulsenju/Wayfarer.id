<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    public const TYPE_SALE = 'sale';
    public const TYPE_RENT = 'rent';

    protected $fillable = [
        'order_id',
        'product_id',
        'type',
        'qty',
        'unit_price',
        'rent_start',
        'rent_end',
        'rent_days',
        'deposit',
        'subtotal',
        'approved_rent_end',
        'handed_over_at',
        'handover_tracking_number',
        'handover_proof_path',
        'handover_notes',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'integer',
            'rent_start' => 'date',
            'rent_end' => 'date',
            'rent_days' => 'integer',
            'deposit' => 'integer',
            'subtotal' => 'integer',
            'approved_rent_end' => 'date',
            'handed_over_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isRent(): bool
    {
        return $this->type === self::TYPE_RENT;
    }
    
    public function rentalReturn(): HasOne
    {
        return $this->hasOne(RentalReturn::class);
    }
}
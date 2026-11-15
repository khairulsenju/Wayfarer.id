<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
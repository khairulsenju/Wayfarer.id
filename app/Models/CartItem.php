<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    public const TYPE_SALE = 'sale';
    public const TYPE_RENT = 'rent';

    protected $fillable = [
        'cart_id',
        'product_id',
        'type',
        'qty',
        'rent_start',
        'rent_end',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'rent_start' => 'date',
            'rent_end' => 'date',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
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
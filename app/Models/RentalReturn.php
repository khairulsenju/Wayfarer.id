<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalReturn extends Model
{
    public const STATUS_AWAITING = 'awaiting_return';
    public const STATUS_INSPECTED = 'inspected';
    public const STATUS_SETTLED = 'settled';

    public const CONDITION_GOOD = 'good';
    public const CONDITION_DAMAGED = 'damaged';
    public const CONDITION_LOST = 'lost';

    protected $fillable = [
        'order_item_id',
        'returned_at',
        'condition_status',
        'late_days',
        'late_fee',
        'damage_fee',
        'deposit_refunded',
        'additional_due',
        'inspection_notes',
        'inspected_by',
        'inspected_at',
        'refund_reference',
        'refunded_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
            'inspected_at' => 'datetime',
            'refunded_at' => 'datetime',
            'late_days' => 'integer',
            'late_fee' => 'integer',
            'damage_fee' => 'integer',
            'deposit_refunded' => 'integer',
            'additional_due' => 'integer',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'inspected_by'
        );
    }
}

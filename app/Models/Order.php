<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_EXPIRED = 'expired';
    public const PAYMENT_FAILED = 'failed';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_RENTED = 'rented';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_LABELS = [
        self::PAYMENT_PENDING => 'Menunggu Pembayaran',
        self::PAYMENT_PAID => 'Lunas',
        self::PAYMENT_EXPIRED => 'Kedaluwarsa',
        self::PAYMENT_FAILED => 'Gagal',
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_PROCESSING => 'Diproses',
        self::STATUS_SHIPPED => 'Dikirim',
        self::STATUS_RENTED => 'Sedang Disewa',
        self::STATUS_RETURNED => 'Sudah Dikembalikan',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    protected $fillable = [
        'user_id',
        'order_code',
        'recipient_name',
        'phone',
        'address',
        'subtotal',
        'deposit_total',
        'grand_total',
        'payment_status',
        'order_status',
        'snap_token',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'deposit_total' => 'integer',
            'grand_total' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->oldestOfMany();
    }

    public function scopeOfPaymentStatus(Builder $query, ?string $status): Builder
    {
        if ($status && array_key_exists($status, self::PAYMENT_LABELS)) {
            return $query->where('payment_status', $status);
        }

        return $query;
    }

    public function scopeOfOrderStatus(Builder $query, ?string $status): Builder
    {
        if ($status && array_key_exists($status, self::STATUS_LABELS)) {
            return $query->where('order_status', $status);
        }

        return $query;
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if ($keyword === null || trim($keyword) === '') {
            return $query;
        }

        $keyword = trim($keyword);

        return $query->where(function (Builder $inner) use ($keyword) {
            $inner->where('order_code', 'like', '%' . $keyword . '%')
                ->orWhere('recipient_name', 'like', '%' . $keyword . '%');
        });
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isPending(): bool
    {
        return $this->payment_status === self::PAYMENT_PENDING;
    }

    public function hasRentItems(): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->contains('type', OrderItem::TYPE_RENT);
        }

        return $this->items()->where('type', OrderItem::TYPE_RENT)->exists();
    }

    public function hasSaleItems(): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->contains('type', OrderItem::TYPE_SALE);
        }

        return $this->items()->where('type', OrderItem::TYPE_SALE)->exists();
    }

    public function paymentStatusLabel(): string
    {
        return self::PAYMENT_LABELS[$this->payment_status] ?? $this->payment_status;
    }

    public function orderStatusLabel(): string
    {
        return self::STATUS_LABELS[$this->order_status] ?? $this->order_status;
    }
}
<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasUniqueSlug;

    public const MODE_SALE = 'sale';
    public const MODE_RENT = 'rent';
    public const MODE_BOTH = 'both';

    public const MODES = [
        self::MODE_SALE,
        self::MODE_RENT,
        self::MODE_BOTH,
    ];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'image',
        'mode',
        'sale_price',
        'rent_price_per_day',
        'deposit',
        'stock_sale',
        'stock_rent',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sale_price' => 'integer',
            'rent_price_per_day' => 'integer',
            'deposit' => 'integer',
            'stock_sale' => 'integer',
            'stock_rent' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfMode(Builder $query, ?string $mode): Builder
    {
        if ($mode && in_array($mode, self::MODES, true)) {
            return $query->where('mode', $mode);
        }

        return $query;
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if ($keyword === null || trim($keyword) === '') {
            return $query;
        }

        return $query->where('name', 'like', '%' . trim($keyword) . '%');
    }

    public function canBeSold(): bool
    {
        return in_array($this->mode, [self::MODE_SALE, self::MODE_BOTH], true);
    }

    public function canBeRented(): bool
    {
        return in_array($this->mode, [self::MODE_RENT, self::MODE_BOTH], true);
    }

    public function modeLabel(): string
    {
        return match ($this->mode) {
            self::MODE_SALE => 'Jual',
            self::MODE_RENT => 'Sewa',
            self::MODE_BOTH => 'Jual & Sewa',
            default => '-',
        };
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->image) {
                return Storage::disk('public')->url($this->image);
            }

            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300">'
                . '<rect width="100%" height="100%" fill="#e5e7eb"/>'
                . '<text x="50%" y="50%" fill="#6b7280" font-family="sans-serif" font-size="22" text-anchor="middle">Wayfarer</text>'
                . '</svg>';

            return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
        });
    }
}
<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUniqueSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function hasProducts(): bool
    {
        return $this->products()->exists();
    }
}
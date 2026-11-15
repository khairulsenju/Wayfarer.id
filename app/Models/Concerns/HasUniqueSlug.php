<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUniqueSlug
{
    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->makeUniqueSlug((string) $model->name);
            }
        });
    }

    public function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $counter = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
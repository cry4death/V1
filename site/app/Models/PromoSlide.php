<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PromoSlide extends Model
{
    protected $fillable = [
        'image',
        'title',
        'subtitle',
        'link_url',
        'button_text',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function imagePublicUrl(?string $fallback = null): ?string
    {
        if ($this->image) {
            return asset($this->image);
        }

        return $fallback ? asset($fallback) : null;
    }
}

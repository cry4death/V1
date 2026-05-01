<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipments';

    protected $fillable = [
        'name',
        'slug',
        'tag',
        'kicker',
        'subtitle',
        'summary',
        'description',
        'image',
        'card_image',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function imagePublicUrl(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    public function cardImagePublicUrl(): ?string
    {
        $img = $this->card_image ?: $this->image;

        return $img ? asset($img) : null;
    }
}

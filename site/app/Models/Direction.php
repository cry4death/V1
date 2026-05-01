<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Direction extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'icon_image', 'image', 'details', 'status', 'sort_order'];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

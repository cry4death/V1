<?php

namespace App\Models;

use App\Support\BlockquoteGuillemets;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Promotion extends Model
{
    protected $fillable = [
        'category_id', 'title', 'slug', 'status',
        'start_date', 'end_date', 'image',
        'short_description', 'full_description', 'items',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'items' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PromotionCategory::class, 'category_id');
    }

    /**
     * URL картинки: пути в public (images/promos/…), новые загрузки из админки — в storage.
     */
    public function imagePublicUrl(?string $fallback = null): string
    {
        $path = $this->image;
        if (blank($path)) {
            return asset($fallback ?? 'images/promos/promo-hero.jpg');
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        return $disk->url($path);
    }

    /**
     * HTML полного описания для страницы акции: line-height + «ёлочки» в blockquote при необходимости.
     */
    public function fullDescriptionForSite(): string
    {
        $html = (string) ($this->full_description ?? '');
        $html = preg_replace(
            '/(?<![\d.])line-height\s*:\s*1(?:\.0)?(?![\d.])(?=\s*[;"\'\s]|;)/iu',
            'line-height: 1.68',
            $html
        ) ?? $html;

        return BlockquoteGuillemets::ensureInHtml($html);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
                     ->where(function ($q) {
                         $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                     })
                     ->where(function ($q) {
                         $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                     });
    }
}

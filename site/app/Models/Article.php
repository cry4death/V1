<?php

namespace App\Models;

use App\Support\BlockquoteGuillemets;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    protected $fillable = [
        'category_id', 'title', 'slug', 'author', 'author_doctor_id',
        'published_at', 'reading_time', 'status',
        'cover_image', 'content', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'reading_time' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function authorDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'author_doctor_id');
    }

    /**
     * URL обложки: старые пути в public (images/blog/…), новые — в storage (blog/covers/…).
     */
    public function coverPublicUrl(): string
    {
        $path = $this->cover_image;
        if (blank($path)) {
            return asset('images/blog/placeholder.jpg');
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

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            if ($article->author_doctor_id) {
                $article->loadMissing('authorDoctor');
                if ($article->authorDoctor) {
                    $article->author = $article->authorDoctor->full_name;
                }
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * HTML для публичной страницы: слишком плотный line-height: 1 из редактора перебивает стили и «слипает» строки.
     */
    public function contentForSite(): string
    {
        $html = (string) ($this->content ?? '');
        $html = preg_replace(
            '/(?<![\d.])line-height\s*:\s*1(?:\.0)?(?![\d.])(?=\s*[;"\'\s]|;)/iu',
            'line-height: 1.68',
            $html
        ) ?? $html;

        return BlockquoteGuillemets::ensureInHtml($html);
    }
}

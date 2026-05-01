<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'doctor_id', 'author_name', 'rating', 'text',
        'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    protected static function booted(): void
    {
        static::saved(function (Review $review): void {
            $review->doctor?->syncRatingFromReviews();
        });

        static::deleted(function (Review $review): void {
            $review->doctor?->syncRatingFromReviews();
        });
    }
}

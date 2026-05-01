<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = [
        'specialization_id', 'last_name', 'first_name', 'middle_name',
        'slug', 'category', 'academic_degree', 'experience_years', 'experience_since', 'patient_age',
        'photo', 'description', 'education', 'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'education' => 'array',
            'experience_years' => 'integer',
            'experience_since' => 'integer',
            'rating' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->first_name} {$this->middle_name}");
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'highest' => 'Высшая категория',
            'first' => 'Первая категория',
            'second' => 'Вторая категория',
            default => '',
        };
    }

    /**
     * Текст для блока «стаж / с какого года» (сайт и мобильное API).
     */
    public function getExperienceSummaryAttribute(): string
    {
        if ($this->experience_since) {
            return "Профессионал с {$this->experience_since} года";
        }

        $y = max(0, (int) $this->experience_years);

        if ($y === 0) {
            return 'Стаж уточняйте в клинике';
        }

        return 'Стаж: '.$y.' '.$this->russianExperienceWord($y);
    }

    protected function russianExperienceWord(int $y): string
    {
        $n = $y % 100;
        if ($n >= 11 && $n <= 14) {
            return 'лет';
        }

        return match ($y % 10) {
            1 => 'год',
            2, 3, 4 => 'года',
            default => 'лет',
        };
    }

    public function getPhotoPublicUrlAttribute(): string
    {
        $path = $this->photo ?: 'images/doctor-profile.png';

        return asset($path);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Средний балл по одобренным отзывам (0, если отзывов нет). Колонка rating для списков и карточек.
     */
    public function syncRatingFromReviews(): void
    {
        if (! $this->exists) {
            return;
        }

        $avg = $this->reviews()->where('status', 'approved')->avg('rating');
        $value = $avg === null ? 0.0 : round((float) $avg, 2);

        static::query()->whereKey($this->getKey())->update(['rating' => $value]);
        $this->setAttribute('rating', $value);
    }
}

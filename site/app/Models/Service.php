<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = [
        'direction_id', 'name', 'slug', 'price', 'duration_minutes',
        'description', 'indications', 'preparation',
        'status', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

    /**
     * Шаг сетки слотов (мин.): из настройки {@see Setting} `booking.slot_step_minutes`,
     * иначе длительность приёма по услуге.
     */
    public function slotStepMinutes(): int
    {
        $raw = Setting::getValue('booking', 'slot_step_minutes');
        if ($raw !== null && $raw !== '' && ctype_digit((string) $raw)) {
            return max(5, min(240, (int) $raw));
        }

        return max(5, min(240, (int) $this->duration_minutes));
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

<?php

namespace App\Models;

use App\Enums\Weekday;
use Database\Factories\DoctorScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $doctor_id
 * @property Weekday $weekday 0 = понедельник … 6 = воскресенье
 * @property Carbon $start_time
 * @property Carbon $end_time
 */
class DoctorSchedule extends Model
{
    /** @use HasFactory<DoctorScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'weekday', 'start_time', 'end_time',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => Weekday::class,
            'start_time' => 'datetime:H:i:s',
            'end_time' => 'datetime:H:i:s',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}

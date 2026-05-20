<?php

namespace App\Models;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id', 'espo_entity_id', 'espo_entity_type',
        'espo_synced_at', 'espo_sync_status', 'espo_sync_error',
        'doctor_id', 'service_id', 'patient_name', 'phone', 'email',
        'type', 'source', 'message', 'status', 'admin_comment', 'preferred_date',
        'start_at', 'end_at',
        'cancellation_reason', 'cancelled_at', 'rescheduled_from_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => AppointmentStatus::class,
            'source' => AppointmentSource::class,
            'preferred_date' => 'datetime',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'espo_synced_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function rescheduledFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'rescheduled_from_id');
    }

    public function rescheduledChildren(): HasMany
    {
        return $this->hasMany(self::class, 'rescheduled_from_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AppointmentEvent::class)->latest('id');
    }

    public function scopeNew($query)
    {
        return $query->where('status', AppointmentStatus::New);
    }

    public function scopeActiveCalendar($query)
    {
        return $query->whereNotIn('status', [
            AppointmentStatus::Cancelled,
            AppointmentStatus::Rescheduled,
        ]);
    }
}

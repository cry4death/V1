<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id', 'espo_entity_id', 'espo_entity_type',
        'doctor_id', 'service_id', 'patient_name', 'phone', 'email',
        'type', 'message', 'status', 'admin_comment', 'preferred_date',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'datetime',
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

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }
}

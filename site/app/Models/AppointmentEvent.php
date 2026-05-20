<?php

namespace App\Models;

use App\Enums\AppointmentEventAction;
use App\Enums\AppointmentEventActor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentEvent extends Model
{
    protected $fillable = [
        'appointment_id',
        'actor_type',
        'actor_id',
        'action',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'actor_type' => AppointmentEventActor::class,
            'action' => AppointmentEventAction::class,
            'payload' => 'array',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function log(
        Appointment $appointment,
        AppointmentEventActor $actorType,
        ?int $actorId,
        AppointmentEventAction $action,
        array $payload = [],
    ): self {
        return self::query()->create([
            'appointment_id' => $appointment->id,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'payload' => $payload === [] ? null : $payload,
        ]);
    }
}

<?php

namespace App\Http\Resources\Api;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Appointment */
class AppointmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'status' => $this->status->value,
            'start_at' => $this->start_at?->toIso8601String(),
            'end_at' => $this->end_at?->toIso8601String(),
            'note' => $this->message,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'rescheduled_from_id' => $this->rescheduled_from_id,
        ];

        if ($this->relationLoaded('service') && $this->service !== null) {
            $data['service'] = (new ServiceListResource($this->service))->resolve();
        }

        if ($this->relationLoaded('doctor') && $this->doctor !== null) {
            $data['doctor'] = (new DoctorListResource($this->doctor))->resolve();
        }

        return $data;
    }
}

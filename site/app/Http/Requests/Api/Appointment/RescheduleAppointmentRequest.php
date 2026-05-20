<?php

namespace App\Http\Requests\Api\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_at' => ['required', 'date'],
            'doctor_id' => ['nullable', 'integer', Rule::exists('doctors', 'id')->where('status', 'active')],
        ];
    }
}

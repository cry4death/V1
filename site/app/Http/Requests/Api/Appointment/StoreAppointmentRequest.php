<?php

namespace App\Http\Requests\Api\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
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
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('status', 'active')],
            'doctor_id' => ['required', 'integer', Rule::exists('doctors', 'id')->where('status', 'active')],
            'start_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}

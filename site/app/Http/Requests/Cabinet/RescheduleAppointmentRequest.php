<?php

namespace App\Http\Requests\Cabinet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RescheduleAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('patient') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_at' => ['required', 'date', 'after:now'],
            'doctor_id' => ['nullable', 'integer', Rule::exists('doctors', 'id')->where('status', 'active')],
        ];
    }
}

<?php

namespace App\Http\Requests\Booking;

use App\Http\Controllers\BookingFlowController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PickSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $pending = session(BookingFlowController::SESSION_PENDING, []);
        $merge = [];
        if (! $this->filled('service') && isset($pending['service_slug'])) {
            $merge['service'] = $pending['service_slug'];
        }
        if (! $this->filled('doctor') && isset($pending['doctor_slug'])) {
            $merge['doctor'] = $pending['doctor_slug'];
        }
        if (! $this->filled('date') && isset($pending['date'])) {
            $merge['date'] = $pending['date'];
        }
        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service' => ['required', 'string', 'max:255', Rule::exists('services', 'slug')->where('status', 'active')],
            'doctor' => ['required', 'string', 'max:255', Rule::exists('doctors', 'slug')->where('status', 'active')],
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }
}

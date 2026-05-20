<?php

namespace App\Http\Requests\Booking;

use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SlotIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service' => ['required', 'string', 'max:255', Rule::exists('services', 'slug')->where('status', 'active')],
            'doctor' => ['required', 'string', 'max:255', Rule::exists('doctors', 'slug')->where('status', 'active')],
            'start_at' => ['required', 'date', 'after:now'],
            'note' => ['nullable', 'string', 'max:500'],
            'processing_consent' => ['required', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'processing_consent' => 'согласие на обработку данных',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v): void {
            $slug = $this->input('service');
            $doctorSlug = $this->input('doctor');
            if (! is_string($slug) || ! is_string($doctorSlug)) {
                return;
            }

            $service = Service::query()->active()->where('slug', $slug)->first();
            $doctor = Doctor::findBySlug($doctorSlug);
            if ($service === null || $doctor === null) {
                return;
            }

            if (! $service->doctors()->whereKey($doctor->id)->exists()) {
                $v->errors()->add('doctor', 'Этот врач не оказывает выбранную услугу.');
            }
        });
    }
}

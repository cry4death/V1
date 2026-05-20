<?php

namespace App\Http\Requests\Booking;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
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
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'start_at' => ['required', 'date', 'after:now'],
            'note' => ['nullable', 'string', 'max:500'],
            'processing_consent' => ['required', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_at.required' => 'Выберите дату и время приёма.',
            'start_at.date' => 'Некорректная дата и время.',
            'start_at.after' => 'Выбранное время уже прошло. Пожалуйста, выберите другое.',
            'processing_consent.required' => 'Необходимо дать согласие на обработку персональных данных.',
            'processing_consent.accepted' => 'Необходимо дать согласие на обработку персональных данных.',
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($v): void {
            $serviceId = $this->input('service_id');
            $doctorId = $this->input('doctor_id');
            if (! is_numeric($serviceId) || ! is_numeric($doctorId)) {
                return;
            }
            $service = Service::query()->find((int) $serviceId);
            if ($service === null || ! $service->doctors()->whereKey((int) $doctorId)->exists()) {
                $v->errors()->add('doctor_id', 'Этот врач не оказывает выбранную услугу.');
            }
        });
    }
}

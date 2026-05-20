<?php

namespace App\Http\Requests\Patient;

use App\Http\Requests\Api\Auth\Concerns\NormalizesPatientPhone;
use App\Support\PatientPhone;
use Illuminate\Foundation\Http\FormRequest;

class PatientPhoneRequest extends FormRequest
{
    use NormalizesPatientPhone;

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
            'phone' => PatientPhone::belarusMobileRules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->normalizePatientPhoneInput($this->input('phone')),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'phone' => 'телефон',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Укажите белорусский номер: 375 и девять цифр (можно без префикса 375 — только 9 цифр номера).',
        ];
    }
}

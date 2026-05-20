<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\Api\Auth\Concerns\NormalizesPatientPhone;
use App\Support\PatientPhone;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequestOtpRequest extends FormRequest
{
    use NormalizesPatientPhone;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->normalizePatientPhoneInput($this->input('phone')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:120', 'regex:/^[а-яёА-ЯЁ\s\-]+$/u'],
            'first_name' => ['required', 'string', 'max:120', 'regex:/^[а-яёА-ЯЁ\s\-]+$/u'],
            'middle_name' => ['nullable', 'string', 'max:120', 'regex:/^[а-яёА-ЯЁ\s\-]*$/u'],
            'birth_date' => ['required', 'date_format:d.m.Y', 'before:today'],
            'gender' => ['required', 'string', 'in:male,female'],
            'phone' => PatientPhone::belarusMobileRules(),
        ];
    }
}

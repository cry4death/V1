<?php

namespace App\Http\Requests\Api\Auth;

use App\Http\Requests\Api\Auth\Concerns\NormalizesPatientPhone;
use App\Support\PatientPhone;
use Illuminate\Foundation\Http\FormRequest;

class LoginVerifyRequest extends FormRequest
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
            'phone' => PatientPhone::belarusMobileRules(),
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
        ];
    }
}

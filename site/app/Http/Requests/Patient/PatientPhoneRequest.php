<?php

namespace App\Http\Requests\Patient;

use App\Support\PatientPhone;
use Illuminate\Foundation\Http\FormRequest;

class PatientPhoneRequest extends FormRequest
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
            'phone' => PatientPhone::rules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->input('phone');
        if (is_string($raw)) {
            $this->merge(['phone' => PatientPhone::normalize($raw)]);
        }
    }
}

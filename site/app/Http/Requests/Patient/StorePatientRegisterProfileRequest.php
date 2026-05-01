<?php

namespace App\Http\Requests\Patient;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientRegisterProfileRequest extends FormRequest
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
            'last_name' => ['required', 'string', 'max:120', 'regex:/^[а-яёА-ЯЁ\s\-]+$/u'],
            'first_name' => ['required', 'string', 'max:120', 'regex:/^[а-яёА-ЯЁ\s\-]+$/u'],
            'middle_name' => ['nullable', 'string', 'max:120', 'regex:/^[а-яёА-ЯЁ\s\-]*$/u'],
            'birth_date' => ['required', 'string', 'regex:/^\d{2}\.\d{2}\.\d{4}$/'],
            'gender' => ['required', 'string', 'in:male,female'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $raw = $this->input('birth_date');
            if (! is_string($raw) || ! preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $raw)) {
                return;
            }
            $parts = explode('.', $raw);
            $d = (int) $parts[0];
            $m = (int) $parts[1];
            $y = (int) $parts[2];
            if ($m < 1 || $m > 12 || $d < 1 || $d > 31 || $y < 1900) {
                $v->errors()->add('birth_date', 'Некорректная дата рождения.');

                return;
            }
            if (! checkdate($m, $d, $y)) {
                $v->errors()->add('birth_date', 'Некорректная дата рождения.');

                return;
            }
            $dt = Carbon::createFromFormat('d.m.Y', $raw)->startOfDay();
            if ($dt->isFuture() || $dt->isToday()) {
                $v->errors()->add('birth_date', 'Укажите дату рождения в прошлом.');
            }
        });
    }
}

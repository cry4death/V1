<?php

namespace App\Support;

class PatientPhone
{
    public static function normalize(string $raw): string
    {
        return preg_replace('/\D+/', '', $raw) ?? '';
    }

    public static function rules(): array
    {
        return ['required', 'string', 'regex:/^[0-9]{10,15}$/'];
    }
}

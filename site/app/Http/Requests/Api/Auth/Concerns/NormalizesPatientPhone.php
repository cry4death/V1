<?php

namespace App\Http\Requests\Api\Auth\Concerns;

use App\Support\PatientPhone;

trait NormalizesPatientPhone
{
    protected function normalizePatientPhoneInput(mixed $phone): string
    {
        if (! is_string($phone)) {
            return '';
        }
        $digits = PatientPhone::normalize($phone);
        if (strlen($digits) === 9 && ! str_starts_with($digits, '375')) {
            $digits = '375'.$digits;
        }

        return $digits;
    }
}

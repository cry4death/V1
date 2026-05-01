<?php

namespace App\Services;

use App\Models\PatientOtp;
use Illuminate\Support\Carbon;

class PatientOtpService
{
    public function issue(string $phone): void
    {
        PatientOtp::query()
            ->where('phone', $phone)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        PatientOtp::query()->create([
            'phone' => $phone,
            'code' => PatientOtp::DEMO_OTP_CODE,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);
    }

    /**
     * MVP: принимается только код 111111 при наличии непросроченной записи.
     */
    public function verifyAndConsume(string $phone, string $code): bool
    {
        if ($code !== PatientOtp::DEMO_OTP_CODE) {
            return false;
        }

        $row = PatientOtp::query()
            ->where('phone', $phone)
            ->where('code', PatientOtp::DEMO_OTP_CODE)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if ($row === null) {
            return false;
        }

        $row->forceFill(['consumed_at' => now()])->save();

        return true;
    }
}

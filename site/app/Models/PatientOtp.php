<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientOtp extends Model
{
    public const DEMO_OTP_CODE = '111111';

    protected $fillable = [
        'phone', 'code', 'expires_at', 'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Services\Sms;

use App\Contracts\SmsSender;
use Illuminate\Support\Facades\Log;

class LogSmsSender implements SmsSender
{
    public function send(string $e164WithoutPlus, string $message): void
    {
        Log::info('[SMS log driver] '.$e164WithoutPlus.' — '.$message);
    }
}

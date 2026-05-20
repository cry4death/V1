<?php

namespace App\Services\Sms;

use App\Contracts\SmsSender;
use Illuminate\Support\Facades\Log;

/**
 * Заглушка под белорусских SMS-провайдеров (BeSMS, Rocket SMS и т.п.).
 * На MVP не вызывает внешние API — только логирует попытку.
 */
class SmsBy implements SmsSender
{
    public function send(string $e164WithoutPlus, string $message): void
    {
        Log::warning('[SmsBy stub] отправка не настроена.', [
            'to' => $e164WithoutPlus,
            'length' => strlen($message),
        ]);
    }
}

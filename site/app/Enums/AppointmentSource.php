<?php

namespace App\Enums;

enum AppointmentSource: string
{
    case Web = 'web';
    case Mobile = 'mobile';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Сайт',
            self::Mobile => 'Мобильное приложение',
        };
    }
}

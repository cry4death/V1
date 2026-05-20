<?php

namespace App\Support;

use App\Rules\BelarusPhone;

class PatientPhone
{
    /**
     * Strip non-digits (E.164 without leading +).
     */
    public static function normalize(string $raw): string
    {
        return preg_replace('/\D+/', '', $raw) ?? '';
    }

    /**
     * Правила валидации телефона для API и веб-форм:
     *  - сначала строгий regex (быстрая проверка формата)
     *  - затем brick/phonenumber (реальный диапазон номеров Беларуси)
     *
     * После нормализации в prepareForValidation значение приходит как `375XXXXXXXXX`.
     *
     * @return list<mixed>
     */
    public static function belarusMobileRules(): array
    {
        return ['required', 'string', 'regex:/^375[0-9]{9}$/', new BelarusPhone];
    }

    /**
     * Правила для веб-форм пациента (алиас).
     *
     * @return list<mixed>
     */
    public static function rules(): array
    {
        return self::belarusMobileRules();
    }
}

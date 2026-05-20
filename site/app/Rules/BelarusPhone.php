<?php

namespace App\Rules;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Валидирует номер телефона как реально существующий белорусский номер.
 *
 * Принимает форматы: +375XXXXXXXXX, 375XXXXXXXXX, 80XXXXXXXXX, XXXXXXXXX (9 цифр).
 * Нормализует к 375XXXXXXXXX (без +) для хранения в БД.
 */
class BelarusPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('Укажите номер телефона.');

            return;
        }

        $e164 = $this->toE164($value);

        if ($e164 === null) {
            $fail('Введите корректный белорусский номер телефона (например, +375291234567).');

            return;
        }

        try {
            $number = PhoneNumber::parse($e164);
        } catch (PhoneNumberParseException) {
            $fail('Введите корректный белорусский номер телефона (например, +375291234567).');

            return;
        }

        // getRegionCode() и getCountryCode() — строки; isValidNumber() отклоняет несуществующие диапазоны
        if (! $number->isValidNumber()) {
            $fail('Такого номера телефона не существует. Проверьте правильность ввода.');

            return;
        }

        if ($number->getRegionCode() !== 'BY') {
            $fail('Принимаются только белорусские номера (+375).');
        }
    }

    /**
     * Нормализует различные форматы в E.164 строку для передачи в brick/phonenumber.
     */
    private function toE164(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        // 80XXXXXXXXX → 375XXXXXXXXX (белорусский внутренний формат)
        if (str_starts_with($digits, '80') && strlen($digits) === 11) {
            $digits = '375'.substr($digits, 2);
        }

        // Только оператор + абонент (9 цифр, например 291234567)
        if (! str_starts_with($digits, '375') && strlen($digits) === 9) {
            $digits = '375'.$digits;
        }

        // Должно быть 375 + 9 цифр = 12 итого
        if (! str_starts_with($digits, '375') || strlen($digits) !== 12) {
            return null;
        }

        return '+'.$digits;
    }
}

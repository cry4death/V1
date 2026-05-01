<?php

namespace App\Support;

final class RussianPlural
{
    /**
     * Склонение существительного после числа (1 отзыв, 2 отзыва, 5 отзывов).
     */
    public static function afterNumber(int $n, string $one, string $two, string $many): string
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;

        if ($n >= 11 && $n <= 19) {
            return $many;
        }

        if ($n1 === 1) {
            return $one;
        }

        if ($n1 >= 2 && $n1 <= 4) {
            return $two;
        }

        return $many;
    }
}

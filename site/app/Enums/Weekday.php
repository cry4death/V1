<?php

namespace App\Enums;

use Carbon\CarbonInterface;

/**
 * День недели для расписания врача в БД: **0 = понедельник … 6 = воскресенье**.
 *
 * Не совпадает с {@see CarbonInterface::dayOfWeek} (там 0 = воскресенье).
 */
enum Weekday: int
{
    case Monday = 0;
    case Tuesday = 1;
    case Wednesday = 2;
    case Thursday = 3;
    case Friday = 4;
    case Saturday = 5;
    case Sunday = 6;

    /**
     * Преобразовать из Carbon/PHP dayOfWeek: 0 = воскресенье … 6 = суббота.
     */
    public static function fromCarbonDayOfWeek(int $carbonDayOfWeek): self
    {
        return match ($carbonDayOfWeek) {
            0 => self::Sunday,
            1 => self::Monday,
            2 => self::Tuesday,
            3 => self::Wednesday,
            4 => self::Thursday,
            5 => self::Friday,
            6 => self::Saturday,
        };
    }

    public function labelRu(): string
    {
        return match ($this) {
            self::Monday => 'Понедельник',
            self::Tuesday => 'Вторник',
            self::Wednesday => 'Среда',
            self::Thursday => 'Четверг',
            self::Friday => 'Пятница',
            self::Saturday => 'Суббота',
            self::Sunday => 'Воскресенье',
        };
    }
}

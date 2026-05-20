<?php

use App\Enums\Weekday;

test('weekday monday is zero and maps from carbon monday', function (): void {
    expect(Weekday::Monday->value)->toBe(0)
        ->and(Weekday::fromCarbonDayOfWeek(1))->toBe(Weekday::Monday)
        ->and(Weekday::fromCarbonDayOfWeek(0))->toBe(Weekday::Sunday);
});

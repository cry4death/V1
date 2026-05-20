<?php

namespace Database\Factories;

use App\Enums\Weekday;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorSchedule>
 */
class DoctorScheduleFactory extends Factory
{
    protected $model = DoctorSchedule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'weekday' => fake()->randomElement([
                Weekday::Monday,
                Weekday::Tuesday,
                Weekday::Wednesday,
                Weekday::Thursday,
                Weekday::Friday,
            ]),
            'start_time' => '08:00:00',
            'end_time' => '13:40:00',
        ];
    }
}

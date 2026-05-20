<?php

namespace Database\Seeders;

use App\Enums\Weekday;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Seeder;

/**
 * Совпадает с дампом {@see dumpSite.sql}: пн–пт 08:00–13:40 для каждого врача.
 * В дампе weekday был 1–5 (пн–пт); в схеме приложения это {@see Weekday} 0–4.
 */
class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $workdays = [
            Weekday::Monday,
            Weekday::Tuesday,
            Weekday::Wednesday,
            Weekday::Thursday,
            Weekday::Friday,
        ];

        foreach (Doctor::query()->orderBy('id')->get() as $doctor) {
            foreach ($workdays as $day) {
                DoctorSchedule::query()->updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'weekday' => $day,
                    ],
                    [
                        'start_time' => '08:00:00',
                        'end_time' => '13:40:00',
                    ]
                );
            }
        }
    }
}

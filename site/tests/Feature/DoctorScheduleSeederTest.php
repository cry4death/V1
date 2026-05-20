<?php

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Specialization;
use Database\Seeders\DoctorScheduleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('doctor schedule seeder creates mon fri slots for every doctor', function (): void {
    $spec = Specialization::query()->create([
        'name' => 'Терапия',
        'slug' => 'terapiya-seed-test',
    ]);

    foreach (['Иванов', 'Петров'] as $i => $last) {
        Doctor::query()->create([
            'specialization_id' => $spec->id,
            'last_name' => $last,
            'first_name' => 'Тест',
            'middle_name' => null,
            'slug' => 'doctor-seed-test-'.$i,
            'category' => 'first',
            'experience_years' => 5,
            'patient_age' => 'both',
            'photo' => null,
            'description' => 'Тест',
            'education' => [],
            'status' => 'active',
            'sort_order' => $i,
        ]);
    }

    $this->seed(DoctorScheduleSeeder::class);

    expect(DoctorSchedule::query()->count())->toBe(10);

    $first = Doctor::query()->first();
    expect($first->schedules)->toHaveCount(5)
        ->and($first->schedules->first()->start_time)->not->toBeNull();
});

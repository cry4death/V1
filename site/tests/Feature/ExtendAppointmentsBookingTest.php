<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Direction;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Specialization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('appointments support extended status and booking timestamps', function (): void {
    $direction = Direction::query()->create([
        'name' => 'Направление тест',
        'slug' => 'napravlenie-test-booking',
        'sort_order' => 0,
    ]);

    $service = Service::query()->create([
        'direction_id' => $direction->id,
        'name' => 'Услуга тест',
        'slug' => 'usluga-test-booking',
        'price' => 0,
        'duration_minutes' => 30,
        'status' => 'active',
        'sort_order' => 0,
    ]);

    $spec = Specialization::factory()->create();
    $doctor = Doctor::factory()->create(['specialization_id' => $spec->id]);

    $start = now()->addDay()->startOfHour();

    $appointment = Appointment::query()->create([
        'doctor_id' => $doctor->id,
        'service_id' => $service->id,
        'patient_name' => 'Тест Пациент',
        'phone' => '375291111111',
        'type' => 'appointment',
        'status' => AppointmentStatus::Cancelled,
        'message' => null,
        'start_at' => $start,
        'end_at' => $start->copy()->addMinutes(30),
        'cancelled_at' => now(),
        'cancellation_reason' => 'Проверка',
    ]);

    $fresh = $appointment->fresh();
    expect($fresh->status)->toBe(AppointmentStatus::Cancelled)
        ->and($fresh->start_at)->not->toBeNull()
        ->and($fresh->end_at)->not->toBeNull();
});

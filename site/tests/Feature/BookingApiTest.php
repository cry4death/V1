<?php

use App\Enums\Weekday;
use App\Jobs\SyncAppointmentToEspo;
use App\Models\Direction;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Specialization;
use App\Notifications\AppointmentCreatedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Setting::setValue('booking', 'min_lead_minutes', '60');
    Setting::setValue('booking', 'cancel_window_hours', '3');
    Setting::setValue('booking', 'slot_step_minutes', '20');
    Notification::fake();
});

afterEach(function (): void {
    Carbon::setTestNow(null);
});

function apiBookingMakeService(): Service
{
    $direction = Direction::query()->create([
        'name' => 'Напр '.fake()->unique()->numerify('###'),
        'slug' => 'dir-api-'.fake()->unique()->numerify('###'),
        'sort_order' => 0,
    ]);

    return Service::query()->create([
        'direction_id' => $direction->id,
        'name' => 'Услуга API',
        'slug' => 'svc-api-'.fake()->unique()->numerify('###'),
        'price' => 0,
        'duration_minutes' => 30,
        'status' => 'active',
        'sort_order' => 0,
    ]);
}

function apiBookingMakeDoctorWithSchedule(Service $service, Weekday $weekday = Weekday::Monday): Doctor
{
    $spec = Specialization::factory()->create();
    $doctor = Doctor::factory()->create(['specialization_id' => $spec->id]);
    $doctor->services()->attach($service->id);

    DoctorSchedule::query()->create([
        'doctor_id' => $doctor->id,
        'weekday' => $weekday,
        'start_time' => '08:00:00',
        'end_time' => '13:40:00',
    ]);

    return $doctor;
}

test('booking doctors returns 422 without service', function (): void {
    $this->getJson('/api/v1/booking/doctors')->assertStatus(422);
});

test('booking dates returns data wrapper and respects range', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00');
    $service = apiBookingMakeService();
    $doctor = apiBookingMakeDoctorWithSchedule($service);

    $response = $this->getJson('/api/v1/booking/dates?service='.$service->slug.'&doctor='.$doctor->slug.'&from=2026-06-08&to=2026-06-10')
        ->assertOk()
        ->assertJsonStructure(['data']);

    expect($response->json('data'))->toContain('2026-06-08');
});

test('booking slots returns app timezone wall datetimes without z offset', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00');
    $service = apiBookingMakeService();
    $doctor = apiBookingMakeDoctorWithSchedule($service, Weekday::Monday);

    $first = $this->getJson('/api/v1/booking/slots?service='.$service->slug.'&doctor='.$doctor->slug.'&date=2026-06-08')
        ->assertOk()
        ->json('data.0');

    expect($first)->toBeString()->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/');
});

test('booking slots mismatch doctor service returns 422', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00');
    $serviceA = apiBookingMakeService();
    $serviceB = apiBookingMakeService();
    $doctor = apiBookingMakeDoctorWithSchedule($serviceA);

    $this->getJson('/api/v1/booking/slots?service='.$serviceB->slug.'&doctor='.$doctor->slug.'&date=2026-06-08')
        ->assertStatus(422)
        ->assertJsonPath('errors.booking.0', 'Этот врач не оказывает выбранную услугу.');
});

test('patient can create appointment via api', function (): void {
    Bus::fake([SyncAppointmentToEspo::class]);
    Carbon::setTestNow('2026-06-08 08:00:00');
    $service = apiBookingMakeService();
    $doctor = apiBookingMakeDoctorWithSchedule($service);
    $patient = Patient::query()->create([
        'phone' => '375291234567',
        'last_name' => 'Тест',
        'first_name' => 'Пациент',
        'middle_name' => null,
        'birth_date' => '1990-05-15',
        'gender' => 'male',
        'password' => bcrypt('x'),
    ]);
    $token = $patient->createToken('mobile')->plainTextToken;

    $start = CarbonImmutable::parse('2026-06-08 11:00:00', config('app.timezone'))->toIso8601String();

    $this->postJson('/api/v1/appointments', [
        'service_id' => $service->id,
        'doctor_id' => $doctor->id,
        'start_at' => $start,
        'note' => 'API',
    ], ['Authorization' => 'Bearer '.$token])
        ->assertCreated()
        ->assertJsonPath('data.note', 'API');

    Bus::assertDispatched(SyncAppointmentToEspo::class);

    $patient = Patient::query()->where('phone', '375291234567')->first();
    expect($patient)->not->toBeNull();
    Notification::assertSentTo($patient, AppointmentCreatedNotification::class);
});

test('patch me updates profile', function (): void {
    $patient = Patient::query()->create([
        'phone' => '375299999999',
        'last_name' => 'Старый',
        'first_name' => 'Имя',
        'middle_name' => null,
        'birth_date' => '1991-01-02',
        'gender' => 'male',
        'password' => bcrypt('x'),
    ]);
    $token = $patient->createToken('mobile')->plainTextToken;

    $this->patchJson('/api/v1/me', [
        'last_name' => 'Новый',
    ], ['Authorization' => 'Bearer '.$token])
        ->assertOk()
        ->assertJsonPath('data.last_name', 'Новый');
});

test('logout revokes current token', function (): void {
    $patient = Patient::query()->create([
        'phone' => '375298888888',
        'last_name' => 'А',
        'first_name' => 'Б',
        'middle_name' => null,
        'birth_date' => '1991-01-02',
        'gender' => 'female',
        'password' => bcrypt('x'),
    ]);
    $plain = $patient->createToken('mobile')->plainTextToken;

    $this->postJson('/api/v1/me/logout', [], ['Authorization' => 'Bearer '.$plain])
        ->assertOk();

    $this->getJson('/api/v1/me', ['Authorization' => 'Bearer '.$plain])
        ->assertUnauthorized();
});

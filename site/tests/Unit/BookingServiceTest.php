<?php

use App\Enums\AppointmentEventAction;
use App\Enums\AppointmentEventActor;
use App\Enums\AppointmentStatus;
use App\Enums\Weekday;
use App\Exceptions\BookingException;
use App\Jobs\SyncAppointmentToEspo;
use App\Models\Appointment;
use App\Models\AppointmentEvent;
use App\Models\Direction;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Specialization;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentCreatedNotification;
use App\Notifications\AppointmentRescheduledNotification;
use App\Services\BookingService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;

function bookingMakePatient(): Patient
{
    return Patient::query()->create([
        'phone' => '37529'.fake()->unique()->numerify('#######'),
        'last_name' => 'Иванов',
        'first_name' => 'Иван',
        'middle_name' => null,
        'birth_date' => '1990-05-15',
        'gender' => 'male',
        'password' => bcrypt('unused'),
    ]);
}

function bookingMakeDoctorWithServiceAndSchedule(Service $service, Weekday $weekday = Weekday::Monday): Doctor
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

function bookingMakeService(): Service
{
    $direction = Direction::query()->create([
        'name' => 'Тест напр '.fake()->unique()->numerify('###'),
        'slug' => 'test-dir-'.fake()->unique()->numerify('###'),
        'sort_order' => 0,
    ]);

    return Service::query()->create([
        'direction_id' => $direction->id,
        'name' => 'Услуга тест',
        'slug' => 'svc-'.fake()->unique()->numerify('###'),
        'price' => 0,
        'duration_minutes' => 30,
        'status' => 'active',
        'sort_order' => 0,
    ]);
}

beforeEach(function (): void {
    Setting::setValue('booking', 'min_lead_minutes', '60');
    Setting::setValue('booking', 'cancel_window_hours', '3');
    Setting::setValue('booking', 'slot_step_minutes', '20');
    Notification::fake();
});

afterEach(function (): void {
    Carbon::setTestNow(null);
});

test('available slots do not overlap existing appointments', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00'); // Monday
    $service = bookingMakeService();
    $doctor = bookingMakeDoctorWithServiceAndSchedule($service);
    $patient = bookingMakePatient();
    $svc = app(BookingService::class);

    $start = CarbonImmutable::parse('2026-06-08 11:00:00', config('app.timezone'));
    $svc->book($patient, $doctor, $service, $start, 'первая');

    $slots = $svc->availableSlots($doctor, $service, $start->startOfDay());
    expect($slots->contains(fn (CarbonImmutable $s) => $s->equalTo($start)))->toBeFalse();
});

test('cannot book outside doctor weekday schedule', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00'); // Monday
    $service = bookingMakeService();
    $doctor = bookingMakeDoctorWithServiceAndSchedule($service, Weekday::Monday);
    $patient = bookingMakePatient();
    $svc = app(BookingService::class);

    $sunday = CarbonImmutable::parse('2026-06-07 11:00:00', config('app.timezone'));
    expect($svc->availableSlots($doctor, $service, $sunday->startOfDay()))->toBeEmpty();

    $svc->book($patient, $doctor, $service, CarbonImmutable::parse('2026-06-08 11:00:00', config('app.timezone')));

    $tooLate = CarbonImmutable::parse('2026-06-08 14:00:00', config('app.timezone'));
    expect(fn () => $svc->book($patient, $doctor, $service, $tooLate))
        ->toThrow(BookingException::class);
});

test('cannot book in the past relative to min lead', function (): void {
    Carbon::setTestNow('2026-06-08 10:00:00');
    $service = bookingMakeService();
    $doctor = bookingMakeDoctorWithServiceAndSchedule($service);
    $patient = bookingMakePatient();
    $svc = app(BookingService::class);

    $tooEarly = CarbonImmutable::parse('2026-06-08 10:30:00', config('app.timezone'));

    expect(fn () => $svc->book($patient, $doctor, $service, $tooEarly))
        ->toThrow(BookingException::class);
});

test('double book same slot fails second time', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00');
    $service = bookingMakeService();
    $doctor = bookingMakeDoctorWithServiceAndSchedule($service);
    $a = bookingMakePatient();
    $b = bookingMakePatient();
    $svc = app(BookingService::class);

    $start = CarbonImmutable::parse('2026-06-08 11:00:00', config('app.timezone'));
    $svc->book($a, $doctor, $service, $start);

    expect(fn () => $svc->book($b, $doctor, $service, $start))
        ->toThrow(BookingException::class);

    expect(Appointment::query()->where('doctor_id', $doctor->id)->activeCalendar()->count())->toBe(1);
});

test('successful book dispatches SyncAppointmentToEspo', function (): void {
    Bus::fake([SyncAppointmentToEspo::class]);
    Carbon::setTestNow('2026-06-08 08:00:00');
    $service = bookingMakeService();
    $doctor = bookingMakeDoctorWithServiceAndSchedule($service);
    $patient = bookingMakePatient();
    $svc = app(BookingService::class);

    $start = CarbonImmutable::parse('2026-06-08 11:00:00', config('app.timezone'));
    $appointment = $svc->book($patient, $doctor, $service, $start);

    Bus::assertDispatched(SyncAppointmentToEspo::class, fn (SyncAppointmentToEspo $job): bool => $job->appointmentId === $appointment->id);
    Notification::assertSentTo($patient, AppointmentCreatedNotification::class);
});

test('available dates returns days with at least one slot', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00'); // Monday
    $service = bookingMakeService();
    $doctor = bookingMakeDoctorWithServiceAndSchedule($service);
    $svc = app(BookingService::class);

    $dates = $svc->availableDates($doctor, $service, CarbonInterval::days(2));
    expect($dates)->toContain('2026-06-08')
        ->and($dates)->not->toContain('2026-06-07'); // Sunday без расписания
});

test('book writes appointment_events created row', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00');
    $service = bookingMakeService();
    $doctor = bookingMakeDoctorWithServiceAndSchedule($service);
    $patient = bookingMakePatient();
    $svc = app(BookingService::class);

    $start = CarbonImmutable::parse('2026-06-08 11:00:00', config('app.timezone'));
    $appointment = $svc->book($patient, $doctor, $service, $start, 'коммент');

    $event = AppointmentEvent::query()->where('appointment_id', $appointment->id)->sole();
    expect($event->actor_type)->toBe(AppointmentEventActor::Patient)
        ->and($event->actor_id)->toBe($patient->id)
        ->and($event->action)->toBe(AppointmentEventAction::Created)
        ->and($event->payload)->toBe(['note' => 'коммент']);
});

test('cancel writes appointment_events cancelled row', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00');
    $service = bookingMakeService();
    $doctor = bookingMakeDoctorWithServiceAndSchedule($service);
    $patient = bookingMakePatient();
    $svc = app(BookingService::class);

    $start = CarbonImmutable::parse('2026-06-08 12:00:00', config('app.timezone'));
    $appointment = $svc->book($patient, $doctor, $service, $start);

    $svc->cancel($appointment, $patient, 'не могу прийти');
    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Cancelled);

    $cancelEvt = AppointmentEvent::query()
        ->where('appointment_id', $appointment->id)
        ->where('action', AppointmentEventAction::Cancelled)
        ->sole();

    expect($cancelEvt->actor_type)->toBe(AppointmentEventActor::Patient)
        ->and($cancelEvt->payload)->toBe(['reason' => 'не могу прийти']);

    Notification::assertSentTo($patient, AppointmentCancelledNotification::class);
});

test('reschedule writes rescheduled on old and created on new appointment', function (): void {
    Carbon::setTestNow('2026-06-08 08:00:00');
    $service = bookingMakeService();
    $doctor = bookingMakeDoctorWithServiceAndSchedule($service);
    $patient = bookingMakePatient();
    $svc = app(BookingService::class);

    $oldStart = CarbonImmutable::parse('2026-06-08 11:00:00', config('app.timezone'));
    $old = $svc->book($patient, $doctor, $service, $oldStart);

    $newStart = CarbonImmutable::parse('2026-06-08 12:00:00', config('app.timezone'));
    $new = $svc->reschedule($patient, $old->fresh(), $newStart);

    $old->refresh();
    expect($old->status)->toBe(AppointmentStatus::Rescheduled)
        ->and($new->rescheduled_from_id)->toBe($old->id);

    $eventsOld = AppointmentEvent::query()->where('appointment_id', $old->id)->orderBy('id')->get();
    expect($eventsOld->pluck('action')->all())->toBe([
        AppointmentEventAction::Created,
        AppointmentEventAction::Rescheduled,
    ]);

    $resEvt = $eventsOld->last();
    expect($resEvt->payload)->toMatchArray([
        'new_appointment_id' => $new->id,
        'new_doctor_id' => $doctor->id,
    ]);

    $newCreated = AppointmentEvent::query()
        ->where('appointment_id', $new->id)
        ->where('action', AppointmentEventAction::Created)
        ->sole();
    expect($newCreated->payload)->toBe(['rescheduled_from_id' => $old->id]);

    Notification::assertSentTo($patient, AppointmentRescheduledNotification::class, function (AppointmentRescheduledNotification $n, array $channels) use ($new): bool {
        return $n->newAppointmentId === $new->id;
    });
});

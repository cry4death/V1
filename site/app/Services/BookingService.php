<?php

namespace App\Services;

use App\Enums\AppointmentEventAction;
use App\Enums\AppointmentEventActor;
use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Enums\Weekday;
use App\Exceptions\BookingException;
use App\Jobs\SendAppointmentReminderJob;
use App\Jobs\SyncAppointmentToEspo;
use App\Models\Appointment;
use App\Models\AppointmentEvent;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Setting;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentCreatedNotification;
use App\Notifications\AppointmentRescheduledNotification;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Даты (Y-m-d) в горизонте, на которые есть хотя бы один свободный слот.
     *
     * @return list<string>
     */
    public function availableDates(Doctor $doctor, Service $service, CarbonInterval $window): array
    {
        $this->assertDoctorOffersService($doctor, $service);

        $tz = config('app.timezone');
        $start = CarbonImmutable::now($tz)->startOfDay();
        $end = $start->add($window);

        $dates = [];
        for ($day = $start; $day->lte($end); $day = $day->addDay()) {
            if ($this->availableSlots($doctor, $service, $day)->isNotEmpty()) {
                $dates[] = $day->format('Y-m-d');
            }
        }

        return $dates;
    }

    /**
     * Даты Y-m-d в диапазоне [rangeStart, rangeEnd], на которые есть хотя бы один слот (не раньше сегодня).
     *
     * @return list<string>
     */
    public function availableDatesBetween(
        Doctor $doctor,
        Service $service,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
    ): array {
        $this->assertDoctorOffersService($doctor, $service);

        $tz = config('app.timezone');
        $today = CarbonImmutable::now($tz)->startOfDay();
        $start = $rangeStart->startOfDay();
        if ($start->lt($today)) {
            $start = $today;
        }
        $end = $rangeEnd->startOfDay();
        if ($end->lt($start)) {
            return [];
        }

        $dates = [];
        for ($day = $start; $day->lte($end); $day = $day->addDay()) {
            if ($this->availableSlots($doctor, $service, $day)->isNotEmpty()) {
                $dates[] = $day->format('Y-m-d');
            }
        }

        return $dates;
    }

    /**
     * Начала свободных слотов на дату (шаг — {@see Service::slotStepMinutes()}).
     *
     * @return Collection<int, CarbonImmutable>
     */
    public function availableSlots(Doctor $doctor, Service $service, CarbonImmutable $date): Collection
    {
        $this->assertDoctorOffersService($doctor, $service);

        $window = $this->scheduleWindowForDate($doctor, $date);
        if ($window === null) {
            return collect();
        }

        [$windowStart, $windowEnd] = $window;
        $step = $service->slotStepMinutes();
        $duration = max(5, (int) $service->duration_minutes);
        $minLead = (int) Setting::getValue('booking', 'min_lead_minutes', '60');
        $earliest = CarbonImmutable::now(config('app.timezone'))->addMinutes($minLead);

        $slots = collect();
        $t = $windowStart;

        while (true) {
            $slotEnd = $t->addMinutes($duration);
            if ($slotEnd->gt($windowEnd)) {
                break;
            }

            if ($t->gte($earliest) && ! $this->doctorHasTimeConflict($doctor, $t, $slotEnd, null)) {
                $slots->push($t);
            }

            $t = $t->addMinutes($step);
        }

        return $slots;
    }

    public function book(
        Patient $patient,
        Doctor $doctor,
        Service $service,
        CarbonImmutable $startsAt,
        ?string $note = null,
        AppointmentSource $source = AppointmentSource::Web,
    ): Appointment {
        $this->assertDoctorOffersService($doctor, $service);
        $this->assertMinLead($startsAt);

        $duration = max(5, (int) $service->duration_minutes);
        $endsAt = $startsAt->addMinutes($duration);

        $appointment = DB::transaction(function () use ($patient, $doctor, $service, $startsAt, $endsAt, $note, $source) {
            Doctor::query()->whereKey($doctor->id)->lockForUpdate()->first();

            $this->assertMinLead($startsAt);

            if (! $this->isSlotWithinSchedule($doctor, $startsAt, $endsAt)) {
                throw new BookingException('Выбранное время вне расписания врача.');
            }

            if ($this->doctorHasTimeConflict($doctor, $startsAt, $endsAt, null)) {
                throw new BookingException('Это время уже занято.');
            }

            $appointment = $patient->appointments()->create([
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'patient_name' => $patient->displayName(),
                'phone' => $patient->phone,
                'email' => null,
                'type' => 'appointment',
                'source' => $source,
                'status' => AppointmentStatus::New,
                'message' => $note,
                'preferred_date' => $startsAt,
                'start_at' => $startsAt,
                'end_at' => $endsAt,
            ]);

            AppointmentEvent::log(
                $appointment,
                AppointmentEventActor::Patient,
                $patient->id,
                AppointmentEventAction::Created,
                $note !== null && $note !== '' ? ['note' => $note] : [],
            );

            return $appointment;
        });

        DB::afterCommit(function () use ($patient, $appointment): void {
            SyncAppointmentToEspo::dispatch($appointment->id);
            $patient->notify(new AppointmentCreatedNotification($appointment->id));
            // Пуш «Вы записались» — синхронно, без ожидания cron/очереди
            if ($patient->fcm_token) {
                SendAppointmentReminderJob::dispatchSync($appointment->id, 'booked');
            }
        });

        return $appointment;
    }

    public function cancel(Appointment $appointment, Patient $patient, ?string $reason = null): void
    {
        if ($appointment->patient_id !== $patient->id) {
            throw new BookingException('Нельзя отменить чужую запись.');
        }

        if (! in_array($appointment->status, [AppointmentStatus::New, AppointmentStatus::Processing], true)) {
            throw new BookingException('Эту запись нельзя отменить.');
        }

        $windowH = (int) Setting::getValue('booking', 'cancel_window_hours', '3');
        if ($appointment->start_at !== null && $appointment->start_at->lte(now()->addHours($windowH))) {
            throw new BookingException('Отмена недоступна: до приёма осталось меньше '.$windowH.' ч.');
        }

        DB::transaction(function () use ($appointment, $patient, $reason): void {
            $appointment->forceFill([
                'status' => AppointmentStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ])->save();

            AppointmentEvent::log(
                $appointment,
                AppointmentEventActor::Patient,
                $patient->id,
                AppointmentEventAction::Cancelled,
                $reason !== null && $reason !== '' ? ['reason' => $reason] : [],
            );
        });

        DB::afterCommit(function () use ($patient, $appointment, $reason): void {
            $this->auditBookingCancel($appointment, $patient, $reason);
            SyncAppointmentToEspo::dispatch($appointment->id);
            $patient->notify(new AppointmentCancelledNotification($appointment->id));
        });
    }

    /**
     * Новая запись на новое время; старая помечается перенесённой.
     */
    public function reschedule(
        Patient $patient,
        Appointment $old,
        CarbonImmutable $newStart,
        ?Doctor $newDoctor = null,
    ): Appointment {
        if ($old->patient_id !== $patient->id) {
            throw new BookingException('Нельзя перенести чужую запись.');
        }

        if (! in_array($old->status, [AppointmentStatus::New, AppointmentStatus::Processing], true)) {
            throw new BookingException('Эту запись нельзя перенести.');
        }

        $doctor = $newDoctor ?? $old->doctor;
        $service = $old->service;
        if ($doctor === null || $service === null) {
            throw new BookingException('Некорректные данные записи.');
        }

        $this->assertDoctorOffersService($doctor, $service);
        $this->assertMinLead($newStart);

        $duration = max(5, (int) $service->duration_minutes);
        $newEnd = $newStart->addMinutes($duration);

        $newAppointment = DB::transaction(function () use ($patient, $doctor, $service, $newStart, $newEnd, $old) {
            Doctor::query()->whereKey($doctor->id)->lockForUpdate()->first();

            if (! $this->isSlotWithinSchedule($doctor, $newStart, $newEnd)) {
                throw new BookingException('Выбранное время вне расписания врача.');
            }

            if ($this->doctorHasTimeConflict($doctor, $newStart, $newEnd, $old->id)) {
                throw new BookingException('Это время уже занято.');
            }

            $created = $patient->appointments()->create([
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'patient_name' => $patient->displayName(),
                'phone' => $patient->phone,
                'email' => null,
                'type' => 'appointment',
                'status' => AppointmentStatus::New,
                'message' => $old->message,
                'preferred_date' => $newStart,
                'start_at' => $newStart,
                'end_at' => $newEnd,
                'rescheduled_from_id' => $old->id,
            ]);

            $old->forceFill([
                'status' => AppointmentStatus::Rescheduled,
                'cancelled_at' => now(),
            ])->save();

            AppointmentEvent::log(
                $old,
                AppointmentEventActor::Patient,
                $patient->id,
                AppointmentEventAction::Rescheduled,
                [
                    'new_appointment_id' => $created->id,
                    'new_start_at' => $newStart->toIso8601String(),
                    'new_doctor_id' => $doctor->id,
                ],
            );

            AppointmentEvent::log(
                $created,
                AppointmentEventActor::Patient,
                $patient->id,
                AppointmentEventAction::Created,
                ['rescheduled_from_id' => $old->id],
            );

            return $created;
        });

        DB::afterCommit(function () use ($patient, $old, $newAppointment): void {
            $this->auditBookingReschedule($old, $newAppointment, $patient);
            SyncAppointmentToEspo::dispatch($old->id);
            SyncAppointmentToEspo::dispatch($newAppointment->id);
            $patient->notify(new AppointmentRescheduledNotification($newAppointment->id));
        });

        return $newAppointment;
    }

    private function auditBookingCancel(Appointment $appointment, Patient $patient, ?string $reason): void
    {
        Log::channel('audit')->info('booking.cancel', [
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'reason' => $reason,
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    private function auditBookingReschedule(Appointment $oldAppointment, Appointment $newAppointment, Patient $patient): void
    {
        Log::channel('audit')->info('booking.reschedule', [
            'old_appointment_id' => $oldAppointment->id,
            'new_appointment_id' => $newAppointment->id,
            'patient_id' => $patient->id,
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    private function assertDoctorOffersService(Doctor $doctor, Service $service): void
    {
        if (! $doctor->services()->where('services.id', $service->id)->exists()) {
            throw new BookingException('Этот врач не оказывает выбранную услугу.');
        }
    }

    private function assertMinLead(CarbonImmutable $startsAt): void
    {
        $minLead = (int) Setting::getValue('booking', 'min_lead_minutes', '60');
        $earliest = CarbonImmutable::now(config('app.timezone'))->addMinutes($minLead);
        if ($startsAt->lt($earliest)) {
            throw new BookingException('Слишком раннее время записи. Выберите более поздний слот.');
        }
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}|null
     */
    private function scheduleWindowForDate(Doctor $doctor, CarbonImmutable $date): ?array
    {
        $weekday = Weekday::fromCarbonDayOfWeek((int) $date->dayOfWeek);
        $schedule = $doctor->schedules()->where('weekday', $weekday)->first();
        if ($schedule === null) {
            return null;
        }

        $tz = config('app.timezone');
        $dateStr = $date->format('Y-m-d');
        $start = CarbonImmutable::parse($dateStr.' '.$this->formatTime($schedule->start_time), $tz);
        $end = CarbonImmutable::parse($dateStr.' '.$this->formatTime($schedule->end_time), $tz);

        return [$start, $end];
    }

    private function isSlotWithinSchedule(Doctor $doctor, CarbonImmutable $startsAt, CarbonImmutable $endsAt): bool
    {
        $window = $this->scheduleWindowForDate($doctor, $startsAt);
        if ($window === null) {
            return false;
        }
        [$wStart, $wEnd] = $window;

        return ! $startsAt->lt($wStart) && ! $endsAt->gt($wEnd);
    }

    private function doctorHasTimeConflict(
        Doctor $doctor,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $exceptAppointmentId,
    ): bool {
        return Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->activeCalendar()
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->when($exceptAppointmentId !== null, fn ($q) => $q->where('id', '!=', $exceptAppointmentId))
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->exists();
    }

    private function formatTime(mixed $time): string
    {
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i:s');
        }

        return (string) $time;
    }
}

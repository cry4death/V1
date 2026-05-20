<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Jobs\SendAppointmentReminderJob;
use App\Models\Appointment;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Отправить FCM-напоминания пациентам о приёмах через ~24 часа';

    public function handle(): int
    {
        $excluded = [
            AppointmentStatus::Cancelled->value,
            AppointmentStatus::Completed->value,
            AppointmentStatus::Rescheduled->value,
        ];

        $totalDispatched = 0;

        // Напоминание за 24 часа
        $window24 = now()->addHours(24);
        $remind24 = Appointment::query()
            ->with('patient:id,fcm_token')
            ->whereBetween('start_at', [
                $window24->copy()->subMinutes(10),
                $window24->copy()->addMinutes(10),
            ])
            ->whereNotIn('status', $excluded)
            ->whereHas('patient', fn ($q) => $q->whereNotNull('fcm_token'))
            ->get();

        foreach ($remind24 as $appointment) {
            SendAppointmentReminderJob::dispatch($appointment->id, 'day');
            $totalDispatched++;
        }

        // Напоминание за 1 час
        $window1 = now()->addHour();
        $remind1 = Appointment::query()
            ->with('patient:id,fcm_token')
            ->whereBetween('start_at', [
                $window1->copy()->subMinutes(10),
                $window1->copy()->addMinutes(10),
            ])
            ->whereNotIn('status', $excluded)
            ->whereHas('patient', fn ($q) => $q->whereNotNull('fcm_token'))
            ->get();

        foreach ($remind1 as $appointment) {
            SendAppointmentReminderJob::dispatch($appointment->id, 'hour');
            $totalDispatched++;
        }

        $this->info("Поставлено в очередь: {$totalDispatched} напоминаний (24ч: {$remind24->count()}, 1ч: {$remind1->count()}).");

        return self::SUCCESS;
    }
}

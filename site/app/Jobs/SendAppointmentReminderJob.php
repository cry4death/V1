<?php

namespace App\Jobs;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound as FcmNotFound;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

/**
 * Типы пуш-уведомлений о записи.
 *
 * booked   — пациент только что записался
 * hour     — напоминание за 1 час до приёма
 * day      — напоминание за 24 часа до приёма
 */
class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly int $appointmentId,
        public readonly string $type = 'day',
    ) {}

    public function handle(Messaging $messaging): void
    {
        $appointment = Appointment::with(['patient', 'doctor', 'service'])
            ->find($this->appointmentId);

        if (! $appointment) {
            return;
        }

        $patient = $appointment->patient;
        if (! $patient || ! $patient->fcm_token) {
            return;
        }

        $doctorName = $appointment->doctor?->full_name ?? 'врача';
        $serviceName = $appointment->service?->name;
        $time = $appointment->start_at
            ? $appointment->start_at->setTimezone('Europe/Minsk')->format('d.m.Y в H:i')
            : null;

        [$title, $body] = match ($this->type) {
            'booked' => [
                'Запись подтверждена',
                $time
                    ? "Вы записаны {$time}. Врач: {$doctorName}"
                    : "Вы записаны. Врач: {$doctorName}",
            ],
            'hour' => [
                'Напоминание: приём через 1 час',
                $serviceName
                    ? "Услуга: {$serviceName}. Врач: {$doctorName}"
                    : "Врач: {$doctorName}",
            ],
            default => [ // 'day'
                $time ? "Напоминание: приём {$time}" : 'Напоминание о предстоящем приёме',
                $serviceName
                    ? "Услуга: {$serviceName}. Врач: {$doctorName}"
                    : "Врач: {$doctorName}",
            ],
        };

        $message = CloudMessage::new()
            ->withToken($patient->fcm_token)
            ->withNotification(Notification::create($title, $body))
            ->withData([
                'type' => 'appointment_reminder',
                'reminder_type' => $this->type,
                'appointment_id' => (string) $appointment->id,
            ]);

        try {
            $messaging->send($message);
            Log::channel('default')->info('FCM push sent', [
                'appointment_id' => $this->appointmentId,
                'reminder_type' => $this->type,
                'patient_id' => $patient->id,
            ]);
        } catch (FcmNotFound $e) {
            // Токен не найден в Firebase — устарел или приложение удалено
            $patient->update(['fcm_token' => null]);
            Log::channel('default')->info('FCM token invalidated (NotFound), cleared', [
                'patient_id' => $patient->id,
            ]);
        } catch (\Throwable $e) {
            Log::channel('default')->warning('FCM push failed', [
                'appointment_id' => $this->appointmentId,
                'reminder_type' => $this->type,
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), 'UNREGISTERED') ||
                str_contains($e->getMessage(), 'INVALID_ARGUMENT') ||
                str_contains($e->getMessage(), 'NOT_FOUND') ||
                str_contains($e->getMessage(), 'Requested entity was not found')) {
                $patient->update(['fcm_token' => null]);

                return;
            }

            throw $e;
        }
    }
}

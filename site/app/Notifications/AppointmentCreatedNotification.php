<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AppointmentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $appointmentId,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * @return list<string|class-string>
     */
    public function via(object $notifiable): array
    {
        $channels = [SmsChannel::class];
        if (method_exists($notifiable, 'routeNotificationForMail')
            && $notifiable->routeNotificationForMail() !== null) {
            array_unshift($channels, 'mail');
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->loadAppointment();

        return (new MailMessage)
            ->subject('Подтверждение записи — '.$appointment->service?->name)
            ->markdown('mail.appointments.created', [
                'patientName' => $notifiable->displayName(),
                'appointment' => $appointment,
                'cancelUrl' => URL::temporarySignedRoute(
                    'booking.guest-cancel',
                    now()->addDays(60),
                    ['appointment' => $appointment->id],
                ),
            ]);
    }

    public function toSms(object $notifiable): string
    {
        $appointment = $this->loadAppointment();
        $dt = $appointment->start_at?->timezone(config('app.timezone'))->format('d.m.Y H:i');
        $doctor = $appointment->doctor?->last_name.' '.$appointment->doctor?->first_name;
        $svc = $appointment->service?->name ?? '';
        $cancel = URL::temporarySignedRoute(
            'booking.guest-cancel',
            now()->addDays(60),
            ['appointment' => $appointment->id],
        );

        return trim("Запись: {$dt}. {$doctor}. {$svc}. Отмена: {$cancel}");
    }

    private function loadAppointment(): Appointment
    {
        return Appointment::query()
            ->with(['doctor', 'service'])
            ->findOrFail($this->appointmentId);
    }
}

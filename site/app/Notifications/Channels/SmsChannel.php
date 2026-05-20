<?php

namespace App\Notifications\Channels;

use App\Contracts\SmsSender;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(
        private readonly SmsSender $smsSender,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $text = $notification->toSms($notifiable);
        if (! is_string($text) || $text === '') {
            return;
        }

        if (! method_exists($notifiable, 'routeNotificationForSms')) {
            return;
        }

        $phone = $notifiable->routeNotificationForSms();
        if (! is_string($phone) || $phone === '') {
            return;
        }

        $this->smsSender->send($phone, $text);
    }
}

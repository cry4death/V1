<?php

namespace App\Enums;

enum AppointmentEventAction: string
{
    case Created = 'created';
    case Rescheduled = 'rescheduled';
    case Cancelled = 'cancelled';
    case StatusChanged = 'status_changed';
}

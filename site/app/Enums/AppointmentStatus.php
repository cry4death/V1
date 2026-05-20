<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case New = 'new';
    case Processing = 'processing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rescheduled = 'rescheduled';
}

<?php

namespace App\Enums;

enum AppointmentEventActor: string
{
    case Patient = 'patient';
    case Admin = 'admin';
    case System = 'system';
}

<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\Crm\EspoCrmSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * После коммита в БД: Contact + Meeting в EspoCRM (очередь `crm`).
 */
class SyncAppointmentToEspo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $appointmentId)
    {
        $this->onQueue('crm');
    }

    public function handle(EspoCrmSyncService $crm): void
    {
        $appointment = Appointment::query()->find($this->appointmentId);
        if ($appointment === null) {
            return;
        }

        $crm->syncAppointment($appointment);
    }
}

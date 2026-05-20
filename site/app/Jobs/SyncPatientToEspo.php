<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Services\Crm\EspoCrmSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPatientToEspo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $patientId)
    {
        $this->onQueue('crm');
    }

    public function handle(EspoCrmSyncService $crm): void
    {
        $patient = Patient::query()->find($this->patientId);
        if ($patient === null) {
            return;
        }

        $crm->syncPatient($patient);
    }
}

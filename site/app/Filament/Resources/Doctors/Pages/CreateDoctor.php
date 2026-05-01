<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Resources\Doctors\Schemas\DoctorForm;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return DoctorForm::mergeEducationRepeatersIntoStoredFormat($data);
    }

    protected function afterCreate(): void
    {
        $this->record->syncRatingFromReviews();
    }
}

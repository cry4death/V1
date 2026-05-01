<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Filament\Resources\Doctors\DoctorResource;
use App\Filament\Resources\Doctors\Schemas\DoctorForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDoctor extends EditRecord
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $education = $data['education'] ?? null;
        $education = is_array($education) ? $education : [];

        $split = DoctorForm::splitEducationForForm($education);
        $data['work_experience'] = $split['work_experience'];
        $data['education_qualifications'] = $split['education_qualifications'];
        unset($data['education']);

        $data['slug_locked'] = true;

        $photo = $data['photo'] ?? null;
        if (is_string($photo) && $photo !== '' && ! str_contains($photo, '/') && ! str_contains($photo, '\\')) {
            foreach (['images/doctors/'.$photo, 'images/'.$photo] as $path) {
                if (Storage::disk('public_assets')->exists($path)) {
                    $data['photo'] = $path;
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return DoctorForm::mergeEducationRepeatersIntoStoredFormat($data);
    }

    protected function afterSave(): void
    {
        $this->record->syncRatingFromReviews();
    }
}

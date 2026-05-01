<?php

namespace App\Filament\Resources\Specializations\Pages;

use App\Filament\Resources\Specializations\SpecializationResource;
use App\Models\Specialization;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditSpecialization extends EditRecord
{
    protected static string $resource = SpecializationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (Specialization $record): void {
                    if ($record->doctors()->exists()) {
                        Notification::make()
                            ->title('Нельзя удалить')
                            ->body('Сначала переназначьте врачей с другой специализацией.')
                            ->danger()
                            ->send();
                        throw new Halt;
                    }
                }),
        ];
    }
}

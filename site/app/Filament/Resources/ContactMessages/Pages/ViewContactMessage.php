<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markRead')
                ->label('Отметить прочитанным')
                ->icon(Heroicon::CheckCircle)
                ->color('success')
                ->visible(fn (): bool => $this->record->status === 'new')
                ->action(function (): void {
                    /** @var ContactMessage $record */
                    $record = $this->record;
                    $record->update(['status' => 'read']);
                    $this->refreshFormData(['status']);
                    Notification::make()->title('Отмечено прочитанным')->success()->send();
                }),

            Action::make('archive')
                ->label('В архив')
                ->icon(Heroicon::ArchiveBox)
                ->color('gray')
                ->visible(fn (): bool => $this->record->status !== 'archived')
                ->action(function (): void {
                    /** @var ContactMessage $record */
                    $record = $this->record;
                    $record->update(['status' => 'archived']);
                    $this->refreshFormData(['status']);
                    Notification::make()->title('Перемещено в архив')->success()->send();
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Автоматически отмечаем как прочитанное при открытии
        if ($this->record->status === 'new') {
            $this->record->update(['status' => 'read']);
            $data['status'] = 'read';
        }

        return $data;
    }
}

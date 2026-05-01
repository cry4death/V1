<?php

namespace App\Filament\Resources\Directions\Pages;

use App\Filament\Resources\Directions\Concerns\NormalizesDirectionPayload;
use App\Filament\Resources\Directions\DirectionResource;
use App\Models\Direction;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\Storage;

class EditDirection extends EditRecord
{
    use NormalizesDirectionPayload;

    protected static string $resource = DirectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (Direction $record): void {
                    if ($record->services()->exists()) {
                        Notification::make()
                            ->title('Нельзя удалить')
                            ->body('Сначала удалите или перенесите услуги в другую категорию.')
                            ->danger()
                            ->send();
                        throw new Halt;
                    }
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (['image', 'icon_image'] as $field) {
            $path = $data[$field] ?? null;
            if (! is_string($path) || $path === '' || str_contains($path, '/') || str_contains($path, '\\')) {
                continue;
            }
            $candidates = match ($field) {
                'icon_image' => ['images/directions/icons/'.$path, 'images/directions/'.$path, 'images/'.$path],
                default => ['images/directions/'.$path, 'images/'.$path],
            };
            foreach ($candidates as $full) {
                if (Storage::disk('public_assets')->exists($full)) {
                    $data[$field] = $full;
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
        return $this->normalizeDirectionPayload($data);
    }
}
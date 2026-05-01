<?php

namespace App\Filament\Resources\Directions\Pages;

use App\Filament\Resources\Directions\Concerns\NormalizesDirectionPayload;
use App\Filament\Resources\Directions\DirectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDirection extends CreateRecord
{
    use NormalizesDirectionPayload;

    protected static string $resource = DirectionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeDirectionPayload($data);
    }
}
<?php

namespace App\Filament\Resources\PromoSlides\Pages;

use App\Filament\Resources\PromoSlides\PromoSlideResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPromoSlide extends EditRecord
{
    protected static string $resource = PromoSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

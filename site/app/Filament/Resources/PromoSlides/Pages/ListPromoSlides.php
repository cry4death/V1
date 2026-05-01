<?php

namespace App\Filament\Resources\PromoSlides\Pages;

use App\Filament\Resources\PromoSlides\PromoSlideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromoSlides extends ListRecords
{
    protected static string $resource = PromoSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Добавить слайд'),
        ];
    }
}

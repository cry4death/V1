<?php

namespace App\Filament\Resources\PromotionCategories\Pages;

use App\Filament\Resources\PromotionCategories\PromotionCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromotionCategories extends ListRecords
{
    protected static string $resource = PromotionCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Добавить категорию')];
    }
}

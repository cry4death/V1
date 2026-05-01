<?php

namespace App\Filament\Resources\PromotionCategories\Pages;

use App\Filament\Resources\PromotionCategories\PromotionCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPromotionCategory extends EditRecord
{
    protected static string $resource = PromotionCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}

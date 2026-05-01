<?php

namespace App\Filament\Resources\PromoSlides;

use App\Filament\Resources\PromoSlides\Pages\CreatePromoSlide;
use App\Filament\Resources\PromoSlides\Pages\EditPromoSlide;
use App\Filament\Resources\PromoSlides\Pages\ListPromoSlides;
use App\Filament\Resources\PromoSlides\Schemas\PromoSlideForm;
use App\Filament\Resources\PromoSlides\Tables\PromoSlidesTable;
use App\Models\PromoSlide;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromoSlideResource extends Resource
{
    protected static ?string $model = PromoSlide::class;

    protected static ?string $navigationLabel = 'Слайдер акций';

    protected static ?string $modelLabel = 'Слайд';

    protected static ?string $pluralModelLabel = 'Слайды';

    protected static string|\UnitEnum|null $navigationGroup = 'Акции';

    protected static ?int $navigationSort = 30;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    public static function form(Schema $schema): Schema
    {
        return PromoSlideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromoSlidesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromoSlides::route('/'),
            'create' => CreatePromoSlide::route('/create'),
            'edit' => EditPromoSlide::route('/{record}/edit'),
        ];
    }
}

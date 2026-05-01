<?php

namespace App\Filament\Resources\Directions;

use App\Filament\Clusters\ClinicCategoriesCluster;
use App\Filament\Resources\Directions\Pages\CreateDirection;
use App\Filament\Resources\Directions\Pages\EditDirection;
use App\Filament\Resources\Directions\Pages\ListDirections;
use App\Filament\Resources\Directions\Schemas\DirectionForm;
use App\Filament\Resources\Directions\Tables\DirectionsTable;
use App\Models\Direction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DirectionResource extends Resource
{
    protected static ?string $model = Direction::class;

    protected static ?string $cluster = ClinicCategoriesCluster::class;

    protected static ?string $navigationLabel = 'Категории услуг';

    protected static ?string $modelLabel = 'Категория услуг';

    protected static ?string $pluralModelLabel = 'Категории услуг';

    protected static ?int $navigationSort = 20;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    public static function form(Schema $schema): Schema
    {
        return DirectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DirectionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDirections::route('/'),
            'create' => CreateDirection::route('/create'),
            'edit' => EditDirection::route('/{record}/edit'),
        ];
    }
}

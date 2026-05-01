<?php

namespace App\Filament\Resources\Specializations;

use App\Filament\Clusters\ClinicCategoriesCluster;
use App\Filament\Resources\Specializations\Pages\CreateSpecialization;
use App\Filament\Resources\Specializations\Pages\EditSpecialization;
use App\Filament\Resources\Specializations\Pages\ListSpecializations;
use App\Filament\Resources\Specializations\Schemas\SpecializationForm;
use App\Filament\Resources\Specializations\Tables\SpecializationsTable;
use App\Models\Specialization;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SpecializationResource extends Resource
{
    protected static ?string $model = Specialization::class;

    protected static ?string $cluster = ClinicCategoriesCluster::class;

    protected static ?string $navigationLabel = 'Специализации врачей';

    protected static ?string $modelLabel = 'Специализация';

    protected static ?string $pluralModelLabel = 'Специализации врачей';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public static function form(Schema $schema): Schema
    {
        return SpecializationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpecializationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpecializations::route('/'),
            'create' => CreateSpecialization::route('/create'),
            'edit' => EditSpecialization::route('/{record}/edit'),
        ];
    }
}

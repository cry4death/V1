<?php

namespace App\Filament\Resources\PromotionCategories;

use App\Filament\Resources\PromotionCategories\Pages\CreatePromotionCategory;
use App\Filament\Resources\PromotionCategories\Pages\EditPromotionCategory;
use App\Filament\Resources\PromotionCategories\Pages\ListPromotionCategories;
use App\Models\PromotionCategory;
use App\Support\Slug;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromotionCategoryResource extends Resource
{
    protected static ?string $model = PromotionCategory::class;

    protected static ?string $navigationLabel = 'Категории акций';

    protected static ?string $modelLabel = 'Категория акций';

    protected static ?string $pluralModelLabel = 'Категории акций';

    protected static string|\UnitEnum|null $navigationGroup = 'Акции';

    protected static ?int $navigationSort = 20;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                    if (! filled($state) || filled($get('slug'))) {
                        return;
                    }
                    $set('slug', Slug::make($state));
                }),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Название')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug')->toggleable(),
                TextColumn::make('promotions_count')
                    ->label('Акций')
                    ->counts('promotions')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([EditAction::make()->label('Изменить')])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotionCategories::route('/'),
            'create' => CreatePromotionCategory::route('/create'),
            'edit' => EditPromotionCategory::route('/{record}/edit'),
        ];
    }
}

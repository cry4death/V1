<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Support\Slug;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('direction_id')
                    ->label('Категория (направление)')
                    ->relationship('direction', 'name', modifyQueryUsing: fn ($query) => $query->orderBy('sort_order')->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),

                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                        if (! filled($state) || filled($get('slug'))) {
                            return;
                        }
                        $set('slug', Slug::make($state));
                    }),

                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Уникальный адрес страницы услуги.'),

                TextInput::make('price')
                    ->label('Цена от (BYN)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->suffix('BYN')
                    ->helperText('0 — на странице цена не показывается в списке направления.'),

                Textarea::make('description')
                    ->label('Описание')
                    ->rows(4)
                    ->columnSpanFull(),

                Textarea::make('indications')
                    ->label('Показания')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('preparation')
                    ->label('Подготовка к процедуре')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Порядок в списке')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'На сайте',
                        'hidden' => 'Скрыто',
                    ])
                    ->default('active')
                    ->required()
                    ->native(false),
            ]);
    }
}

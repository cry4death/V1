<?php

namespace App\Filament\Resources\Vacancies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VacancyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Название вакансии')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Описание (опционально)')
                    ->rows(4)
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Select::make('is_active')
                    ->label('Статус')
                    ->options([
                        1 => 'Активен',
                        0 => 'Скрыт',
                    ])
                    ->default(1)
                    ->required()
                    ->native(false),
            ]);
    }
}

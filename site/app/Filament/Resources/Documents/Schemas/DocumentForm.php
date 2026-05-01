<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Название документа')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('label')
                    ->label('Метка / категория (бейдж)')
                    ->placeholder('Например: Конфиденциальность')
                    ->maxLength(120),

                TextInput::make('url')
                    ->label('Ссылка на документ')
                    ->required()
                    ->url()
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->placeholder('https://example.com/document.pdf'),

                Textarea::make('text')
                    ->label('Краткое описание')
                    ->rows(3)
                    ->maxLength(500)
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

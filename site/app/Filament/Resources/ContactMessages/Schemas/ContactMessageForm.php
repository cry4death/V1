<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->disabled(),

                TextInput::make('email')
                    ->label('Email')
                    ->disabled(),

                TextInput::make('phone')
                    ->label('Телефон')
                    ->disabled(),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'new'      => 'Новое',
                        'read'     => 'Прочитано',
                        'archived' => 'В архиве',
                    ])
                    ->required()
                    ->native(false),

                Textarea::make('message')
                    ->label('Сообщение')
                    ->disabled()
                    ->rows(6)
                    ->columnSpanFull(),

                Textarea::make('admin_note')
                    ->label('Заметка администратора')
                    ->rows(3)
                    ->placeholder('Внутренние заметки — не видны посетителю')
                    ->columnSpanFull(),
            ]);
    }
}

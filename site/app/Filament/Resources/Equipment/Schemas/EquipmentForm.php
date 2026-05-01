<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Filament\Support\LocalPublicFileUpload;
use App\Support\Slug;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                        if (! filled($state)) {
                            return;
                        }
                        if (! filled($get('slug')) || ! $get('slug_locked')) {
                            $set('slug', Slug::make($state));
                        }
                    }),

                TextInput::make('slug')
                    ->label('URL (slug)')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set) => $set('slug_locked', true))
                    ->helperText('Латиница, дефисы. Используется в адресе /equipment/{slug}'),

                TextInput::make('tag')
                    ->label('Тег / категория (бейдж в карточке)')
                    ->maxLength(120),

                TextInput::make('kicker')
                    ->label('Надзаголовок страницы')
                    ->placeholder('Медицинское оборудование')
                    ->maxLength(160),

                TextInput::make('subtitle')
                    ->label('Подзаголовок страницы (h2)')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('summary')
                    ->label('Краткое описание (для карточки)')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Полное описание (для страницы)')
                    ->rows(10)
                    ->columnSpanFull()
                    ->helperText('Поддерживаются абзацы — разделяйте пустой строкой.'),

                Section::make('Изображения')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Главное изображение (страница)')
                            ->image()
                            ->disk('public_assets')
                            ->directory('images/medical_equipment')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->panelLayout('integrated')
                            ->imagePreviewHeight('320')
                            ->deletable(true)
                            ->downloadable(true)
                            ->openable(true)
                            ->getUploadedFileUsing(LocalPublicFileUpload::uploadedFileUsing())
                            ->columnSpanFull(),

                        FileUpload::make('card_image')
                            ->label('Изображение для карточки (опционально)')
                            ->image()
                            ->disk('public_assets')
                            ->directory('images/medical_equipment')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->imagePreviewHeight('320')
                            ->deletable(true)
                            ->downloadable(true)
                            ->openable(true)
                            ->getUploadedFileUsing(LocalPublicFileUpload::uploadedFileUsing())
                            ->columnSpanFull()
                            ->helperText('Если не задано — используется главное изображение.'),
                    ])
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Порядок')
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

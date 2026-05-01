<?php

namespace App\Filament\Resources\PromoSlides\Schemas;

use App\Filament\Support\LocalPublicFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PromoSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image')
                    ->label('Изображение слайда')
                    ->image()
                    ->disk('public_assets')
                    ->directory('images/promo-slides')
                    ->visibility('public')
                    ->maxSize(5120)
                    ->panelLayout('integrated')
                    ->imagePreviewHeight('320')
                    ->deletable(true)
                    ->downloadable(true)
                    ->openable(true)
                    ->getUploadedFileUsing(LocalPublicFileUpload::uploadedFileUsing())
                    ->columnSpanFull()
                    ->required(),

                TextInput::make('title')
                    ->label('Заголовок (опционально)')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('subtitle')
                    ->label('Подзаголовок (опционально)')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('button_text')
                    ->label('Текст кнопки (опционально)')
                    ->maxLength(120),

                TextInput::make('link_url')
                    ->label('Ссылка')
                    ->placeholder('/promotions/some-promo')
                    ->maxLength(500)
                    ->helperText('Куда ведёт клик по слайду. Если пусто — клик не сработает.'),

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

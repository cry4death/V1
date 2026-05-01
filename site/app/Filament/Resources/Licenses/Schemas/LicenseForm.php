<?php

namespace App\Filament\Resources\Licenses\Schemas;

use App\Filament\Support\LocalPublicFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('caption')
                    ->label('Название / подпись')
                    ->maxLength(255)
                    ->columnSpanFull(),

                FileUpload::make('image')
                    ->label('Изображение лицензии')
                    ->image()
                    ->required()
                    ->disk('public_assets')
                    ->directory('images/licenses')
                    ->visibility('public')
                    ->maxSize(10240)
                    ->panelLayout('integrated')
                    ->imagePreviewHeight('320')
                    ->deletable(true)
                    ->downloadable(true)
                    ->openable(true)
                    ->getUploadedFileUsing(LocalPublicFileUpload::uploadedFileUsing())
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }
}

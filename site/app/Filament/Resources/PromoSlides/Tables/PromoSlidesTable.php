<?php

namespace App\Filament\Resources\PromoSlides\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromoSlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Изображение')
                    ->disk('public_assets')
                    ->imageHeight(80),

                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(function ($state): string {
                        $on = $state === true || $state === 1 || $state === '1';

                        return $on ? 'Активен' : 'Скрыт';
                    })
                    ->color(function ($state): string {
                        $on = $state === true || $state === 1 || $state === '1';

                        return $on ? 'success' : 'gray';
                    }),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Изменить'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

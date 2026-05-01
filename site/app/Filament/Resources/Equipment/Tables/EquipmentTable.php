<?php

namespace App\Filament\Resources\Equipment\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Фото')
                    ->disk('public_assets')
                    ->height(60),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('tag')
                    ->label('Тег')
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'На сайте' : 'Скрыто')
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'active' => 'На сайте',
                    'hidden' => 'Скрыто',
                ]),
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

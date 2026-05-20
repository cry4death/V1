<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('direction.name')
                    ->label('Категория')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Цена')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' BYN')
                    ->sortable(),

                TextColumn::make('duration_minutes')
                    ->label('Мин.')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'На сайте',
                        default => 'Скрыто',
                    })
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),

                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('direction_id')
                    ->label('Категория')
                    ->relationship('direction', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'На сайте',
                        'hidden' => 'Скрыто',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

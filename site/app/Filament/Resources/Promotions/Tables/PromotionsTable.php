<?php

namespace App\Filament\Resources\Promotions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Фото')
                    ->imageHeight(40)
                    ->checkFileExistence(false)
                    ->getStateUsing(fn ($record): ?string => filled($record->image) ? $record->imagePublicUrl() : null),

                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'На сайте',
                        'completed' => 'Завершена',
                        default => 'Черновик',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Начало')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Окончание')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'active' => 'На сайте',
                        'completed' => 'Завершена',
                    ]),
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('start_date', 'desc')
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

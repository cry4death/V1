<?php

namespace App\Filament\Resources\Vacancies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VacanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Вакансия')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (?bool $state): string => ($state ?? false) ? 'Активна' : 'Скрыта')
                    ->color(fn (?bool $state): string => ($state ?? false) ? 'success' : 'gray'),
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

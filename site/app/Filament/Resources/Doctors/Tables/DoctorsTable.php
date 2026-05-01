<?php

namespace App\Filament\Resources\Doctors\Tables;

use App\Models\Doctor;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('Фото')
                    ->height(48)
                    ->square()
                    ->checkFileExistence(false)
                    ->getStateUsing(fn (Doctor $record): string => $record->photo
                        ? asset($record->photo)
                        : asset('images/doctors/doctor-placeholder.jpg')),

                TextColumn::make('last_name')
                    ->label('Фамилия')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('first_name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('middle_name')
                    ->label('Отчество')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('specialization.name')
                    ->label('Специализация')
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category')
                    ->label('Категория')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'highest' => 'Высшая',
                        'first' => 'Первая',
                        'second' => 'Вторая',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'highest' => 'success',
                        'first' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('experience_years')
                    ->label('Стаж')
                    ->numeric()
                    ->suffix(' л.')
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Рейтинг')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->description('По одобренным отзывам'),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Активен',
                        default => 'Скрыт',
                    })
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),

                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable(),

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
                        'active' => 'Активен',
                        'inactive' => 'Скрыт',
                    ]),
                SelectFilter::make('specialization_id')
                    ->label('Специализация')
                    ->relationship('specialization', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('sort_order')
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

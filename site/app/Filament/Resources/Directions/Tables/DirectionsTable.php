<?php

namespace App\Filament\Resources\Directions\Tables;

use App\Models\Direction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DirectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'На сайте',
                        default => 'Скрыто',
                    })
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),

                TextColumn::make('services_count')
                    ->counts('services')
                    ->label('Услуг')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (Direction $record): void {
                        if ($record->services()->exists()) {
                            Notification::make()
                                ->title('Нельзя удалить')
                                ->body('В категории есть услуги. Сначала перенесите или удалите их.')
                                ->danger()
                                ->send();
                            throw new Halt;
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (Collection $records): void {
                            foreach ($records as $record) {
                                /** @var Direction $record */
                                if ($record->services()->exists()) {
                                    Notification::make()
                                        ->title('Нельзя удалить')
                                        ->body('У категории «'.$record->name.'» есть услуги.')
                                        ->danger()
                                        ->send();
                                    throw new Halt;
                                }
                            }
                        }),
                ]),
            ]);
    }
}

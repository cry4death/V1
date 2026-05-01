<?php

namespace App\Filament\Resources\Specializations\Tables;

use App\Models\Specialization;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SpecializationsTable
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

                TextColumn::make('doctors_count')
                    ->counts('doctors')
                    ->label('Врачей')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (Specialization $record): void {
                        if ($record->doctors()->exists()) {
                            Notification::make()
                                ->title('Нельзя удалить')
                                ->body('У этой специализации есть врачи. Сначала переназначьте их.')
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
                                /** @var Specialization $record */
                                if ($record->doctors()->exists()) {
                                    Notification::make()
                                        ->title('Нельзя удалить')
                                        ->body('У специализации «'.$record->name.'» есть врачи. Сначала переназначьте их.')
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

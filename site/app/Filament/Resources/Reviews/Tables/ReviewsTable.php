<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('doctor.full_name')
                    ->label('Врач')
                    ->searchable(['doctors.last_name', 'doctors.first_name', 'doctors.middle_name'])
                    ->sortable()
                    ->wrap(),

                TextColumn::make('author_name')
                    ->label('Автор')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rating')
                    ->label('Оценка')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state).str_repeat('☆', 5 - $state))
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state === 3 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                TextColumn::make('text')
                    ->label('Текст')
                    ->limit(80)
                    ->wrap()
                    ->tooltip(fn (Review $record): string => $record->text),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Одобрен',
                        'rejected' => 'Отклонён',
                        default => 'На модерации',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Опубликован')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Получен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'На модерации',
                        'approved' => 'Одобрен',
                        'rejected' => 'Отклонён',
                    ])
                    ->default('pending'),

                SelectFilter::make('doctor_id')
                    ->label('Врач')
                    ->relationship(
                        name: 'doctor',
                        titleAttribute: 'last_name',
                        modifyQueryUsing: fn ($query) => $query->orderBy('last_name')->orderBy('first_name'),
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('rating')
                    ->label('Оценка')
                    ->options([
                        5 => '5 ★',
                        4 => '4 ★',
                        3 => '3 ★',
                        2 => '2 ★',
                        1 => '1 ★',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Одобрить')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (Review $record): bool => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Опубликовать отзыв?')
                    ->modalDescription('Отзыв появится на странице врача.')
                    ->modalSubmitActionLabel('Опубликовать')
                    ->action(function (Review $record): void {
                        $record->update([
                            'status' => 'approved',
                            'published_at' => $record->published_at ?? now(),
                        ]);

                        Notification::make()
                            ->title('Отзыв опубликован')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Отклонить')
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->visible(fn (Review $record): bool => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->modalHeading('Отклонить отзыв?')
                    ->modalDescription('Отзыв не будет показан на сайте. Его можно будет одобрить позже.')
                    ->modalSubmitActionLabel('Отклонить')
                    ->action(function (Review $record): void {
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->title('Отзыв отклонён')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Изменить'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveBulk')
                        ->label('Одобрить выбранные')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $now = now();
                            $count = 0;
                            foreach ($records as $record) {
                                /** @var Review $record */
                                $record->update([
                                    'status' => 'approved',
                                    'published_at' => $record->published_at ?? $now,
                                ]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Опубликовано отзывов: {$count}")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('rejectBulk')
                        ->label('Отклонить выбранные')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                /** @var Review $record */
                                $record->update(['status' => 'rejected']);
                                $count++;
                            }

                            Notification::make()
                                ->title("Отклонено отзывов: {$count}")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

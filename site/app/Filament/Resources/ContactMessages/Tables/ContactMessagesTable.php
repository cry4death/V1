<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Скопировано'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Скопировано'),

                TextColumn::make('message')
                    ->label('Сообщение')
                    ->limit(60)
                    ->wrap()
                    ->tooltip(fn (ContactMessage $record): string => $record->message),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new'      => 'Новое',
                        'read'     => 'Прочитано',
                        'archived' => 'В архиве',
                        default    => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'new'      => 'danger',
                        'read'     => 'success',
                        'archived' => 'gray',
                        default    => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'new'      => 'Новое',
                        'read'     => 'Прочитано',
                        'archived' => 'В архиве',
                    ])
                    ->default('new'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ContactMessage $record): string => ContactMessageResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                Action::make('markRead')
                    ->label('Отметить прочитанным')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (ContactMessage $record): bool => $record->status === 'new')
                    ->action(function (ContactMessage $record): void {
                        $record->update(['status' => 'read']);
                        Notification::make()->title('Отмечено прочитанным')->success()->send();
                    }),

                Action::make('archive')
                    ->label('В архив')
                    ->icon(Heroicon::ArchiveBox)
                    ->color('gray')
                    ->visible(fn (ContactMessage $record): bool => $record->status !== 'archived')
                    ->action(function (ContactMessage $record): void {
                        $record->update(['status' => 'archived']);
                        Notification::make()->title('Перемещено в архив')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('markReadBulk')
                        ->label('Отметить прочитанными')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $records->each->update(['status' => 'read']);
                            Notification::make()->title('Отмечено прочитанными')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Обложка')
                    ->height(40)
                    ->checkFileExistence(false)
                    ->getStateUsing(fn (Article $record): string => $record->coverPublicUrl()),

                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Опубликовано',
                        default => 'Черновик',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Публикация')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('reading_time')
                    ->label('Мин.')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('author')
                    ->label('Автор')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'published' => 'Опубликовано',
                    ]),
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('published_at', 'desc')
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

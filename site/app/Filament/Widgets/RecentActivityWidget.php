<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Последние материалы блога';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Article::query()->latest('id'))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'published' ? 'Опубликовано' : 'Черновик')
                    ->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Дата публикации')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->since()
                    ->sortable(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }
}

<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\Doctor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('doctor_id')
                    ->label('Врач')
                    ->relationship(
                        name: 'doctor',
                        titleAttribute: 'last_name',
                        modifyQueryUsing: fn ($query) => $query->orderBy('last_name')->orderBy('first_name'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Doctor $record): string => $record->full_name)
                    ->searchable(['last_name', 'first_name', 'middle_name'])
                    ->preload()
                    ->required()
                    ->native(false),

                TextInput::make('author_name')
                    ->label('Автор')
                    ->required()
                    ->maxLength(100),

                Select::make('rating')
                    ->label('Оценка')
                    ->options([
                        5 => '5 — отлично',
                        4 => '4 — хорошо',
                        3 => '3 — нормально',
                        2 => '2 — плохо',
                        1 => '1 — очень плохо',
                    ])
                    ->required()
                    ->native(false),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'На модерации',
                        'approved' => 'Одобрен (на сайте)',
                        'rejected' => 'Отклонён',
                    ])
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function (?string $state, callable $set, $get): void {
                        if ($state === 'approved' && blank($get('published_at'))) {
                            $set('published_at', now()->format('Y-m-d H:i:s'));
                        }
                    })
                    ->helperText('При статусе «Одобрен» отзыв виден на странице врача.'),

                DateTimePicker::make('published_at')
                    ->label('Дата публикации')
                    ->seconds(false)
                    ->native(false)
                    ->displayFormat('d.m.Y H:i')
                    ->helperText('Заполняется автоматически при одобрении.'),

                Textarea::make('text')
                    ->label('Текст отзыва')
                    ->required()
                    ->maxLength(2000)
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }
}

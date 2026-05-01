<?php

namespace App\Filament\Resources\Directions\Schemas;

use App\Filament\Support\LocalPublicFileUpload;
use App\Support\DirectionFontAwesomeIcons;
use App\Support\Slug;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DirectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                        if (! filled($state) || filled($get('slug'))) {
                            return;
                        }
                        $set('slug', Slug::make($state));
                    }),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Идентификатор вкладки на странице услуг (латиница, дефисы).'),

                Textarea::make('description')
                    ->label('Краткое описание')
                    ->rows(3)
                    ->columnSpanFull(),

                Section::make('Иконка на сайте')
                    ->description('Выберите иконку из списка (Font Awesome) или загрузите PNG / SVG / WebP. Загруженный файл имеет приоритет над классом FA.')
                    ->schema([
                        Select::make('icon')
                            ->label('Иконка из набора')
                            ->options(DirectionFontAwesomeIcons::selectOptions())
                            ->searchable()
                            ->default('fa-stethoscope')
                            ->native(false)
                            ->live()
                            ->helperText('Используется, если не загружена своя иконка.')
                            ->createOptionForm([
                                TextInput::make('class')
                                    ->label('Класс Font Awesome')
                                    ->placeholder('fa-kit-medical')
                                    ->required()
                                    ->helperText('Только имя класса, например fa-kit-medical (префикс fas подставится на сайте).'),
                            ])
                            ->createOptionModalHeading('Свой класс FA')
                            ->createOptionUsing(function (array $data): string {
                                $c = trim((string) ($data['class'] ?? ''));
                                $c = preg_replace('/^(fa-solid|fas|far|fal|fab)\s+/i', '', $c) ?? $c;
                                if (! str_starts_with($c, 'fa-')) {
                                    $c = 'fa-'.ltrim($c, '-');
                                }

                                return $c;
                            }),

                        SchemaView::make('filament.forms.direction-icon-preview')
                            ->columnSpanFull(),

                        FileUpload::make('icon_image')
                            ->label('Своя иконка (файл)')
                            ->disk(function (FileUpload $component): string {
                                $state = $component->getState();
                                if (is_array($state)) {
                                    $state = reset($state) ?: null;
                                }
                                if (! is_string($state) || $state === '') {
                                    return 'public_assets';
                                }
                                if (str_contains($state, 'livewire-tmp')) {
                                    return 'public';
                                }

                                return 'public_assets';
                            })
                            ->directory('images/directions/icons')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->imagePreviewHeight('64')
                            ->acceptedFileTypes([
                                'image/png',
                                'image/jpeg',
                                'image/gif',
                                'image/webp',
                                'image/svg+xml',
                            ])
                            ->deletable(true)
                            ->downloadable(true)
                            ->openable(true)
                            ->getUploadedFileUsing(LocalPublicFileUpload::uploadedFileUsing())
                            ->helperText('До 2 МБ. Если файл загружен, он показывается вместо иконки FA.'),
                    ])
                    ->columnSpanFull(),

                Section::make('Баннер направления')
                    ->description('Широкое изображение в карточке направления на странице услуг.')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Файл баннера')
                            ->image()
                            ->disk(function (FileUpload $component): string {
                                $state = $component->getState();
                                if (is_array($state)) {
                                    $state = reset($state) ?: null;
                                }
                                if (! is_string($state) || $state === '') {
                                    return 'public_assets';
                                }
                                if (str_contains($state, 'livewire-tmp')) {
                                    return 'public';
                                }

                                return 'public_assets';
                            })
                            ->directory('images/directions')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->panelLayout('integrated')
                            ->panelAspectRatio('2.4:1')
                            ->imagePreviewHeight('320')
                            ->deletable(true)
                            ->downloadable(true)
                            ->openable(true)
                            ->getUploadedFileUsing(LocalPublicFileUpload::uploadedFileUsing())
                            ->helperText('Сохраняется в images/directions. Рекомендуется широкий кадр, как на сайте.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'На сайте',
                        'hidden' => 'Скрыто',
                    ])
                    ->default('active')
                    ->required()
                    ->native(false),

                Section::make('Текст на странице направления')
                    ->description('Блок как у «Гинекологии»: вводите списки построчно — каждая строка станет отдельным пунктом. Баннер в шапке совпадает с полем «Баннер направления» выше.')
                    ->schema([
                        Textarea::make('details.general')
                            ->label('Вводный текст')
                            ->rows(5)
                            ->columnSpanFull(),

                        TextInput::make('details.when_subtitle')
                            ->label('Подзаголовок перед списком «когда обратиться»')
                            ->placeholder('Например: Когда записаться к гинекологу?')
                            ->maxLength(255),

                        Textarea::make('details.when_list')
                            ->label('Список «когда обратиться»')
                            ->rows(8)
                            ->columnSpanFull()
                            ->helperText('Каждая строка — отдельный пункт.')
                            ->afterStateHydrated(function (Textarea $component, $state): void {
                                if (is_array($state)) {
                                    $lines = array_map(
                                        static fn ($v) => is_string($v) ? $v : '',
                                        $state,
                                    );
                                    $component->state(implode("\n", $lines));

                                    return;
                                }
                                if (! is_string($state)) {
                                    $component->state('');
                                }
                            }),

                        Textarea::make('details.conclusion')
                            ->label('Текст после первого списка (заключение, призыв)')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('details.treat_subtitle')
                            ->label('Подзаголовок перед списком «чем помогаем»')
                            ->placeholder('Например: Чем мы можем помочь')
                            ->maxLength(255),

                        Textarea::make('details.treat_list')
                            ->label('Список услуг / направлений помощи')
                            ->rows(8)
                            ->columnSpanFull()
                            ->helperText('Каждая строка — отдельный пункт.')
                            ->afterStateHydrated(function (Textarea $component, $state): void {
                                if (is_array($state)) {
                                    $lines = array_map(
                                        static fn ($v) => is_string($v) ? $v : '',
                                        $state,
                                    );
                                    $component->state(implode("\n", $lines));

                                    return;
                                }
                                if (! is_string($state)) {
                                    $component->state('');
                                }
                            }),

                        Repeater::make('details.faq')
                            ->label('Часто задаваемые вопросы')
                            ->schema([
                                TextInput::make('question')
                                    ->label('Вопрос')
                                    ->required()
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                Textarea::make('answer')
                                    ->label('Ответ')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => Str::limit(trim((string) ($state['question'] ?? '')), 72) ?: null)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }
}

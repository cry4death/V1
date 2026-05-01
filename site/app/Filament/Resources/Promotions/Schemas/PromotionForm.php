<?php

namespace App\Filament\Resources\Promotions\Schemas;

use App\Filament\Forms\Plugins\HighlightRichContentPlugin;
use App\Models\PromotionCategory;
use App\Support\Slug;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\Actions\AttachFilesAction;
use Filament\Forms\Components\RichEditor\Actions\LinkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckFileExistence;
use Throwable;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Категория акции')
                    ->relationship('category', 'name', modifyQueryUsing: fn ($query) => $query->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->placeholder('Без категории')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionModalHeading('Новая категория')
                    ->createOptionUsing(function (array $data): int {
                        $name = trim($data['name']);
                        $existing = PromotionCategory::query()->where('name', $name)->first();
                        if ($existing) {
                            return $existing->getKey();
                        }
                        $baseSlug = Slug::make($name);
                        $slug = $baseSlug;
                        $suffix = 0;
                        while (PromotionCategory::query()->where('slug', $slug)->exists()) {
                            $suffix++;
                            $slug = $baseSlug.'-'.$suffix;
                        }

                        return PromotionCategory::query()->create([
                            'name' => $name,
                            'slug' => $slug,
                        ])->getKey();
                    }),

                TextInput::make('title')
                    ->label('Заголовок')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                        if (! filled($state) || $get('slug_locked')) {
                            return;
                        }
                        $set('slug', Slug::make($state));
                    }),

                Hidden::make('slug_locked')->default(false)->dehydrated(false),

                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set) => $set('slug_locked', true))
                    ->helperText('Адрес страницы: /promotions/{slug}. Автоматически из заголовка, пока не изменить вручную.'),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'active' => 'На сайте',
                        'completed' => 'Завершена',
                    ])
                    ->default('draft')
                    ->required()
                    ->native(false),

                DatePicker::make('start_date')
                    ->label('Дата начала')
                    ->native(false)
                    ->displayFormat('d.m.Y'),

                DatePicker::make('end_date')
                    ->label('Дата окончания')
                    ->native(false)
                    ->displayFormat('d.m.Y')
                    ->afterOrEqual('start_date'),

                FileUpload::make('image')
                    ->label('Изображение (баннер)')
                    ->image()
                    ->disk(function (FileUpload $component): string {
                        $state = $component->getState();
                        if (is_array($state)) {
                            $state = reset($state) ?: null;
                        }
                        if (! is_string($state) || $state === '') {
                            return 'public';
                        }
                        if (str_contains($state, 'livewire-tmp')) {
                            return 'public';
                        }
                        if (str_starts_with($state, 'images/promos/')) {
                            return 'public_assets';
                        }

                        return 'public';
                    })
                    ->directory(function (FileUpload $component): string {
                        $state = $component->getState();
                        if (is_array($state)) {
                            $state = reset($state) ?: null;
                        }
                        if (is_string($state) && str_starts_with($state, 'images/promos/')) {
                            return 'images/promos';
                        }

                        return 'promotions';
                    })
                    ->visibility('public')
                    ->maxSize(5120)
                    ->imagePreviewHeight('320')
                    ->deletable(true)
                    ->downloadable(true)
                    ->openable(true)
                    ->getUploadedFileUsing(function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                        /** @var FilesystemAdapter $storage */
                        $storage = $component->getDisk();
                        $shouldFetchFileInformation = $component->shouldFetchFileInformation();

                        if ($shouldFetchFileInformation) {
                            try {
                                if (! $storage->exists($file)) {
                                    return null;
                                }
                            } catch (UnableToCheckFileExistence) {
                                return null;
                            }
                        }

                        $url = null;

                        if ($component->getVisibility() === 'private') {
                            try {
                                $url = $storage->temporaryUrl(
                                    $file,
                                    now()->addMinutes(30)->endOfHour(),
                                );
                            } catch (Throwable) {
                            }
                        }

                        if (blank($url)) {
                            $url = $storage->url($file);
                        }

                        $diskName = $component->getDiskName();
                        if (
                            is_string($url)
                            && in_array($diskName, ['public', 'public_assets'], true)
                            && preg_match('#^https?://#i', $url)
                        ) {
                            $path = parse_url($url, PHP_URL_PATH);
                            $query = parse_url($url, PHP_URL_QUERY);
                            if (is_string($path) && $path !== '') {
                                $url = $path.($query ? '?'.$query : '');
                            }
                        }

                        return [
                            'name' => ($component->isMultiple() ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
                            'size' => $shouldFetchFileInformation ? $storage->size($file) : 0,
                            'type' => $shouldFetchFileInformation ? $storage->mimeType($file) : null,
                            'url' => $url,
                        ];
                    })
                    ->helperText('Новые файлы — в storage/app/public/promotions (URL /storage/promotions/…). Старые пути images/promos/… в public остаются как есть.'),

                Textarea::make('short_description')
                    ->label('Краткое описание')
                    ->rows(3)
                    ->maxLength(2000)
                    ->live(debounce: 300)
                    ->helperText(function (?string $state): string {
                        $max = 2000;
                        $len = mb_strlen((string) ($state ?? ''));
                        $left = max(0, $max - $len);

                        return "Осталось символов: {$left} из {$max}.";
                    })
                    ->columnSpanFull(),

                TagsInput::make('items')
                    ->label('Что входит в программу')
                    ->placeholder('Введите пункт и нажмите Enter')
                    ->reorderable()
                    ->helperText('Список пунктов на странице акции.')
                    ->columnSpanFull(),

                RichEditor::make('full_description')
                    ->label('Полный текст')
                    ->extraAttributes(['class' => 'fi-rich-editor-sticky'])
                    ->columnSpanFull()
                    ->plugins([
                        HighlightRichContentPlugin::make(),
                    ])
                    ->customTextColors()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'strike', 'link'],
                        ['h1', 'h2', 'h3'],
                        ['textColorPanel', 'highlightPanel', 'lineSpacingMenu'],
                        ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                        ['bulletList', 'orderedList', 'blockquoteWithAuthor'],
                        ['attachFiles', 'table'],
                        ['undo', 'redo'],
                    ])
                    ->fileAttachments(true)
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('promotions/content')
                    ->fileAttachmentsVisibility('public')
                    ->registerActions([
                        LinkAction::make()->modalSubmitActionLabel('Изменить ссылку'),
                        AttachFilesAction::make()->modalSubmitActionLabel('Прикрепить'),
                    ])
                    ->resizableImages(true)
                    ->placeholder('Описание акции. Цитата: выделите текст и нажмите «Цитата», при необходимости укажите автора в окне.')
                    ->helperText('«Цитата» открывает окно с полем автора (необязательно) и вставляет оформление как на сайте.'),
            ]);
    }
}

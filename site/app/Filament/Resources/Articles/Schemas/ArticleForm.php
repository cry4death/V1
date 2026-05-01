<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Filament\Forms\Plugins\HighlightRichContentPlugin;
use App\Models\ArticleCategory;
use App\Models\Doctor;
use App\Support\Slug;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\Actions\AttachFilesAction;
use Filament\Forms\Components\RichEditor\Actions\LinkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckFileExistence;
use Throwable;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Без категории')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Название категории')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionModalHeading('Новая категория')
                    ->createOptionUsing(function (array $data): int {
                        $name = trim($data['name']);
                        $existing = ArticleCategory::query()->where('name', $name)->first();
                        if ($existing) {
                            return $existing->getKey();
                        }
                        $slug = Slug::make($name);
                        $suffix = 0;
                        while (ArticleCategory::query()->where('slug', $slug)->exists()) {
                            $suffix++;
                            $slug = Slug::make($name.'-'.$suffix);
                        }

                        return ArticleCategory::query()->create([
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
                    ->label('Slug (адрес в URL)')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set) => $set('slug_locked', true))
                    ->helperText('Автоматически формируется из заголовка. Если изменить вручную — фиксируется.'),

                Select::make('author_doctor_id')
                    ->label('Автор (врач клиники)')
                    ->relationship(
                        name: 'authorDoctor',
                        titleAttribute: 'last_name',
                        modifyQueryUsing: fn ($query) => $query->where('status', 'active')
                            ->orderBy('last_name')
                            ->orderBy('first_name'),
                    )
                    ->searchable(['last_name', 'first_name', 'middle_name'])
                    ->getOptionLabelFromRecordUsing(fn (Doctor $record): string => $record->full_name)
                    ->preload()
                    ->placeholder('Выберите врача'),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликовано',
                    ])
                    ->default('draft')
                    ->required()
                    ->native(false),

                DateTimePicker::make('published_at')
                    ->label('Дата публикации')
                    ->seconds(false)
                    ->native(false)
                    ->displayFormat('d.m.Y H:i'),

                TextInput::make('reading_time')
                    ->label('Время чтения')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(120)
                    ->suffix('мин')
                    ->placeholder('Например, 7'),

                FileUpload::make('cover_image')
                    ->label('Обложка')
                    ->image()
                    ->disk(function (FileUpload $component): string {
                        $state = $component->getState();
                        if (is_array($state)) {
                            $state = reset($state) ?: null;
                        }
                        if (! is_string($state) || $state === '') {
                            return 'public_assets';
                        }
                        // Временные файлы Livewire лежат на диске public (см. config/livewire.php).
                        if (str_contains($state, 'livewire-tmp')) {
                            return 'public';
                        }
                        if (str_starts_with($state, 'blog/covers/')) {
                            return 'public';
                        }

                        return 'public_assets';
                    })
                    ->directory(function (FileUpload $component): string {
                        $state = $component->getState();
                        if (is_array($state)) {
                            $state = reset($state) ?: null;
                        }
                        if (is_string($state) && str_starts_with($state, 'blog/covers/')) {
                            return 'blog/covers';
                        }

                        return 'images/blog';
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

                        // FilePond грузит превью через fetch(). Абсолютный URL из APP_URL при доступе к админке
                        // с другим host (127.0.0.1 vs localhost) даёт чужой origin — бесконечное «Ожидание».
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
                    ->automaticallyResizeImagesMode('contain')
                    ->automaticallyResizeImagesToWidth('1920')
                    ->automaticallyResizeImagesToHeight('1920')
                    ->automaticallyUpscaleImagesWhenResizing(false)
                    ->helperText('Старые обложки из images/blog видны здесь; можно удалить или заменить. Новые файлы ужимаются до 1920×1920 (contain). Путь в storage: images/blog или blog/covers.'),

                RichEditor::make('content')
                    ->label('Текст статьи')
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
                    ->fileAttachmentsDirectory('blog/article-content')
                    ->fileAttachmentsVisibility('public')
                    ->registerActions([
                        LinkAction::make()->modalSubmitActionLabel('Изменить ссылку'),
                        AttachFilesAction::make()->modalSubmitActionLabel('Прикрепить'),
                    ])
                    ->resizableImages(true)
                    ->placeholder('Текст статьи. Цитата: выделите фрагмент и нажмите «Цитата» — откроется окно, при желании укажите автора.')
                    ->helperText('Кнопка «Цитата» вставляет блок цитаты с «ёлочками» и по желанию подписью автора внизу.'),

                Textarea::make('meta_description')
                    ->label('Meta description (SEO)')
                    ->rows(3)
                    ->maxLength(200)
                    ->live(debounce: 400)
                    ->columnSpanFull()
                    ->helperText(fn (?string $state): string => 'Краткое описание для поиска и превью, до 200 символов. '.mb_strlen((string) $state).'/200'),
            ]);
    }
}

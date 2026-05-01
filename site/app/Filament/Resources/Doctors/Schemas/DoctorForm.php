<?php

namespace App\Filament\Resources\Doctors\Schemas;

use App\Filament\Support\LocalPublicFileUpload;
use App\Models\Service;
use App\Models\Specialization;
use App\Support\Slug;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DoctorForm
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mergeEducationRepeatersIntoStoredFormat(array $data): array
    {
        $work = $data['work_experience'] ?? [];
        $edu = $data['education_qualifications'] ?? [];
        unset($data['work_experience'], $data['education_qualifications']);

        $merged = [];

        foreach (array_values(Arr::wrap($work)) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $period = trim((string) ($row['period'] ?? ''));
            $title = trim((string) ($row['title'] ?? ''));
            $institution = trim((string) ($row['institution'] ?? ''));
            if ($period === '' && $title === '' && $institution === '') {
                continue;
            }
            $merged[] = [
                'type' => 'experience',
                'period' => $period,
                'title' => $title,
                'institution' => $institution,
            ];
        }

        foreach (array_values(Arr::wrap($edu)) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $period = trim((string) ($row['period'] ?? ''));
            $title = trim((string) ($row['title'] ?? ''));
            $institution = trim((string) ($row['institution'] ?? ''));
            if ($period === '' && $title === '' && $institution === '') {
                continue;
            }
            $merged[] = [
                'type' => 'education',
                'period' => $period,
                'title' => $title,
                'institution' => $institution,
            ];
        }

        $data['education'] = $merged;

        return $data;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('specialization_id')
                    ->label('Специализация')
                    ->relationship('specialization', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Название специализации')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->createOptionModalHeading('Новая специализация')
                    ->createOptionUsing(function (array $data): int {
                        $name = trim($data['name']);
                        $existing = Specialization::query()->where('name', $name)->first();
                        if ($existing) {
                            return $existing->getKey();
                        }
                        $slug = Slug::make($name);
                        $suffix = 0;
                        while (Specialization::query()->where('slug', $slug)->exists()) {
                            $suffix++;
                            $slug = Slug::make($name.'-'.$suffix);
                        }

                        return Specialization::query()->create([
                            'name' => $name,
                            'slug' => $slug,
                        ])->getKey();
                    }),

                TextInput::make('last_name')
                    ->label('Фамилия')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get): void {
                        if ($get('slug_locked')) {
                            return;
                        }
                        $set('slug', Slug::make(static::slugSourceFromName($get)));
                    }),

                TextInput::make('first_name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get): void {
                        if ($get('slug_locked')) {
                            return;
                        }
                        $set('slug', Slug::make(static::slugSourceFromName($get)));
                    }),

                TextInput::make('middle_name')
                    ->label('Отчество')
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, Get $get): void {
                        if ($get('slug_locked')) {
                            return;
                        }
                        $set('slug', Slug::make(static::slugSourceFromName($get)));
                    }),

                Hidden::make('slug_locked')->default(false)->dehydrated(false),

                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set) => $set('slug_locked', true))
                    ->helperText('Автоматически формируется из ФИО. Если изменить вручную — фиксируется.'),

                Select::make('category')
                    ->label('Категория')
                    ->options([
                        'highest' => 'Высшая',
                        'first' => 'Первая',
                        'second' => 'Вторая',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('academic_degree')
                    ->label('Учёная степень')
                    ->maxLength(255)
                    ->helperText('Необязательно, например: кандидат медицинских наук'),

                TextInput::make('experience_years')
                    ->label('Стаж (лет)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(80)
                    ->default(0)
                    ->required(),

                TextInput::make('experience_since')
                    ->label('Год начала практики')
                    ->numeric()
                    ->minValue(1950)
                    ->maxValue((int) date('Y') + 1)
                    ->placeholder('Например, 2004'),

                Select::make('patient_age')
                    ->label('Пациенты')
                    ->options([
                        'adults' => 'Взрослые',
                        'children' => 'Дети',
                        'both' => 'Взрослые и дети',
                    ])
                    ->default('both')
                    ->required()
                    ->native(false),

                Select::make('services')
                    ->label('Услуги')
                    ->relationship(
                        name: 'services',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->with('direction')->orderBy('name'),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(
                        fn (Service $record): string => ($record->direction?->name ? $record->direction->name.' — ' : '').$record->name,
                    )
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Порядок в списке')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активен',
                        'inactive' => 'Скрыт',
                    ])
                    ->default('active')
                    ->required()
                    ->native(false),

                FileUpload::make('photo')
                    ->label('Фото')
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
                    ->directory('images/doctors')
                    ->visibility('public')
                    ->maxSize(5120)
                    ->imagePreviewHeight('320')
                    ->deletable(true)
                    ->downloadable(true)
                    ->openable(true)
                    ->getUploadedFileUsing(LocalPublicFileUpload::uploadedFileUsing())
                    ->helperText('Старые пути вроде images/doctor-profile.png поддерживаются; новые файлы — в images/doctors.'),

                Textarea::make('description')
                    ->label('Описание')
                    ->rows(6)
                    ->columnSpanFull(),

                Repeater::make('work_experience')
                    ->label('Опыт работы')
                    ->schema([
                        TextInput::make('period')
                            ->label('Период')
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label('Должность / должность по месту работы')
                            ->maxLength(500),
                        TextInput::make('institution')
                            ->label('Учреждение')
                            ->maxLength(500),
                    ])
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => Str::limit(trim(($state['period'] ?? '').' '.($state['title'] ?? '')), 60) ?: null)
                    ->columnSpanFull()
                    ->helperText('Стаж по местам работы — как блок «Стаж работы» на сайте.'),

                Repeater::make('education_qualifications')
                    ->label('Образование и повышение квалификации')
                    ->schema([
                        TextInput::make('period')
                            ->label('Период / год')
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label('Название (специальность, курс, ПК)')
                            ->maxLength(500),
                        TextInput::make('institution')
                            ->label('Учреждение')
                            ->maxLength(500),
                    ])
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => Str::limit(trim(($state['period'] ?? '').' '.($state['title'] ?? '')), 60) ?: null)
                    ->columnSpanFull()
                    ->helperText('Вуз, интернатура, курсы — как блок «Образование и повышения квалификации» на сайте.'),
            ]);
    }

    /**
     * @param  array<string, mixed>  $education
     * @return array{work_experience: list<array{period: string, title: string, institution: string}>, education_qualifications: list<array{period: string, title: string, institution: string}>}
     */
    public static function splitEducationForForm(array $education): array
    {
        $work = [];
        $edu = [];

        foreach ($education as $item) {
            if (! is_array($item)) {
                continue;
            }
            $row = [
                'period' => (string) ($item['period'] ?? ''),
                'title' => (string) ($item['title'] ?? ''),
                'institution' => (string) ($item['institution'] ?? ''),
            ];
            if (($item['type'] ?? 'experience') === 'education') {
                $edu[] = $row;
            } else {
                $work[] = $row;
            }
        }

        return [
            'work_experience' => array_values($work),
            'education_qualifications' => array_values($edu),
        ];
    }

    private static function slugSourceFromName(Get $get): string
    {
        return implode(' ', array_filter([
            $get('last_name'),
            $get('first_name'),
            $get('middle_name'),
        ]));
    }
}

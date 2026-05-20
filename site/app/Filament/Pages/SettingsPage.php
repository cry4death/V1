<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.settings-page';

    protected static ?string $navigationLabel = 'Настройки сайта';

    protected static ?string $title = 'Настройки сайта';

    protected static string|\UnitEnum|null $navigationGroup = 'Клиника';

    protected static ?int $navigationSort = 90;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    /** @var array<string,mixed> */
    public array $data = [];

    /**
     * Map of form-field-name => [group, key]
     *
     * @var array<string, array{0:string,1:string}>
     */
    protected array $fieldMap = [
        'contacts_address' => ['contacts', 'address'],
        'contacts_postal_address' => ['contacts', 'postal_address'],
        'contacts_phone_main' => ['contacts', 'phone_main'],
        'contacts_phone_mobile' => ['contacts', 'phone_mobile'],
        'contacts_phone_extra_1' => ['contacts', 'phone_extra_1'],
        'contacts_phone_short' => ['contacts', 'phone_short'],
        'contacts_phone_short_note' => ['contacts', 'phone_short_note'],
        'contacts_email' => ['contacts', 'email'],
        'contacts_map_url' => ['contacts', 'map_url'],

        'schedule_weekdays' => ['schedule', 'weekdays'],
        'schedule_saturday' => ['schedule', 'saturday'],
        'schedule_sunday' => ['schedule', 'sunday'],

        'social_instagram' => ['social', 'instagram'],
        'social_telegram' => ['social', 'telegram'],
        'social_facebook' => ['social', 'facebook'],
        'social_vk' => ['social', 'vk'],
        'social_youtube' => ['social', 'youtube'],

        'legal_company_name' => ['legal', 'company_name'],
        'legal_unp' => ['legal', 'unp'],
        'legal_license_number' => ['legal', 'license_number'],

        'booking_slot_step_minutes' => ['booking', 'slot_step_minutes'],
    ];

    public function mount(): void
    {
        $values = [];
        foreach ($this->fieldMap as $field => [$group, $key]) {
            $values[$field] = Setting::getValue($group, $key, '');
        }
        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('Настройки')
                    ->tabs([
                        Tab::make('Контакты')->schema([
                            TextInput::make('contacts_address')->label('Адрес')->maxLength(255),
                            TextInput::make('contacts_postal_address')->label('Почтовый адрес')->maxLength(255),
                            TextInput::make('contacts_phone_main')->label('Телефон (основной)')->maxLength(50),
                            TextInput::make('contacts_phone_mobile')->label('Мобильный телефон')->maxLength(50),
                            TextInput::make('contacts_phone_extra_1')->label('Дополнительный телефон')->maxLength(50),
                            TextInput::make('contacts_phone_short')->label('Короткий номер')->maxLength(50),
                            TextInput::make('contacts_phone_short_note')->label('Подпись короткого номера')->maxLength(120),
                            TextInput::make('contacts_email')->label('E-mail')->email()->maxLength(120),
                            Textarea::make('contacts_map_url')->label('Ссылка на карту (iframe src)')->rows(2),
                        ])->columns(2),

                        Tab::make('Расписание')->schema([
                            TextInput::make('schedule_weekdays')->label('Будни')->maxLength(120),
                            TextInput::make('schedule_saturday')->label('Суббота')->maxLength(120),
                            TextInput::make('schedule_sunday')->label('Воскресенье')->maxLength(120),
                        ])->columns(3),

                        Tab::make('Онлайн-запись')->schema([
                            TextInput::make('booking_slot_step_minutes')
                                ->label('Шаг сетки слотов (мин.)')
                                ->numeric()
                                ->minValue(5)
                                ->maxValue(240)
                                ->step(5)
                                ->nullable()
                                ->helperText('Интервал между допустимыми началами слотов. Оставьте пустым — использовать длительность приёма из карточки каждой услуги.'),
                        ])->columns(1),

                        Tab::make('Социальные сети')->schema([
                            TextInput::make('social_instagram')->label('Instagram URL')->url()->maxLength(255),
                            TextInput::make('social_telegram')->label('Telegram URL')->url()->maxLength(255),
                            TextInput::make('social_facebook')->label('Facebook URL')->url()->maxLength(255),
                            TextInput::make('social_vk')->label('VK URL')->url()->maxLength(255),
                            TextInput::make('social_youtube')->label('YouTube URL')->url()->maxLength(255),
                        ])->columns(2),

                        Tab::make('Юридические')->schema([
                            TextInput::make('legal_company_name')->label('Название организации')->maxLength(255),
                            TextInput::make('legal_unp')->label('УНП')->maxLength(50),
                            TextInput::make('legal_license_number')->label('Номер лицензии')->maxLength(120),
                        ])->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($this->fieldMap as $field => [$group, $key]) {
            Setting::setValue($group, $key, $state[$field] ?? null);
        }

        Notification::make()
            ->title('Настройки сохранены')
            ->success()
            ->send();
    }
}

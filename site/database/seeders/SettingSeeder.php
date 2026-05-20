<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'contacts' => [
                'address' => 'г. Минск, ул. Притыцкого, 10',
                'postal_address' => '220140, г. Минск, а/я 100',
                'phone_main' => '+375 (17) 123-45-67',
                'phone_mobile' => '+375 (29) 123-45-67',
                'email' => 'info@mayak-zdorovya.by',
                'map_url' => 'https://yandex.ru/maps/157/minsk/',
            ],
            'schedule' => [
                'weekdays' => '8:00 – 20:00',
                'saturday' => '9:00 – 15:00',
                'sunday' => 'Выходной',
            ],
            'social' => [
                'instagram' => 'https://www.instagram.com/lighthouse_med/',
                'facebook' => 'https://www.facebook.com/lighthouse.med.by',
                'vk' => 'https://vk.com/lighthouse_med',
                'youtube' => 'https://www.youtube.com/channel/UC_82Q11hDuQrKX7pzhQLPIg',
                'telegram' => 'https://t.me/mayak_zdorovya',
            ],
            'legal' => [
                'company_name' => 'ООО «Маяк Здоровья»',
                'unp' => '123456789',
                'license_number' => 'Лицензия №М-12345 от 01.01.2020',
            ],
            'booking' => [
                'slot_step_minutes' => '20',
                'min_lead_minutes' => '60',
                'cancel_window_hours' => '3',
            ],
        ];

        foreach ($settings as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::updateOrCreate(
                    ['group_name' => $group, 'key' => $key],
                    ['value' => $value]
                );
            }
        }
    }
}

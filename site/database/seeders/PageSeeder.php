<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Главная',
                'content' => [
                    'hero' => [
                        'title' => 'Маяк Здоровья',
                        'subtitle' => 'Многопрофильная клиника в Минске',
                        'badge' => 'Более 20 лет опыта',
                        'image' => 'images/hero.jpg',
                    ],
                    'features' => [
                        ['icon' => 'fa-user-doctor', 'title' => 'Опытные специалисты', 'text' => 'Врачи высшей и первой категории'],
                        ['icon' => 'fa-microscope', 'title' => 'Современное оборудование', 'text' => 'Оборудование экспертного класса'],
                        ['icon' => 'fa-clock', 'title' => 'Удобная запись', 'text' => 'Приём 7 дней в неделю, без очередей'],
                        ['icon' => 'fa-shield-heart', 'title' => 'Гарантия качества', 'text' => 'Работаем по международным стандартам'],
                    ],
                ],
            ]
        );

        Page::updateOrCreate(
            ['slug' => 'about'],
            [
                'title' => 'О клинике',
                'content' => [
                    'intro' => 'Клиника «Маяк Здоровья» — многопрофильный медицинский центр в Минске с более чем 20-летним опытом.',
                    'stats' => [
                        ['value' => '20+', 'label' => 'лет опыта'],
                        ['value' => '50+', 'label' => 'врачей'],
                        ['value' => '100 000+', 'label' => 'пациентов'],
                        ['value' => '15', 'label' => 'направлений'],
                    ],
                ],
            ]
        );

        Page::updateOrCreate(
            ['slug' => 'search'],
            [
                'title' => 'Поиск по сайту',
                'content' => [],
            ]
        );
    }
}

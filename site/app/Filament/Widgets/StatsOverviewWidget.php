<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Direction;
use App\Models\Doctor;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseStatsWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Обзор клиники';

    protected ?string $description = 'Ключевые показатели сайта в режиме реального времени';

    protected function getStats(): array
    {
        return [
            Stat::make('Врачи', Doctor::where('status', 'active')->count())
                ->description('Активные специалисты')
                ->color('primary'),

            Stat::make('Услуги', Service::where('status', 'active')->count())
                ->description('Доступные на сайте')
                ->color('primary'),

            Stat::make('Категории услуг', Direction::where('status', 'active')->count())
                ->description('Активные на сайте')
                ->color('primary'),

            Stat::make('Статьи блога', Article::where('status', 'published')->count())
                ->description('Опубликованные материалы')
                ->color('primary'),

            Stat::make('Активные акции', Promotion::where('status', 'active')->count())
                ->description('Видны на сайте')
                ->color('primary'),

            Stat::make('Отзывы', Review::where('status', 'approved')->count())
                ->description('Одобренные отзывы')
                ->color('primary'),
        ];
    }
}

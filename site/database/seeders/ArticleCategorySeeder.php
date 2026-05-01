<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use App\Support\Slug;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = ['Здоровье', 'Диагностика', 'Профилактика', 'Советы врачей', 'Новости клиники'];

        foreach ($items as $name) {
            ArticleCategory::updateOrCreate(
                ['name' => $name],
                ['slug' => Slug::make($name)]
            );
        }
    }
}

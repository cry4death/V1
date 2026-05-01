<?php

namespace Database\Seeders;

use App\Models\PromotionCategory;
use App\Support\Slug;
use Illuminate\Database\Seeder;

class PromotionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = ['Чек-апы', 'Сезонные', 'Семейные', 'Скидки'];

        foreach ($items as $name) {
            PromotionCategory::updateOrCreate(
                ['name' => $name],
                ['slug' => Slug::make($name)]
            );
        }
    }
}

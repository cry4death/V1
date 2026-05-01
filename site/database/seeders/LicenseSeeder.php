<?php

namespace Database\Seeders;

use App\Models\License;
use Illuminate\Database\Seeder;

class LicenseSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['images/license-1.png', 'Лицензия на медицинскую деятельность'],
            ['images/license-2.png', 'Дополнение к лицензии'],
            ['images/license-3.png', 'Перечень работ и услуг'],
        ];

        foreach ($items as $i => [$image, $caption]) {
            License::updateOrCreate(
                ['caption' => $caption],
                [
                    'image' => $image,
                    'sort_order' => $i,
                ]
            );
        }
    }
}

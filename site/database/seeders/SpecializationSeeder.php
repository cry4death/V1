<?php

namespace Database\Seeders;

use App\Models\Specialization;
use App\Support\Slug;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Терапевт', 'Кардиолог', 'Невролог',
            'Акушер-гинеколог', 'Гинеколог',
            'Проктолог', 'Хирург', 'Эндокринолог', 'Уролог',
            'Нефролог', 'Онколог', 'Маммолог',
            'Гастроэнтеролог', 'Диетолог', 'Флеболог',
            'Психолог', 'Психотерапевт',
            'Врач УЗИ', 'Врач функциональной диагностики',
        ];

        foreach ($items as $name) {
            Specialization::updateOrCreate(
                ['name' => $name],
                ['slug' => Slug::make($name)]
            );
        }
    }
}

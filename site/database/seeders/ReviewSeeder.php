<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $texts = [
            'Очень внимательный врач, всё объяснил подробно, рекомендую.',
            'Приятная атмосфера в клинике, приём прошёл быстро и профессионально.',
            'Врач поставил точный диагноз и подобрал эффективное лечение.',
            'Спасибо за чуткое отношение и высокий профессионализм!',
            'Хорошая диагностика, всё современно, буду обращаться ещё.',
        ];

        $authors = [
            'Алина', 'Сергей', 'Мария', 'Олег', 'Наталья',
            'Андрей', 'Екатерина', 'Дмитрий', 'Анна К.', 'Виктор',
        ];

        foreach (Doctor::all() as $doctor) {
            // Макееву не трогаем: её отзывы уже заполнены точными данными в DoctorSeeder
            if ($doctor->slug === \App\Support\Slug::make('Макеева-Алеся-Викторовна')) {
                continue;
            }

            // Если у доктора уже есть отзывы (повторный запуск) — пропускаем
            if ($doctor->reviews()->exists()) {
                continue;
            }

            $count = random_int(2, 4);
            for ($i = 0; $i < $count; $i++) {
                Review::create([
                    'doctor_id'    => $doctor->id,
                    'author_name'  => $authors[array_rand($authors)],
                    'rating'       => random_int(4, 5),
                    'text'         => $texts[array_rand($texts)],
                    'status'       => 'approved',
                    'published_at' => now()->subDays(random_int(1, 180)),
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SpecializationSeeder::class,
            DirectionSeeder::class,
            ServiceSeeder::class,
            DoctorSeeder::class,
            DoctorScheduleSeeder::class,
            ReviewSeeder::class,
            ArticleCategorySeeder::class,
            ArticleSeeder::class,
            PromotionCategorySeeder::class,
            PromotionSeeder::class,
            LicenseSeeder::class,
            SettingSeeder::class,
            PageSeeder::class,
            InitialContentSeeder::class,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $last = fake()->lastName();

        return [
            'specialization_id' => Specialization::factory(),
            'last_name' => $last,
            'first_name' => fake()->firstName(),
            'middle_name' => null,
            'slug' => Str::slug($last.'-'.fake()->unique()->numerify('####')),
            'category' => 'first',
            'experience_years' => fake()->numberBetween(1, 25),
            'patient_age' => 'both',
            'photo' => null,
            'description' => fake()->sentence(),
            'education' => [],
            'status' => 'active',
            'sort_order' => fake()->numberBetween(0, 1000),
        ];
    }
}

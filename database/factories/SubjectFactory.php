<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grade_level_id' => GradeLevel::factory(),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->unique()->words(3, true),
            'included_in_total_score' => fake()->boolean(95),
            'needs_lab' => fake()->boolean(80),
            'description' => fake()->paragraph,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::currentId(),
            'school_id' => School::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'name' => fake()->unique()->randomLetter(),
            'capacity' => fake()->numberBetween(20, 40),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolStudentsGender;
use App\Models\School;
use App\Models\SchoolPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolPeriod>
 */
class SchoolPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'academic_period' => fake()->randomElement(SchoolAcademicPeriod::getPrimaryValues()),
            'students_gender' => fake()->randomElement(SchoolStudentsGender::cases()),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\SchoolEducationalStageEnum;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolEducationalStage>
 */
class SchoolEducationalStageFactory extends Factory
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
            'stage' => fake()->randomElement(SchoolEducationalStageEnum::cases()),
        ];
    }
}

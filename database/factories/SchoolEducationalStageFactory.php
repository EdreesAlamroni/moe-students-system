<?php

namespace Database\Factories;

use App\Enums\SchoolEducationalStageEnum;
use App\Models\AcademicYear;
use App\Models\SchoolEducationalStage;
use App\Models\SchoolPeriod;
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
            'academic_year_id' => AcademicYear::currentId() ?? AcademicYear::factory(),
            'school_period_id' => SchoolPeriod::factory(),
            'stage' => fake()->randomElement(SchoolEducationalStageEnum::cases()),
        ];
    }
}

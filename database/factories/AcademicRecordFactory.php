<?php

namespace Database\Factories;

use App\Enums\AcademicRecordRating;
use App\Enums\AcademicRecordStatus;
use App\Models\AcademicRecord;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicRecord>
 */
class AcademicRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'student_id' => Student::factory(),
            'status' => $this->faker->randomElement(AcademicRecordStatus::cases()),
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (): array => [
            'status' => AcademicRecordStatus::PASSED,
            'rating' => $this->faker->randomElement(AcademicRecordRating::cases()),
        ]);
    }

    public function promoted(): static
    {
        return $this->state(fn (): array => [
            'status' => AcademicRecordStatus::PROMOTED,
            'rating' => $this->faker->randomElement(AcademicRecordRating::cases()),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => AcademicRecordStatus::FAILED,
            'rating' => null,
        ]);
    }
}

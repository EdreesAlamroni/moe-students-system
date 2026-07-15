<?php

namespace Database\Factories;

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassSchedule>
 */
class ClassScheduleFactory extends Factory
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
            'academic_year_id' => AcademicYear::currentId(),

            'classroom_id' => fn (array $attributes) => Classroom::factory()->state([
                'school_id' => $attributes['school_id'],
                'academic_year_id' => $attributes['academic_year_id'],
            ]),
            'class_period_id' => fn (array $attributes) => ClassPeriod::factory()->state([
                'academic_year_id' => $attributes['academic_year_id'],
            ]),
            'subject_id' => Subject::factory(),

            'day_of_week' => DayOfWeek::schoolDays()->random(),
            'notes' => null,
        ];
    }

    public function forDay(DayOfWeek $day): static
    {
        return $this->state([
            'day_of_week' => $day,
        ]);
    }
}

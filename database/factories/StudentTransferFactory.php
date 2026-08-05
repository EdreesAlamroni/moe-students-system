<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentTransfer>
 */
class StudentTransferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'left_academic_year_id' => AcademicYear::currentId() ?? AcademicYear::factory(),
            'joined_academic_year_id' => fn (array $attributes) => $attributes['left_academic_year_id'],

            'from_school_period_id' => SchoolPeriod::factory(),
            'to_school_period_id' => function (array $attributes) {
                $fromSchoolPeriod = SchoolPeriod::query()
                    ->findOrFail($attributes['from_school_period_id']);

                return SchoolPeriod::factory()->for(
                    School::factory()->state([
                        'education_monitor_id' => $fromSchoolPeriod->education_monitor_id,
                        'education_services_office_id' => $fromSchoolPeriod->education_services_office_id,
                    ]),
                    'school',
                );
            },

            'student_id' => fn (array $attributes) => Student::factory()->state([
                'school_period_id' => $attributes['to_school_period_id'],
            ]),

            'left_school_period_at' => function (array $attributes) {
                $academicYear = AcademicYear::query()->find($attributes['left_academic_year_id']);

                return fake()->dateTimeBetween(
                    $academicYear->start_date,
                    $academicYear->end_date,
                );
            },
            'joined_school_period_at' => function (array $attributes) {
                $academicYear = AcademicYear::query()->find($attributes['joined_academic_year_id']);

                return fake()->dateTimeBetween(
                    $attributes['left_school_period_at'],
                    $academicYear->end_date,
                );
            },
        ];
    }

    /**
     * Transfer where the student has left a school but not yet joined another.
     */
    public function awaitingJoin(): static
    {
        return $this->state(fn (): array => [
            'joined_academic_year_id' => null,
            'to_school_period_id' => null,
            'joined_school_period_at' => null,
            'student_id' => fn (array $attributes) => Student::factory()->state([
                'school_period_id' => $attributes['from_school_period_id'],
            ]),
        ]);
    }
}

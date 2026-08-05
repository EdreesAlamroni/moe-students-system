<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentEnrollment>
 */
class StudentEnrollmentFactory extends Factory
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
            'grade_level_id' => GradeLevel::factory(),
            'classroom_id' => fn (array $attributes) => Classroom::factory()->state([
                'academic_year_id' => $attributes['academic_year_id'],
                'school_period_id' => $attributes['school_period_id'],
                'grade_level_id' => $attributes['grade_level_id'],
            ]),
            'student_id' => fn (array $attributes) => Student::factory()->state([
                'school_period_id' => $attributes['school_period_id'],
            ]),
        ];
    }
}

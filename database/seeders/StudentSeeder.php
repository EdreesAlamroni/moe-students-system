<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();

        $school->gradeLevels()->each(function (GradeLevel $gradeLevel) use ($school) {
            Student::factory(50)
                ->recycle($school->monitor)
                ->recycle($school)
                ->create()
                ->each(function (Student $student) use ($gradeLevel) {
                    $student->enrollments()->create([
                        'academic_year_id' => AcademicYear::currentId(),
                        'school_id' => $student->school_id,
                        'grade_level_id' => $gradeLevel->id,
                    ]);
                });
        });
    }
}

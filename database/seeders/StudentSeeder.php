<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $schoolPeriod = SchoolPeriod::first();

        if ($schoolPeriod === null) {
            return;
        }

        $schoolPeriod->gradeLevels()->each(function (GradeLevel $gradeLevel) use ($schoolPeriod) {
            Student::factory(50)
                ->recycle($schoolPeriod->monitor)
                ->recycle($schoolPeriod)
                ->create()
                ->each(function (Student $student) use ($gradeLevel) {
                    $student->enrollments()->create([
                        'academic_year_id' => AcademicYear::currentId(),
                        'school_period_id' => $student->school_period_id,
                        'grade_level_id' => $gradeLevel->id,
                    ]);
                });
        });
    }
}

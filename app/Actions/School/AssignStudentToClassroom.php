<?php

namespace App\Actions\School;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;

class AssignStudentToClassroom
{
    public function execute(Student $student, Classroom $classroom): void
    {
        $student->enrollment()->updateOrCreate([
            'academic_year_id' => AcademicYear::currentId(),
            'school_id' => auth('school')->user()->organization_id,
            'grade_level_id' => $classroom->grade_level_id,
        ], [
            'classroom_id' => $classroom->id,
        ]);
    }
}

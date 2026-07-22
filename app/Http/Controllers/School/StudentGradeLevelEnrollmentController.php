<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\Student\EnrollGradeLevelRequest;
use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

class StudentGradeLevelEnrollmentController extends Controller
{
    public function store(EnrollGradeLevelRequest $request, Student $student): RedirectResponse
    {
        Gate::authorize('enrollInGradeLevel', $student);

        $student->enrollment()->create([
            'academic_year_id' => AcademicYear::currentId(),
            'school_id' => auth('school')->user()->organization_id,
            'grade_level_id' => $request->validated('grade_level_id'),
        ]);

        flash_success('student-grade-level-enrolled');

        return Redirect::route('school.students.show', ['student' => $student]);
    }
}

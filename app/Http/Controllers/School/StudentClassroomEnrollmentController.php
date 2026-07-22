<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\Student\EnrollClassroomRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\RedirectResponse;

class StudentClassroomEnrollmentController extends Controller
{
    public function store(EnrollClassroomRequest $request, Student $student): RedirectResponse
    {
        Gate::authorize('enrollInClassroom', $student);

        /** @var Classroom $classroom */
        $classroom = Classroom::where('id', '=', $request->validated('classroom_id'))->first();

        $student->enrollment()->updateOrCreate([
            'academic_year_id' => AcademicYear::currentId(),
            'school_id' => auth('school')->user()->organization_id,
            'grade_level_id' => $classroom->grade_level_id,
        ], [
            'classroom_id' => $classroom->id,
        ]);

        flash_success('student-classroom-enrolled');

        return Redirect::route('school.students.show', ['student' => $student]);
    }
}

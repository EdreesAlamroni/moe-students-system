<?php

namespace App\Http\Controllers\School;

use App\Actions\School\AssignStudentToClassroom;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\Student\EnrollClassroomRequest;
use App\Http\Requests\School\Student\TransferClassroomRequest;
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

        $classroom = Classroom::query()
            ->where('id', '=', $request->validated('classroom_id'))
            ->first();

        app(AssignStudentToClassroom::class)->execute($student, $classroom);

        flash_success('student-classroom-enrolled');

        return Redirect::route('school.students.show', ['student' => $student]);
    }

    public function update(TransferClassroomRequest $request, Student $student): RedirectResponse
    {
        Gate::authorize('transferClassroom', $student);

        $classroom = Classroom::query()
            ->where('id', '=', $request->validated('classroom_id'))
            ->first();

        app(AssignStudentToClassroom::class)->execute($student, $classroom);

        flash_success('student-classroom-transferred');

        return Redirect::route('school.students.show', ['student' => $student]);
    }
}

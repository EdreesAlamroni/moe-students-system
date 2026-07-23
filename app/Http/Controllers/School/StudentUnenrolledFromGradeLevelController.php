<?php

namespace App\Http\Controllers\School;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\School\StudentCollection;
use App\Models\Nationality;
use App\Models\Student;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentUnenrolledFromGradeLevelController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Student::class);

        $students = QueryBuilder::for(Student::class)
            ->select([
                'students.id',
                'students.uuid',
                'students.education_monitor_id',
                'students.school_id',
                'students.nationality_id',
                'students.number',
                'students.registration_status',
                'students.first_name',
                'students.father_name',
                'students.grandfather_name',
                'students.surname',
                'students.gender',
                'students.national_id',
                'students.family_registration_number',
                'students.passport_number',
                'students.created_at',
                'students.deleted_at',
            ])
            ->forCurrentSchool()
            ->unenrolledFromGradeLevel()
            ->with(['nationality'])
            ->allowedFilters(
                AllowedFilter::scope('name', 'byFullName'),
                AllowedFilter::exact('registration_status'),
                AllowedFilter::exact('nationality_id'),
                'national_id',
                'family_registration_number',
                'passport_number',
            )
            ->orderByFullName()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('school/students/unenrolled-from-grade-level/index', [
            'students' => ResourcePayloadBuilder::paginateWithAbilities(
                $students,
                StudentCollection::make($students),
                ['view'],
                $request,
            ),
            'registrationStatuses' => StudentRegistrationStatus::optionsArray(),
            'nationalities' => Nationality::list(),
            'filter' => $request->input('filter', []),
        ]);
    }
}

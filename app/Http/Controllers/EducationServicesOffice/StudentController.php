<?php

namespace App\Http\Controllers\EducationServicesOffice;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\EducationServicesOffice\StudentCollection;
use App\Http\Resources\EducationServicesOffice\StudentResource;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Student::class);

        $schools = School::list(function ($query) {
            return $query->forCurrentEducationServicesOffice();
        });

        $schoolId = $request->filled('school_id')
            ? $request->integer('school_id')
            : null;

        if ($schoolId !== null && ! $schools->contains('id', '=', $schoolId)) {
            $schoolId = null;
        }

        $students = $this->getPaginatedStudents($request, $schoolId);

        return Inertia::render('education-services-office/students/index', [
            'schools' => $schools,
            'school_id' => $schoolId,
            'filter' => $request->input('filter', []),
            ...($students !== null ? [
                'nationalities' => Nationality::list(),
                'registrationStatuses' => StudentRegistrationStatus::optionsArray(),
                'students' => ResourcePayloadBuilder::paginateWithAbilities(
                    $students,
                    StudentCollection::make($students),
                    ['view'],
                    $request,
                ),
            ] : []),
        ]);
    }

    public function show(Student $student): Response
    {
        Gate::authorize('view', $student);

        $student->load([
            'monitor:id,uuid,name',
            'school:id,uuid,name,education_services_office_id',
            'nationality:id,uuid,name,code',
        ]);

        return Inertia::render('education-services-office/students/show', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
        ]);
    }

    private function getPaginatedStudents(Request $request, ?int $schoolId)
    {
        if ($schoolId === null) {
            return null;
        }

        return QueryBuilder::for(Student::class)
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
            ->forCurrentEducationServicesOffice()
            ->where('students.school_id', '=', $schoolId)
            ->with([
                'nationality:id,name,code',
                'school:id,education_services_office_id',
            ])
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
    }
}

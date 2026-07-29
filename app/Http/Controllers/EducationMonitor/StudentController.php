<?php

namespace App\Http\Controllers\EducationMonitor;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\EducationMonitor\StudentCollection;
use App\Http\Resources\EducationMonitor\StudentResource;
use App\Http\Resources\EducationMonitor\StudentTransferCollection;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentTransfer;
use App\Support\ModelAbilityMap;
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
            return $query->forCurrentEducationMonitor();
        });

        $schoolId = $request->filled('school_id')
            ? $request->integer('school_id')
            : null;

        if ($schoolId !== null && ! $schools->contains('id', '=', $schoolId)) {
            $schoolId = null;
        }

        $students = $this->getPaginatedStudents($request, $schoolId);

        return Inertia::render('education-monitor/students/index', [
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
            ...ModelAbilityMap::make(Student::class, ['addTransferredStudent']),
        ]);
    }

    public function show(Request $request, Student $student): Response
    {
        Gate::authorize('view', $student);

        $student->load([
            'monitor:id,uuid,name',
            'school:id,uuid,name',
            'nationality:id,uuid,name,code',
            'enrollment',
            'enrollment.gradeLevel',
            'enrollment.classroom',
        ]);

        $transfers = $this->getStudentTransfers($request, $student);

        return Inertia::render('education-monitor/students/show', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
            'transfers' => $transfers,
            ...ModelAbilityMap::make($student, ['transferStudentOut', 'viewAcademicRecord', 'viewPsychosocialCard']),
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
            ->forCurrentEducationMonitor()
            ->where('students.school_id', '=', $schoolId)
            ->with(['nationality:id,name,code'])
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

    private function getStudentTransfers(Request $request, Student $student)
    {
        $transfers = StudentTransfer::query()
            ->select([
                'id',
                'uuid',
                'left_academic_year_id',
                'joined_academic_year_id',
                'student_id',
                'from_school_id',
                'to_school_id',
                'left_school_at',
                'joined_school_at',
                'created_at',
                'deleted_at',
            ])
            ->where('student_id', '=', $student->id)
            ->with([
                'leftAcademicYear',
                'joinedAcademicYear',
                'student',
                'fromSchool.monitor',
                'toSchool.monitor',
            ])
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return ResourcePayloadBuilder::paginate(
            $transfers,
            StudentTransferCollection::make($transfers),
            $request,
        );
    }
}

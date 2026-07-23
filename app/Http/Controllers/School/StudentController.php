<?php

namespace App\Http\Controllers\School;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\Student\StoreRequest;
use App\Http\Requests\School\Student\UpdateRequest;
use App\Http\Resources\School\StudentCollection;
use App\Http\Resources\School\StudentFormResource;
use App\Http\Resources\School\StudentResource;
use App\Http\Resources\School\StudentTransferCollection;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\Student;
use App\Models\StudentTransfer;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Student::class);

        $students = QueryBuilder::for(Student::class)
            ->select([
                'id',
                'uuid',
                'education_monitor_id',
                'school_id',
                'nationality_id',
                'number',
                'registration_status',
                'first_name',
                'father_name',
                'grandfather_name',
                'surname',
                'gender',
                'national_id',
                'family_registration_number',
                'passport_number',
                'created_at',
                'deleted_at',
            ])
            ->forCurrentSchool()
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

        return Inertia::render('school/students/index', [
            'students' => ResourcePayloadBuilder::paginateWithAbilities(
                $students,
                StudentCollection::make($students),
                ['view'],
                $request,
            ),
            'registrationStatuses' => StudentRegistrationStatus::optionsArray(),
            'nationalities' => Nationality::list(),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(Student::class, ['create', 'addTransferredStudent']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Student::class);

        return Inertia::render('school/students/create', [
            'gradeLevels' => GradeLevel::listForCurrentSchool(),
            'registrationStatuses' => StudentRegistrationStatus::optionsArray(),
            'nationalities' => Nationality::list(),
            'libyanNationalityId' => Nationality::libyanId(),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Student::class);

        $student = DB::transaction(function () use ($request) {
            /** @var Student $student */
            $student = Student::create($request->getAttributes());

            $student->enrollment()->create([
                'academic_year_id' => AcademicYear::currentId(),
                'school_id' => $student->school_id,
                'grade_level_id' => $request->validated('grade_level_id'),
            ]);

            return $student;
        });

        flash_success('create');

        return Redirect::route('school.students.show', ['student' => $student]);
    }

    public function show(Request $request, Student $student)
    {
        Gate::authorize('view', $student);

        $student->load([
            'nationality',
            'enrollment',
            'enrollment.gradeLevel',
            'enrollment.classroom',
        ])->loadExists(['enrollment']);

        $enrollmentGradeLevelId = $student->enrollment?->grade_level_id;

        $transfers = $this->getStudentTransfers($request, $student);

        return Inertia::render('school/students/show', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
            'gradeLevels' => Inertia::optional(function () {
                return GradeLevel::listForCurrentSchool();
            }),
            'classrooms' => Inertia::optional(function () use ($enrollmentGradeLevelId) {
                return Classroom::listForCurrentSchool($enrollmentGradeLevelId);
            }),
            'transfers' => $transfers,
            ...ModelAbilityMap::make($student, [
                'enrollInGradeLevel',
                'enrollInClassroom',
                'transferClassroom',
                'update',
                'transferStudentOut',
                'viewPsychosocialCard',
                'viewAcademicRecord',
            ]),
        ]);
    }

    public function edit(Student $student)
    {
        Gate::authorize('update', $student);

        $student->load(['nationality']);

        $nationalities = Nationality::list();
        $libyanNationalityId = Nationality::libyanId();

        return Inertia::render('school/students/edit', [
            'student' => ResourcePayloadBuilder::make(
                StudentFormResource::make($student),
            ),
            'nationalities' => $nationalities,
            'libyanNationalityId' => $libyanNationalityId,
        ]);
    }

    public function update(UpdateRequest $request, Student $student): RedirectResponse
    {
        Gate::authorize('update', $student);

        $student->update($request->getAttributes());

        flash_success('update');

        return Redirect::route('school.students.show', ['student' => $student]);
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

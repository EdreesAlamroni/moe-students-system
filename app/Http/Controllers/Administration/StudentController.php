<?php

namespace App\Http\Controllers\Administration;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\StudentCollection;
use App\Http\Resources\Administration\StudentResource;
use App\Models\EducationMonitor;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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

        $monitorId = $request->filled('education_monitor_id')
            ? $request->integer('education_monitor_id')
            : null;

        $schoolId = $request->filled('school_id')
            ? $request->integer('school_id')
            : null;

        $schools = $monitorId !== null
            ? School::list(function (Builder $query) use ($monitorId) {
                return $query->where('education_monitor_id', '=', $monitorId);
            }, ['education_monitor_id'])
            : collect([]);

        if ($schoolId !== null && ! $schools->contains('id', '=', $schoolId)) {
            $schoolId = null;
        }

        $students = $this->getPaginatedStudents($request, $schoolId);

        return Inertia::render('administration/students/index', [
            'monitors' => EducationMonitor::list(),
            'schools' => $schools,
            'education_monitor_id' => $monitorId,
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
            'school:id,uuid,name',
            'nationality:id,uuid,name,code',
        ]);

        return Inertia::render('administration/students/show', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
        ]);
    }

    private function getPaginatedStudents(Request $request, ?int $schoolId): ?LengthAwarePaginator
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
}

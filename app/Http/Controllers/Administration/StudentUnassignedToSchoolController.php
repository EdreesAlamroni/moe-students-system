<?php

namespace App\Http\Controllers\Administration;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\StudentCollection;
use App\Models\EducationMonitor;
use App\Models\Nationality;
use App\Models\Student;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentUnassignedToSchoolController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Student::class);

        $monitorId = $request->filled('education_monitor_id')
            ? $request->integer('education_monitor_id')
            : null;

        $monitors = EducationMonitor::list();

        if ($monitorId !== null && ! $monitors->contains('id', '=', $monitorId)) {
            $monitorId = null;
        }

        $students = $this->getPaginatedStudents($request, $monitorId);

        return Inertia::render('administration/students/unassigned-to-school/index', [
            'monitors' => $monitors,
            'education_monitor_id' => $monitorId,
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

    private function getPaginatedStudents(Request $request, ?int $monitorId): ?LengthAwarePaginator
    {
        if ($monitorId === null) {
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
            ->where('students.education_monitor_id', '=', $monitorId)
            ->unassignedToSchool()
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
    }
}

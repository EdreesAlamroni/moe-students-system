<?php

namespace App\Http\Controllers\Warehouse;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BookDistribution\StudentStatusRequest;
use App\Models\BookDistribution;
use App\Models\EducationMonitor;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BookDistributionStudentStatusController extends Controller
{
    public function index(StudentStatusRequest $request): Response
    {
        Gate::authorize('view', BookDistribution::class);

        $selectedAttributes = $request->getAttributes();
        $monitorId = $selectedAttributes['education_monitor_id'];
        $schoolId = $selectedAttributes['school_id'];
        $gradeLevelId = $selectedAttributes['grade_level_id'];

        $hasCompleteSelection = filled($monitorId) && filled($schoolId) && filled($gradeLevelId);

        $monitors = EducationMonitor::list(function ($query): void {
            $query->forCurrentWarehouse();
        }, ['warehouse_id']);

        $schools = filled($monitorId)
            ? School::list(function ($query) use ($monitorId): void {
                $query->forCurrentWarehouse()->where('education_monitor_id', '=', $monitorId);
            }, ['education_monitor_id'])
            : collect([]);

        $gradeLevels = filled($schoolId)
            ? GradeLevel::list(function ($query) use ($schoolId): void {
                $query->where('grade_levels.school_id', '=', $schoolId);
            }, ['grade_levels.school_id'])
            : collect([]);

        return Inertia::render('warehouse/book-distributions/student-status', [
            'monitors' => $monitors,
            'schools' => $schools,
            'gradeLevels' => $gradeLevels,
            'selected' => $selectedAttributes,
            'students' => function () use ($request, $hasCompleteSelection, $schoolId, $gradeLevelId) {
                return $hasCompleteSelection
                    ? $this->getPaginatedStudents($request, $schoolId, $gradeLevelId)
                    : null;
            },
            'registrationStatuses' => function () use ($hasCompleteSelection) {
                return $hasCompleteSelection ? StudentRegistrationStatus::optionsArray() : [];
            },
            'nationalities' => function () use ($hasCompleteSelection) {
                return $hasCompleteSelection ? Nationality::list() : [];
            },
            'filter' => $request->input('filter', []),
        ]);
    }

    private function getPaginatedStudents(StudentStatusRequest $request, int $schoolId, int $gradeLevelId): ?LengthAwarePaginator
    {
        return QueryBuilder::for(Student::class)
            ->select([
                'students.id',
                'students.uuid',
                'students.number',
                'students.first_name',
                'students.father_name',
                'students.grandfather_name',
                'students.surname',
                'students.gender',
            ])
            ->where('school_id', '=', $schoolId)
            ->whereHas('enrollments', function (Builder $query) use ($gradeLevelId): void {
                $query->where('grade_level_id', '=', $gradeLevelId);
            })
            ->withExists(['bookDistributionItem as already_distributed'])
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
            ->through(function (Student $student): array {
                return [
                    'uuid' => $student->uuid,
                    'number' => $student->number,
                    'full_name' => $student->full_name,
                    'gender' => $student->gender->toArray(),
                    'already_distributed' => (bool) $student->already_distributed,
                ];
            })
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);
    }
}

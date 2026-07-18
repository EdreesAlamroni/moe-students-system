<?php

namespace App\Http\Controllers\Warehouse;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BookDistribution\StudentStatusRequest;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\EducationMonitor;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
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
            ? $this->gradeLevelsForSchool($schoolId)
            : collect([]);

        return Inertia::render('warehouse/book-distributions/student-status', [
            'monitors' => $monitors,
            'schools' => $schools,
            'gradeLevels' => $gradeLevels,
            'selected' => $selectedAttributes,
            'filter' => $request->input('filter', []),
            ...($hasCompleteSelection ? [
                'students' => $this->getPaginatedStudents($request, $schoolId, $gradeLevelId),
                'registrationStatuses' => StudentRegistrationStatus::optionsArray(),
                'nationalities' => Nationality::list(),
            ] : []),
        ]);
    }

    private function gradeLevelsForSchool(int $schoolId): Collection
    {
        $academicYearId = AcademicYear::currentId();

        if (is_null($academicYearId)) {
            return collect([]);
        }

        return GradeLevel::list(function ($query) use ($schoolId, $academicYearId): void {
            $query->join('grade_level_school', function (JoinClause $join) use ($schoolId, $academicYearId): void {
                $join->on('grade_levels.id', '=', 'grade_level_school.grade_level_id')
                    ->where('grade_level_school.school_id', '=', $schoolId)
                    ->where('grade_level_school.academic_year_id', '=', $academicYearId);
            });
        });
    }

    private function getPaginatedStudents(StudentStatusRequest $request, int $schoolId, int $gradeLevelId): LengthAwarePaginator
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
            ->whereHas('enrollments', function (Builder $query) use ($gradeLevelId, $schoolId): void {
                $query
                    ->where('grade_level_id', '=', $gradeLevelId)
                    ->where('school_id', '=', $schoolId)
                    ->where('academic_year_id', '=', AcademicYear::currentId());
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
                    'id' => $student->id,
                    'uuid' => $student->uuid,
                    'number' => $student->number,
                    'full_name' => $student->full_name,
                    'gender' => $student->gender->toArray(),
                    'already_distributed' => $student->already_distributed,
                ];
            })
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);
    }
}

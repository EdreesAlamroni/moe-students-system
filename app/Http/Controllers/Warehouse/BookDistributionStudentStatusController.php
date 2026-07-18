<?php

namespace App\Http\Controllers\Warehouse;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BookDistribution\StudentStatusRequest;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\Student;
use App\Services\Warehouse\BookDistributionOrganizationSelection;
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
        $organization = app(BookDistributionOrganizationSelection::class)->resolve($selectedAttributes);
        $schoolId = $organization['schoolId'];
        $gradeLevelId = $selectedAttributes['grade_level_id'];

        $hasCompleteSelection = filled($organization['monitorId']) && filled($schoolId) && filled($gradeLevelId);

        $gradeLevels = filled($schoolId)
            ? $this->gradeLevelsForSchool($schoolId)
            : collect([]);

        return Inertia::render('warehouse/book-distributions/student-status', [
            'monitors' => $organization['monitors'],
            'schools' => $organization['schools'],
            'gradeLevels' => $gradeLevels,
            'selected' => $organization['selected'],
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

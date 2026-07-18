<?php

namespace App\Services\Warehouse;

use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\BookDistributionItem;
use App\Models\GradeLevel;
use App\Models\StudentEnrollment;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookDistributionGradeLevelStats
{
    /**
     * Grade-level checklist for warehouse book distribution (no per-student item aggregates).
     *
     * @return Collection<int, array{
     *     id: int,
     *     name: string,
     *     educational_stage: array{name: string, value: string|null, label: string|null}|null,
     *     students_count: int,
     *     already_distributed: bool,
     * }>
     */
    public function forDistribution(int $schoolId): Collection
    {
        return $this->build($schoolId, false);
    }

    /**
     * Full grade-level statistics including per-student distribution counts.
     *
     * @return Collection<int, array{
     *     id: int,
     *     name: string,
     *     educational_stage: array{name: string, value: string|null, label: string|null}|null,
     *     students_count: int,
     *     distributed_count: int,
     *     pending_count: int,
     *     already_distributed: bool,
     * }>
     */
    public function forSchool(int $schoolId): Collection
    {
        return $this->build($schoolId, true);
    }

    private function build(int $schoolId, bool $withStudentDistributionCounts): Collection
    {
        $academicYearId = AcademicYear::currentId();

        if (is_null($academicYearId)) {
            return collect([]);
        }

        $gradeLevels = GradeLevel::query()
            ->select([
                'grade_levels.id',
                'grade_levels.name',
                'grade_levels.educational_stage',
                'grade_levels.order',
            ])
            ->join('grade_level_school', function (JoinClause $join) use ($schoolId, $academicYearId): void {
                $join->on('grade_levels.id', '=', 'grade_level_school.grade_level_id')
                    ->where('grade_level_school.school_id', '=', $schoolId)
                    ->where('grade_level_school.academic_year_id', '=', $academicYearId);
            })
            ->orderBy('grade_levels.order')
            ->get();

        if ($gradeLevels->isEmpty()) {
            return collect();
        }

        $gradeLevelIds = $gradeLevels->modelKeys();

        $studentCounts = StudentEnrollment::query()
            ->select('grade_level_id', DB::raw('COUNT(*) as count'))
            ->where('academic_year_id', '=', $academicYearId)
            ->where('school_id', '=', $schoolId)
            ->whereIn('grade_level_id', $gradeLevelIds)
            ->groupBy('grade_level_id')
            ->pluck('count', 'grade_level_id');

        $confirmedGradeLevelIds = BookDistribution::query()
            ->where('academic_year_id', '=', $academicYearId)
            ->where('school_id', '=', $schoolId)
            ->whereIn('grade_level_id', $gradeLevelIds)
            ->pluck('grade_level_id')
            ->flip();

        $distributedStudentCounts = $withStudentDistributionCounts
            ? $this->distributedStudentCounts($academicYearId, $schoolId, $gradeLevelIds)
            : null;

        if ($withStudentDistributionCounts) {
            return $gradeLevels->map(function (GradeLevel $gradeLevel) use ($studentCounts, $distributedStudentCounts, $confirmedGradeLevelIds): array {
                $studentsCount = (int) ($studentCounts[$gradeLevel->id] ?? 0);
                $distributedCount = (int) ($distributedStudentCounts[$gradeLevel->id] ?? 0);

                return [
                    'id' => $gradeLevel->id,
                    'name' => $gradeLevel->name,
                    'educational_stage' => $gradeLevel->educational_stage->toArray(),
                    'students_count' => $studentsCount,
                    'distributed_count' => $distributedCount,
                    'pending_count' => max(0, $studentsCount - $distributedCount),
                    'already_distributed' => isset($confirmedGradeLevelIds[$gradeLevel->id]),
                ];
            })->values();
        }

        return $gradeLevels->map(function (GradeLevel $gradeLevel) use ($studentCounts, $confirmedGradeLevelIds): array {
            return [
                'id' => $gradeLevel->id,
                'name' => $gradeLevel->name,
                'educational_stage' => $gradeLevel->educational_stage->toArray(),
                'students_count' => (int) ($studentCounts[$gradeLevel->id] ?? 0),
                'already_distributed' => isset($confirmedGradeLevelIds[$gradeLevel->id]),
            ];
        })->values();
    }

    /**
     * @param  iterable<int, array{
     *     id: int,
     *     name: string,
     *     educational_stage: array{name: string, value: string|null, label: string|null}|null,
     *     students_count: int,
     *     distributed_count: int,
     *     pending_count: int,
     *     already_distributed: bool,
     * }>  $statistics
     * @return array{students_count: int, distributed_count: int, pending_count: int}
     */
    public function totals(iterable $statistics): array
    {
        $statistics = collect($statistics);

        $confirmedStatistics = $statistics->filter(function (array $statistic): bool {
            return $statistic['already_distributed'];
        });

        return [
            'students_count' => (int) $statistics->sum('students_count'),
            'distributed_count' => (int) $confirmedStatistics->sum('distributed_count'),
            'pending_count' => (int) $confirmedStatistics->sum('pending_count'),
        ];
    }

    private function distributedStudentCounts(int $academicYearId, int $schoolId, array $gradeLevelIds): Collection
    {
        return BookDistributionItem::query()
            ->select('student_enrollments.grade_level_id', DB::raw('COUNT(DISTINCT book_distribution_items.student_id) as count'))
            ->join('student_enrollments', function (JoinClause $join) use ($academicYearId, $schoolId): void {
                $join->on('student_enrollments.student_id', '=', 'book_distribution_items.student_id')
                    ->where('student_enrollments.academic_year_id', '=', $academicYearId)
                    ->where('student_enrollments.school_id', '=', $schoolId);
            })
            ->where('book_distribution_items.academic_year_id', '=', $academicYearId)
            ->whereIn('student_enrollments.grade_level_id', $gradeLevelIds)
            ->groupBy('student_enrollments.grade_level_id')
            ->pluck('count', 'grade_level_id');
    }
}

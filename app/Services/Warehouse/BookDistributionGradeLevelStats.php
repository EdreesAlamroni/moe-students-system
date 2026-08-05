<?php

namespace App\Services\Warehouse;

use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\BookDistributionItem;
use App\Models\GradeLevel;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookDistributionGradeLevelStats
{
    /**
     * Grade-level checklist for warehouse book distribution (no per-student item aggregates).
     */
    public function forDistribution(int $schoolPeriodId): Collection
    {
        return $this->build($schoolPeriodId, false);
    }

    /**
     * Full grade-level statistics including per-student distribution counts.
     */
    public function forSchoolPeriod(int $schoolPeriodId): Collection
    {
        return $this->build($schoolPeriodId, true);
    }

    private function build(int $schoolPeriodId, bool $withStudentDistributionCounts): Collection
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return collect([]);
        }

        /** @var EloquentCollection<int, GradeLevel> $gradeLevels */
        $gradeLevels = GradeLevel::query()
            ->select([
                'grade_levels.id',
                'grade_levels.name',
                'grade_levels.educational_stage',
                'grade_levels.order',
            ])
            ->join('grade_level_school_period', function (JoinClause $join) use ($schoolPeriodId, $currentAcademicYearId): void {
                $join->on('grade_levels.id', '=', 'grade_level_school_period.grade_level_id')
                    ->where('grade_level_school_period.school_period_id', '=', $schoolPeriodId)
                    ->where('grade_level_school_period.academic_year_id', '=', $currentAcademicYearId);
            })
            ->orderBy('grade_levels.order')
            ->get();

        if ($gradeLevels->isEmpty()) {
            return collect([]);
        }

        $gradeLevelIds = $gradeLevels->pluck('id')->toArray();

        $studentCounts = StudentEnrollment::query()
            ->select('grade_level_id', DB::raw('COUNT(*) as count'))
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->where('school_period_id', '=', $schoolPeriodId)
            ->whereIn('grade_level_id', $gradeLevelIds)
            ->groupBy('grade_level_id')
            ->pluck('count', 'grade_level_id');

        $confirmedGradeLevelIds = BookDistribution::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->where('school_period_id', '=', $schoolPeriodId)
            ->whereIn('grade_level_id', $gradeLevelIds)
            ->pluck('grade_level_id')
            ->flip();

        $distributedStudentCounts = $withStudentDistributionCounts
            ? $this->distributedStudentCounts($schoolPeriodId, $gradeLevelIds)
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

    private function distributedStudentCounts(int $schoolPeriodId, array $gradeLevelIds): Collection
    {
        $currentAcademicYearId = AcademicYear::currentId();

        return BookDistributionItem::query()
            ->select('student_enrollments.grade_level_id', DB::raw('COUNT(DISTINCT book_distribution_items.student_id) as count'))
            ->join('student_enrollments', function (JoinClause $join) use ($currentAcademicYearId, $schoolPeriodId): void {
                $join->on('student_enrollments.student_id', '=', 'book_distribution_items.student_id')
                    ->where('student_enrollments.academic_year_id', '=', $currentAcademicYearId)
                    ->where('student_enrollments.school_period_id', '=', $schoolPeriodId);
            })
            ->where('book_distribution_items.academic_year_id', '=', $currentAcademicYearId)
            ->whereIn('student_enrollments.grade_level_id', $gradeLevelIds)
            ->groupBy('student_enrollments.grade_level_id')
            ->pluck('count', 'grade_level_id');
    }
}

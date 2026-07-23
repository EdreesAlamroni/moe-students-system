<?php

namespace App\Services\School\ClassroomDistribution\Shared;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class ClassroomDistributionHelper
{
    public static function getCurrentSchoolId(): int
    {
        return once(function (): int {
            return auth('school')->user()->organization_id;
        });
    }

    /**
     * @return EloquentCollection<int, Classroom>
     */
    public static function getClassroomsForGrade(int $gradeLevelId, array $classroomIds = []): EloquentCollection
    {
        $academicYearId = AcademicYear::currentId();
        $schoolId = self::getCurrentSchoolId();

        return Classroom::query()
            ->select(['id', 'uuid', 'grade_level_id', 'name', 'capacity'])
            ->where('school_id', '=', $schoolId)
            ->where('academic_year_id', '=', $academicYearId)
            ->where('grade_level_id', '=', $gradeLevelId)
            ->when(! empty($classroomIds), function (Builder $query) use ($classroomIds): void {
                $query->whereIn('id', $classroomIds);
            })
            ->withCount(['students'])
            ->orderBy('name')
            ->get()
            ->each(function (Classroom $classroom) {
                $occupied = $classroom->students_count;

                $classroom->setAttribute('remaining_capacity', max(0, $classroom->capacity - $occupied));
            });
    }

    /**
     * @return EloquentCollection<int, Student>
     */
    public static function getStudentsWithoutClassroom(int $gradeLevelId): EloquentCollection
    {
        return self::getStudentsWithoutClassroomQuery($gradeLevelId)
            ->orderByFullName()
            ->get();
    }

    /**
     * @return EloquentCollection<int, Student>
     */
    public static function getSelectedStudentsWithoutClassroom(int $gradeLevelId, array $studentIds): EloquentCollection
    {
        return self::getStudentsWithoutClassroomQuery($gradeLevelId)
            ->whereIn('id', $studentIds)
            ->get();
    }

    public static function getCountStudentsWithoutClassroom(int $gradeLevelId): int
    {
        return self::getStudentsWithoutClassroomQuery($gradeLevelId)->count();
    }

    /**
     * @return array{
     *     total_count: int,
     *     eligible_count: int,
     *     without_grade_level_count: int,
     *     without_classroom_count: int,
     * }
     */
    public static function getEnrollmentSummaryForCurrentSchoolAndYear(): array
    {
        $query = self::enrollmentQueryForCurrentSchoolAndYear();

        $totalCount = (clone $query)->count();
        $withoutGradeLevelCount = (clone $query)->whereNull('grade_level_id')->count();
        $eligibleCount = $totalCount - $withoutGradeLevelCount;
        $withoutClassroomCount = (clone $query)
            ->whereNotNull('grade_level_id')
            ->whereNull('classroom_id')
            ->count();

        return [
            'total_count' => $totalCount,
            'eligible_count' => $eligibleCount,
            'without_grade_level_count' => $withoutGradeLevelCount,
            'without_classroom_count' => $withoutClassroomCount,
        ];
    }

    public static function getCountEligibleEnrollmentsForCurrentSchoolAndYear(): int
    {
        return self::getEnrollmentSummaryForCurrentSchoolAndYear()['eligible_count'];
    }

    public static function getCountEnrollmentsWithoutGradeLevelForCurrentSchoolAndYear(): int
    {
        return self::getEnrollmentSummaryForCurrentSchoolAndYear()['without_grade_level_count'];
    }

    public static function resolveEnrollmentGuardFailure(): ?string
    {
        $summary = self::getEnrollmentSummaryForCurrentSchoolAndYear();

        if ($summary['total_count'] === 0) {
            return 'classroom-distribution-no-enrollments';
        }

        if ($summary['eligible_count'] === 0) {
            return 'classroom-distribution-no-grade-level-enrollments';
        }

        return null;
    }

    public static function getCountEnrollmentsForCurrentSchoolAndYear(): int
    {
        return self::getEnrollmentSummaryForCurrentSchoolAndYear()['total_count'];
    }

    public static function getCountEnrollmentsWithoutClassroom(): int
    {
        return self::getEnrollmentSummaryForCurrentSchoolAndYear()['without_classroom_count'];
    }

    /**
     * @param  EloquentCollection<int, Classroom>  $classrooms
     * @return array<int>
     */
    public static function buildCapacitySlots(EloquentCollection $classrooms): array
    {
        $academicYearId = AcademicYear::currentId();

        $occupied = StudentEnrollment::query()
            ->select('classroom_id')
            ->selectRaw('COUNT(*) as occupied_count')
            ->whereIn('classroom_id', $classrooms->pluck('id')->all())
            ->where('academic_year_id', '=', $academicYearId)
            ->groupBy('classroom_id')
            ->pluck('occupied_count', 'classroom_id')
            ->all();

        $slots = [];

        foreach ($classrooms as $classroom) {
            $occupiedCount = ($occupied[$classroom->id] ?? 0);

            $remaining = max(0, $classroom->capacity - $occupiedCount);

            for ($i = 0; $i < $remaining; $i++) {
                $slots[] = $classroom->id;
            }
        }

        shuffle($slots);

        return $slots;
    }

    /**
     * @return Builder<Student>
     */
    protected static function getStudentsWithoutClassroomQuery(int $gradeLevelId): Builder
    {
        $academicYearId = AcademicYear::currentId();
        $schoolId = self::getCurrentSchoolId();

        return Student::query()
            ->where('school_id', '=', $schoolId)
            ->whereHas('enrollments', function (Builder $query) use ($academicYearId, $gradeLevelId): void {
                $query
                    ->where('academic_year_id', '=', $academicYearId)
                    ->where('grade_level_id', '=', $gradeLevelId)
                    ->whereNull('classroom_id');
            });
    }

    /**
     * @return Builder<StudentEnrollment>
     */
    protected static function enrollmentQueryForCurrentSchoolAndYear(): Builder
    {
        $academicYearId = AcademicYear::currentId();

        if (is_null($academicYearId)) {
            return StudentEnrollment::query()->whereRaw('1 = 0');
        }

        $schoolId = self::getCurrentSchoolId();

        return StudentEnrollment::query()
            ->where('academic_year_id', '=', $academicYearId)
            ->where('school_id', '=', $schoolId);
    }
}

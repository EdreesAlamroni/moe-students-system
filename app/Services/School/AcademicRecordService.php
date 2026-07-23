<?php

namespace App\Services\School;

use App\Enums\AcademicRecordStatus;
use App\Enums\StudentRegistrationStatus;
use App\Models\AcademicRecord;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Student;
use Illuminate\Support\Collection;

class AcademicRecordService
{
    /**
     * Get grade levels that precede the given enrollment grade.
     *
     * @return Collection<int, GradeLevel>
     */
    public function precedingGradeLevels(GradeLevel $currentGradeLevel): Collection
    {
        return GradeLevel::query()
            ->select(['id', 'code', 'name', 'order'])
            ->where('code', 'like', 'grade_%')
            ->where('order', '<', $currentGradeLevel->order)
            ->ordered()
            ->get();
    }

    /**
     * Load a student's academic records grouped by grade level.
     *
     * @return Collection<int, Collection<int, AcademicRecord>>
     */
    public function recordsGroupedByGradeLevel(Student $student): Collection
    {
        return AcademicRecord::query()
            ->where('student_id', $student->id)
            ->with(['gradeLevel', 'academicYear'])
            ->orderBy('id')
            ->get()
            ->groupBy('grade_level_id');
    }

    /**
     * Determine whether the student must complete prior-grade academic records.
     */
    public function requiresAcademicRecord(Student $student): bool
    {
        $enrollmentGradeLevel = $this->enrollmentGradeLevel($student);

        if ($enrollmentGradeLevel === null) {
            return false;
        }

        return $this->precedingGradeLevels($enrollmentGradeLevel)->isNotEmpty();
    }

    /**
     * Determine whether all required prior-grade records have been passed.
     */
    public function isComplete(Student $student): bool
    {
        $context = $this->studentRecordContext($student);

        if ($context === null) {
            return false;
        }

        return $this->findCurrentGradeLevel(
            $context['preceding_grade_levels'],
            $context['grouped_records'],
        ) === null;
    }

    /**
     * Get the next grade level awaiting an academic record entry.
     */
    public function getCurrentGradeLevel(Student $student): ?GradeLevel
    {
        $context = $this->studentRecordContext($student);

        if ($context === null) {
            return null;
        }

        return $this->findCurrentGradeLevel(
            $context['preceding_grade_levels'],
            $context['grouped_records'],
        );
    }

    /**
     * Determine whether the student has a passed attempt for a grade level.
     */
    public function hasPassedAttempt(Student $student, GradeLevel $gradeLevel): bool
    {
        return $this->gradeLevelHasPassed(
            $gradeLevel,
            $this->recordsGroupedByGradeLevel($student),
        );
    }

    /**
     * Resolve the stored status for a new attempt based on prior attempts.
     */
    public function resolveAttemptStatus(Student $student, GradeLevel $gradeLevel, AcademicRecordStatus $submittedStatus): AcademicRecordStatus
    {
        if ($submittedStatus !== AcademicRecordStatus::PASSED) {
            return $submittedStatus;
        }

        $attempts = $this->recordsGroupedByGradeLevel($student)->get($gradeLevel->id, collect());

        if ($attempts->isNotEmpty()) {
            return AcademicRecordStatus::PROMOTED;
        }

        return AcademicRecordStatus::PASSED;
    }

    /**
     * Determine whether the student already has an academic record for a year.
     */
    public function studentHasAcademicYearRecord(Student $student, int $academicYearId): bool
    {
        return AcademicRecord::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->exists();
    }

    /**
     * Determine whether a new attempt may be submitted for a grade level.
     */
    public function canAddAttempt(Student $student, GradeLevel $gradeLevel): bool
    {
        $context = $this->studentRecordContext($student);

        if ($context === null) {
            return false;
        }

        $currentGradeLevel = $this->findCurrentGradeLevel(
            $context['preceding_grade_levels'],
            $context['grouped_records'],
        );

        if ($currentGradeLevel?->id !== $gradeLevel->id) {
            return false;
        }

        /** @var Collection<int, AcademicRecord> $attempts */
        $attempts = $context['grouped_records']->get($gradeLevel->id, collect());

        if ($attempts->isEmpty()) {
            return true;
        }

        return $attempts->last()->status === AcademicRecordStatus::FAILED;
    }

    /**
     * Derive the student's registration status from final grade-level attempts.
     */
    public function calculateRegistrationStatus(Student $student): StudentRegistrationStatus
    {
        $context = $this->studentRecordContext($student);

        if ($context === null) {
            return StudentRegistrationStatus::NEW;
        }

        /** @var GradeLevel|null $finalGradeLevel */
        $finalGradeLevel = $context['preceding_grade_levels']->last();

        if ($finalGradeLevel === null) {
            return StudentRegistrationStatus::NEW;
        }

        $attemptCount = $context['grouped_records']->get($finalGradeLevel->id, collect([]))->count();

        return match (true) {
            $attemptCount <= 1 => StudentRegistrationStatus::NEW,
            $attemptCount === 2 => StudentRegistrationStatus::REPEATER,
            default => StudentRegistrationStatus::EXCEPTIONAL_YEAR,
        };
    }

    /**
     * Build grouped records and completion state for the show page.
     *
     * @return array{
     *     groupedRecords: array<int, array<string, mixed>>,
     *     requiresAcademicRecord: bool,
     *     isComplete: bool
     * }
     */
    public function resolveShowPageData(Student $student): array
    {
        $context = $this->studentRecordContext($student);

        if ($context === null) {
            return [
                'groupedRecords' => [],
                'requiresAcademicRecord' => false,
                'isComplete' => false,
            ];
        }

        $currentGradeLevel = $this->findCurrentGradeLevel(
            $context['preceding_grade_levels'],
            $context['grouped_records'],
        );

        return [
            'groupedRecords' => $this->buildGroupedRecords(
                $context['preceding_grade_levels'],
                $context['grouped_records'],
                $currentGradeLevel,
            ),
            'requiresAcademicRecord' => true,
            'isComplete' => $currentGradeLevel === null,
        ];
    }

    /**
     * Build form data and entry progress for the create page.
     *
     * @return array{
     *     groupedRecords: array<int, array<string, mixed>>,
     *     currentGradeLevel: array{id: int, name: string}|null,
     *     selectableAcademicYears: array<int, array{id: int, name: string}>,
     *     progress: array{completed: int, total: int}
     * }
     */
    public function resolveCreatePageData(Student $student): array
    {
        $context = $this->studentRecordContext($student);

        if ($context === null) {
            return [
                'groupedRecords' => [],
                'currentGradeLevel' => null,
                'selectableAcademicYears' => [],
                'progress' => [
                    'completed' => 0,
                    'total' => 0,
                ],
            ];
        }

        $usedAcademicYearIds = $this->usedAcademicYearIdsFromGroupedRecords($context['grouped_records']);

        $currentGradeLevel = $this->findCurrentGradeLevel(
            $context['preceding_grade_levels'],
            $context['grouped_records'],
        );

        $completedCount = 0;

        foreach ($context['preceding_grade_levels'] as $gradeLevel) {
            if ($this->gradeLevelHasPassed($gradeLevel, $context['grouped_records'])) {
                $completedCount++;
            }
        }

        return [
            'groupedRecords' => $this->buildGroupedRecords(
                $context['preceding_grade_levels'],
                $context['grouped_records'],
                $currentGradeLevel,
            ),
            'currentGradeLevel' => $currentGradeLevel?->only(['id', 'name']),
            'selectableAcademicYears' => $this->filterSelectableAcademicYears($usedAcademicYearIds),
            'progress' => [
                'completed' => $completedCount,
                'total' => $context['preceding_grade_levels']->count(),
            ],
        ];
    }

    /**
     * Resolve enrollment context and grouped records for a student.
     *
     * @return array{
     *     enrollment_grade_level: GradeLevel,
     *     preceding_grade_levels: Collection<int, GradeLevel>,
     *     grouped_records: Collection<int, Collection<int, AcademicRecord>>
     * }|null
     */
    private function studentRecordContext(Student $student): ?array
    {
        $enrollmentGradeLevel = $this->enrollmentGradeLevel($student);

        if ($enrollmentGradeLevel === null) {
            return null;
        }

        $precedingGradeLevels = $this->precedingGradeLevels($enrollmentGradeLevel);

        if ($precedingGradeLevels->isEmpty()) {
            return null;
        }

        return [
            'enrollment_grade_level' => $enrollmentGradeLevel,
            'preceding_grade_levels' => $precedingGradeLevels,
            'grouped_records' => $this->recordsGroupedByGradeLevel($student),
        ];
    }

    /**
     * Get the grade level from the student's current enrollment.
     */
    private function enrollmentGradeLevel(Student $student): ?GradeLevel
    {
        $gradeLevel = $student->enrollment?->gradeLevel;

        return $gradeLevel instanceof GradeLevel ? $gradeLevel : null;
    }

    /**
     * Find the first preceding grade level without a passed attempt.
     *
     * @param  Collection<int, GradeLevel>  $precedingGradeLevels
     * @param  Collection<int, Collection<int, AcademicRecord>>  $groupedRecords
     */
    private function findCurrentGradeLevel(Collection $precedingGradeLevels, Collection $groupedRecords): ?GradeLevel
    {
        foreach ($precedingGradeLevels as $gradeLevel) {
            if (! $this->gradeLevelHasPassed($gradeLevel, $groupedRecords)) {
                return $gradeLevel;
            }
        }

        return null;
    }

    /**
     * Determine whether any attempt for a grade level has passed.
     *
     * @param  Collection<int, Collection<int, AcademicRecord>>  $groupedRecords
     */
    private function gradeLevelHasPassed(GradeLevel $gradeLevel, Collection $groupedRecords): bool
    {
        foreach ($groupedRecords->get($gradeLevel->id, collect()) as $record) {
            if ($record->status->isPassing()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format preceding grade levels and attempts for display.
     *
     * @param  Collection<int, GradeLevel>  $precedingGradeLevels
     * @param  Collection<int, Collection<int, AcademicRecord>>  $groupedRecords
     * @return array<int, array{grade_level: array{id: int, name: string}, attempts: array<int, array<string, mixed>>, is_passed: bool, is_current: bool}>
     */
    private function buildGroupedRecords(Collection $precedingGradeLevels, Collection $groupedRecords, ?GradeLevel $currentGradeLevel): array
    {
        return $precedingGradeLevels
            ->map(function (GradeLevel $gradeLevel) use ($groupedRecords, $currentGradeLevel): array {
                /** @var Collection<int, AcademicRecord> $attempts */
                $attempts = $groupedRecords->get($gradeLevel->id, collect());

                return [
                    'grade_level' => $gradeLevel->only(['id', 'name']),
                    'attempts' => $attempts->map(function (AcademicRecord $record): array {
                        return $this->toArray($record);
                    })->values()->all(),
                    'is_passed' => $this->gradeLevelHasPassed($gradeLevel, $groupedRecords),
                    'is_current' => $currentGradeLevel?->id === $gradeLevel->id,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Extract used academic year IDs from grouped records.
     *
     * @param  Collection<int, Collection<int, AcademicRecord>>  $groupedRecords
     * @return array<int, int>
     */
    private function usedAcademicYearIdsFromGroupedRecords(Collection $groupedRecords): array
    {
        return $groupedRecords
            ->flatten(1)
            ->pluck('academic_year_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Filter out academic years already assigned to the student.
     *
     * @param  array<int, int>  $usedAcademicYearIds
     * @return array<int, array{id: int, name: string}>
     */
    private function filterSelectableAcademicYears(array $usedAcademicYearIds): array
    {
        return AcademicYear::list()->reject(function (array $year) use ($usedAcademicYearIds): bool {
            return in_array($year['id'], $usedAcademicYearIds, true);
        })->values()->all();
    }

    /**
     * Transform an academic record for frontend display.
     *
     * @return array<string, mixed>
     */
    private function toArray(AcademicRecord $record): array
    {
        return [
            'id' => $record->id,
            'uuid' => $record->uuid,
            'academic_year' => $record->academicYear?->only(['id', 'name']),
            'status' => $record->status->toArray(),
            'rating' => $record->rating?->toArray(),
            'created_at' => $record->created_at?->toISOString(),
        ];
    }
}

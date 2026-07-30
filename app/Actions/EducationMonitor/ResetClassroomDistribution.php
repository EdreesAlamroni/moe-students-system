<?php

namespace App\Actions\EducationMonitor;

use App\Enums\ClassroomDistributionResetScope;
use App\Models\AcademicYear;
use App\Models\ClassroomDistributionCompletion;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResetClassroomDistribution
{
    /**
     * @return array{
     *     has_distribution_data: bool,
     *     eligible_grade_levels: list<array{id: int, name: string}>,
     * }
     */
    public function summary(School $school): array
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if ($currentAcademicYearId === null) {
            return [
                'has_distribution_data' => false,
                'eligible_grade_levels' => [],
            ];
        }

        $schoolGradeLevelIds = $school->gradeLevels()->pluck('grade_levels.id')->all();

        if ($schoolGradeLevelIds === []) {
            return [
                'has_distribution_data' => $this->hasDistributionData($school, $currentAcademicYearId, []),
                'eligible_grade_levels' => [],
            ];
        }

        $gradeLevelIdsWithAssignments = StudentEnrollment::query()
            ->where('school_id', '=', $school->id)
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->whereIn('grade_level_id', $schoolGradeLevelIds)
            ->whereNotNull('classroom_id')
            ->distinct()
            ->pluck('grade_level_id')
            ->all();

        $eligibleGradeLevels = GradeLevel::query()
            ->select(['id', 'name', 'order'])
            ->whereIn('id', $gradeLevelIdsWithAssignments)
            ->orderBy('order')
            ->get()
            ->map(function (GradeLevel $gradeLevel): array {
                return [
                    'id' => $gradeLevel->id,
                    'name' => $gradeLevel->name,
                ];
            })
            ->values()
            ->all();

        return [
            'has_distribution_data' => $this->hasDistributionData($school, $currentAcademicYearId, $schoolGradeLevelIds),
            'eligible_grade_levels' => $eligibleGradeLevels,
        ];
    }

    public function execute(School $school, ClassroomDistributionResetScope $scope, array $gradeLevelIds = []): void
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if ($currentAcademicYearId === null) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.academic-year-not-found')],
            ]);
        }

        $targetGradeLevelIds = $scope === ClassroomDistributionResetScope::ALL
            ? $school->gradeLevels()->pluck('grade_levels.id')->all()
            : $gradeLevelIds;

        if (! $this->hasDistributionData($school, $currentAcademicYearId, $targetGradeLevelIds)) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.classroom-distribution-reset-nothing-to-reset')],
            ]);
        }

        DB::transaction(function () use ($school, $currentAcademicYearId, $targetGradeLevelIds): void {
            if ($targetGradeLevelIds !== []) {
                StudentEnrollment::query()
                    ->where('school_id', '=', $school->id)
                    ->where('academic_year_id', '=', $currentAcademicYearId)
                    ->whereIn('grade_level_id', $targetGradeLevelIds)
                    ->whereNotNull('classroom_id')
                    ->update(['classroom_id' => null]);
            }

            ClassroomDistributionCompletion::query()
                ->where('school_id', '=', $school->id)
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->delete();
        });
    }

    private function hasDistributionData(School $school, int $currentAcademicYearId, array $gradeLevelIds): bool
    {
        if ($gradeLevelIds !== []) {
            $hasClassroomAssignments = StudentEnrollment::query()
                ->where('school_id', '=', $school->id)
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->whereIn('grade_level_id', $gradeLevelIds)
                ->whereNotNull('classroom_id')
                ->exists();

            if ($hasClassroomAssignments) {
                return true;
            }
        }

        return ClassroomDistributionCompletion::query()
            ->where('school_id', '=', $school->id)
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->exists();
    }
}

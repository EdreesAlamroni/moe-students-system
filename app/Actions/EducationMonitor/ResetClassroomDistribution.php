<?php

namespace App\Actions\EducationMonitor;

use App\Enums\ClassroomDistributionResetScope;
use App\Models\AcademicYear;
use App\Models\ClassroomDistributionCompletion;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchoolPeriod;
use App\Models\School;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResetClassroomDistribution
{
    public function summary(School $school): array
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return [
                'has_distribution_data' => false,
                'eligible_grade_levels' => [],
            ];
        }

        $schoolPeriodIds = $school->periods()->pluck('school_periods.id')->all();

        $schoolGradeLevelIds = GradeLevelSchoolPeriod::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->whereIn('school_period_id', $schoolPeriodIds)
            ->distinct()
            ->pluck('grade_level_id')
            ->all();

        if (empty($schoolGradeLevelIds)) {
            return [
                'has_distribution_data' => $this->hasDistributionData($currentAcademicYearId, $schoolPeriodIds, []),
                'eligible_grade_levels' => [],
            ];
        }

        $gradeLevelIdsWithAssignments = StudentEnrollment::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->whereIn('school_period_id', $schoolPeriodIds)
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
            'has_distribution_data' => $this->hasDistributionData($currentAcademicYearId, $schoolPeriodIds, $schoolGradeLevelIds),
            'eligible_grade_levels' => $eligibleGradeLevels,
        ];
    }

    public function execute(School $school, ClassroomDistributionResetScope $scope, array $gradeLevelIds = []): void
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.academic-year-not-found')],
            ]);
        }

        $schoolPeriodIds = $school->periods()->pluck('school_periods.id')->all();

        $targetGradeLevelIds = $scope === ClassroomDistributionResetScope::ALL
            ? GradeLevelSchoolPeriod::query()
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->whereIn('school_period_id', $schoolPeriodIds)
                ->distinct()
                ->pluck('grade_level_id')
                ->all()
            : $gradeLevelIds;

        if (! $this->hasDistributionData($currentAcademicYearId, $schoolPeriodIds, $targetGradeLevelIds)) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.classroom-distribution-reset-nothing-to-reset')],
            ]);
        }

        DB::transaction(function () use ($schoolPeriodIds, $currentAcademicYearId, $targetGradeLevelIds): void {
            if (! empty($targetGradeLevelIds)) {
                StudentEnrollment::query()
                    ->where('academic_year_id', '=', $currentAcademicYearId)
                    ->whereIn('school_period_id', $schoolPeriodIds)
                    ->whereIn('grade_level_id', $targetGradeLevelIds)
                    ->whereNotNull('classroom_id')
                    ->update(['classroom_id' => null]);
            }

            ClassroomDistributionCompletion::query()
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->whereIn('school_period_id', $schoolPeriodIds)
                ->delete();
        });
    }

    private function hasDistributionData(int $currentAcademicYearId, array $schoolPeriodIds, array $gradeLevelIds): bool
    {
        if (
            ! empty($gradeLevelIds) && StudentEnrollment::query()
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->whereIn('school_period_id', $schoolPeriodIds)
                ->whereIn('grade_level_id', $gradeLevelIds)
                ->whereNotNull('classroom_id')
                ->exists()
        ) {
            return true;
        }

        return ClassroomDistributionCompletion::query()
            ->where('academic_year_id', '=', $currentAcademicYearId)
            ->whereIn('school_period_id', $schoolPeriodIds)
            ->exists();
    }
}

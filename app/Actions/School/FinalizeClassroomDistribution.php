<?php

namespace App\Actions\School;

use App\Models\AcademicYear;
use App\Models\ClassroomDistributionCompletion;
use App\Services\School\ClassroomDistribution\Shared\ClassroomDistributionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinalizeClassroomDistribution
{
    public function execute(): void
    {
        if ($guardFailure = ClassroomDistributionHelper::resolveEnrollmentGuardFailure()) {
            throw ValidationException::withMessages([
                '_' => [__("alerts.messages.{$guardFailure}")],
            ]);
        }

        $enrollmentSummary = ClassroomDistributionHelper::getEnrollmentSummaryForCurrentSchoolAndYear();

        if ($enrollmentSummary['without_grade_level_count'] > 0) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.classroom-distribution-enrollments-missing-grade-level', [
                    'count' => $enrollmentSummary['without_grade_level_count'],
                ])],
            ]);
        }

        if (ClassroomDistributionCompletion::isCompleteForCurrentSchoolAndYear()) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.classroom-distribution-already-finalized')],
            ]);
        }

        // Check if there are any students who remain unassigned to any classroom
        $unassigned = ClassroomDistributionHelper::getCountEnrollmentsWithoutClassroom();
        if ($unassigned > 0) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.classroom-distribution-finalize-students-unassigned', ['count' => $unassigned])],
            ]);
        }

        $academicYearId = AcademicYear::currentId();
        if ($academicYearId === null) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.academic-year-not-found')],
            ]);
        }

        DB::transaction(function () use ($academicYearId) {
            ClassroomDistributionCompletion::create([
                'school_id' => auth('school')->user()->organization_id,
                'academic_year_id' => $academicYearId,
                'completed_at' => now(),
            ]);
        });
    }
}

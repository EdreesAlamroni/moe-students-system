<?php

namespace App\Actions\School;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchoolPeriod;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;

class TransferGradeLevels
{
    public function execute(SchoolPeriod $sourcePeriod, SchoolPeriod $destinationPeriod, array $gradeLevelIds): void
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId) || $gradeLevelIds === []) {
            return;
        }

        DB::transaction(function () use ($sourcePeriod, $destinationPeriod, $gradeLevelIds, $currentAcademicYearId): void {
            $this->ensureDestinationHasRequiredEducationalStages(
                $currentAcademicYearId,
                $destinationPeriod,
                $gradeLevelIds,
            );

            $classroomIds = Classroom::query()
                ->withTrashed()
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->where('school_period_id', '=', $sourcePeriod->id)
                ->whereIn('grade_level_id', $gradeLevelIds)
                ->pluck('id');

            $studentIds = StudentEnrollment::query()
                ->withTrashed()
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->where('school_period_id', '=', $sourcePeriod->id)
                ->whereIn('grade_level_id', $gradeLevelIds)
                ->pluck('student_id');

            if ($classroomIds->isNotEmpty()) {
                ClassSchedule::query()
                    ->where('academic_year_id', '=', $currentAcademicYearId)
                    ->where('school_period_id', '=', $sourcePeriod->id)
                    ->whereIn('classroom_id', $classroomIds)
                    ->delete();
            }

            GradeLevelSchoolPeriod::query()
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->where('school_period_id', '=', $sourcePeriod->id)
                ->whereIn('grade_level_id', $gradeLevelIds)
                ->update([
                    'school_period_id' => $destinationPeriod->id,
                ]);

            Classroom::query()
                ->withTrashed()
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->where('school_period_id', '=', $sourcePeriod->id)
                ->whereIn('grade_level_id', $gradeLevelIds)
                ->update([
                    'school_period_id' => $destinationPeriod->id,
                ]);

            StudentEnrollment::query()
                ->withTrashed()
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->where('school_period_id', '=', $sourcePeriod->id)
                ->whereIn('grade_level_id', $gradeLevelIds)
                ->update([
                    'school_period_id' => $destinationPeriod->id,
                ]);

            if ($studentIds->isNotEmpty()) {
                Student::query()
                    ->withTrashed()
                    ->where('school_period_id', '=', $sourcePeriod->id)
                    ->whereIn('id', $studentIds)
                    ->update([
                        'school_period_id' => $destinationPeriod->id,
                    ]);
            }
        });
    }

    private function ensureDestinationHasRequiredEducationalStages(
        int $currentAcademicYearId,
        SchoolPeriod $destinationPeriod,
        array $gradeLevelIds,
    ): void {
        $requiredStages = GradeLevel::query()
            ->whereIn('id', $gradeLevelIds)
            ->pluck('educational_stage')
            ->unique()
            ->values();

        $existingStages = $destinationPeriod->educationalStages()
            ->pluck('stage');

        $missingStages = $requiredStages
            ->reject(fn ($stage) => $existingStages->contains($stage))
            ->map(fn ($stage): array => [
                'academic_year_id' => $currentAcademicYearId,
                'stage' => $stage,
            ])
            ->values()
            ->all();

        if ($missingStages === []) {
            return;
        }

        app(CreateEducationalStages::class)->execute($destinationPeriod, $missingStages);
    }
}

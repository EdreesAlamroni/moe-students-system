<?php

namespace App\Actions\Warehouse;

use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\SchoolPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DistributeBooksToGradeLevels
{
    /**
     * Confirm that books have been delivered to a school for the selected grade levels.
     *
     * Creates one distribution record per grade level for the current academic year.
     * Grade levels that have already been confirmed are skipped.
     *
     * @param  array<int, int>  $gradeLevelIds
     * @return int Number of grade levels confirmed as having received books.
     */
    public function execute(int $monitorId, int $schoolPeriodId, int $warehouseId, array $gradeLevelIds): int
    {
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            throw ValidationException::withMessages([
                '_' => [__('alerts.messages.academic-year-not-found')],
            ]);
        }

        return DB::transaction(function () use ($monitorId, $schoolPeriodId, $warehouseId, $gradeLevelIds, $currentAcademicYearId): int {
            $schoolPeriod = SchoolPeriod::query()
                ->where('education_monitor_id', '=', $monitorId)
                ->find($schoolPeriodId);

            if (is_null($schoolPeriod)) {
                return 0;
            }

            $eligibleGradeLevelIds = $schoolPeriod->gradeLevels()
                ->whereIn('grade_levels.id', $gradeLevelIds)
                ->pluck('grade_levels.id');

            if ($eligibleGradeLevelIds->isEmpty()) {
                return 0;
            }

            $alreadyDistributedIds = BookDistribution::query()
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->where('school_period_id', '=', $schoolPeriodId)
                ->whereIn('grade_level_id', $eligibleGradeLevelIds)
                ->pluck('grade_level_id');

            $pendingGradeLevelIds = $eligibleGradeLevelIds->diff($alreadyDistributedIds);

            if ($pendingGradeLevelIds->isEmpty()) {
                return 0;
            }

            $now = now();

            $rows = $pendingGradeLevelIds->map(function (int $gradeLevelId) use ($currentAcademicYearId, $monitorId, $schoolPeriodId, $warehouseId, $now): array {
                return [
                    'uuid' => Str::uuid()->toString(),
                    'academic_year_id' => $currentAcademicYearId,
                    'education_monitor_id' => $monitorId,
                    'school_period_id' => $schoolPeriodId,
                    'grade_level_id' => $gradeLevelId,
                    'warehouse_id' => $warehouseId,
                    'distributed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            BookDistribution::query()->insertOrIgnore($rows);

            return count($rows);
        });
    }
}

<?php

namespace App\Actions\Administration;

use App\Enums\SchoolAcademicPeriod;
use App\Models\ClassPeriod;

class ReorderClassPeriods
{
    public function execute(
        ClassPeriod $classPeriod,
        int $newOrder,
        SchoolAcademicPeriod $newAcademicPeriod,
        ?int $oldOrder = null,
        ?SchoolAcademicPeriod $oldAcademicPeriod = null
    ): void {
        if ($oldOrder === null || $oldAcademicPeriod === null) {
            $this->shiftOrdersUp($classPeriod->academic_year_id, $newAcademicPeriod, $newOrder, $classPeriod->id);

            return;
        }

        if ($oldAcademicPeriod !== $newAcademicPeriod) {
            $this->shiftOrdersDown($classPeriod->academic_year_id, $oldAcademicPeriod, $oldOrder, $classPeriod->id);
            $this->shiftOrdersUp($classPeriod->academic_year_id, $newAcademicPeriod, $newOrder, $classPeriod->id);

            return;
        }

        if ($oldOrder !== $newOrder) {
            if ($newOrder > $oldOrder) {
                $this->shiftOrdersDownInRange($classPeriod->academic_year_id, $newAcademicPeriod, $oldOrder, $newOrder, $classPeriod->id);
            } else {
                $this->shiftOrdersUpInRange($classPeriod->academic_year_id, $newAcademicPeriod, $newOrder, $oldOrder, $classPeriod->id);
            }
        }
    }

    private function shiftOrdersUp(int $academicYearId, SchoolAcademicPeriod $academicPeriod, int $targetOrder, int $excludePeriodId): void
    {
        ClassPeriod::query()
            ->forAcademicPeriod($academicPeriod)
            ->where('academic_year_id', '=', $academicYearId)
            ->where('order', '>=', $targetOrder)
            ->where('id', '!=', $excludePeriodId)
            ->increment('order');
    }

    private function shiftOrdersDown(int $academicYearId, SchoolAcademicPeriod $academicPeriod, int $targetOrder, int $excludePeriodId): void
    {
        ClassPeriod::query()
            ->forAcademicPeriod($academicPeriod)
            ->where('academic_year_id', '=', $academicYearId)
            ->where('order', '>', $targetOrder)
            ->where('id', '!=', $excludePeriodId)
            ->decrement('order');
    }

    private function shiftOrdersDownInRange(int $academicYearId, SchoolAcademicPeriod $academicPeriod, int $minOrder, int $maxOrder, int $excludePeriodId): void
    {
        ClassPeriod::query()
            ->forAcademicPeriod($academicPeriod)
            ->where('academic_year_id', '=', $academicYearId)
            ->where('order', '>', $minOrder)
            ->where('order', '<=', $maxOrder)
            ->where('id', '!=', $excludePeriodId)
            ->decrement('order');
    }

    private function shiftOrdersUpInRange(int $academicYearId, SchoolAcademicPeriod $academicPeriod, int $minOrder, int $maxOrder, int $excludePeriodId): void
    {
        ClassPeriod::query()
            ->forAcademicPeriod($academicPeriod)
            ->where('academic_year_id', '=', $academicYearId)
            ->where('order', '>=', $minOrder)
            ->where('order', '<', $maxOrder)
            ->where('id', '!=', $excludePeriodId)
            ->increment('order');
    }
}

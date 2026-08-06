<?php

namespace App\Actions\School;

use App\Models\School;
use App\Models\SchoolPeriod;
use Illuminate\Support\Facades\DB;

class DeleteSchool
{
    /**
     * Soft-delete a school and all of its periods.
     *
     * Historical records (enrollments, transfers, book distributions, classrooms,
     * schedules, educational stages, and grade-level assignments) are preserved.
     * Soft deletes do not trigger database foreign-key cascades.
     */
    public function execute(School $school): void
    {
        DB::transaction(function () use ($school): void {
            $school->periods->each(function (SchoolPeriod $schoolPeriod): void {
                $schoolPeriod->delete();
            });

            $school->delete();
        });
    }
}

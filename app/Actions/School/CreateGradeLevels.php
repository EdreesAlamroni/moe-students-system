<?php

namespace App\Actions\School;

use App\Models\SchoolPeriod;

class CreateGradeLevels
{
    /**
     * @param  array<int, array{academic_year_id: int}>  $gradeLevels  Grade level IDs keyed by ID, each with pivot attributes for attach.
     */
    public function execute(SchoolPeriod $schoolPeriod, array $gradeLevels): void
    {
        if ($gradeLevels === []) {
            return;
        }

        $schoolPeriod->gradeLevels()->attach($gradeLevels);
    }
}

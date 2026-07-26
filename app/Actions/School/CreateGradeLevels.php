<?php

namespace App\Actions\School;

use App\Models\School;

class CreateGradeLevels
{
    /**
     * @param  array<int, array{academic_year_id: int}>  $gradeLevels  Grade level IDs keyed by ID, each with pivot attributes for attach.
     */
    public function execute(School $school, array $gradeLevels): void
    {
        if ($gradeLevels === []) {
            return;
        }

        $school->gradeLevels()->attach($gradeLevels);
    }
}

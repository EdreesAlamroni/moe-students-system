<?php

namespace App\Actions\School;

use App\Models\AcademicYear;
use App\Models\School;

class CreateGradeLevels
{
    public function execute(School $school, array $gradeLevels): void
    {
        if ($gradeLevels === []) {
            return;
        }

        $school->gradeLevels()->attach($gradeLevels, [
            'academic_year_id' => AcademicYear::currentId(),
        ]);
    }
}

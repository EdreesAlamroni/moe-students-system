<?php

namespace App\Actions\School;

use App\Models\SchoolPeriod;

class CreateEducationalStages
{
    public function execute(SchoolPeriod $schoolPeriod, array $stages): void
    {
        $schoolPeriod->educationalStages()->createMany($stages);
    }
}

<?php

namespace App\Actions\Administration\School;

use App\Models\School;

class CreateEducationalStages
{
    public function execute(School $school, array $stages): void
    {
        $school->educationalStages()->createMany($stages);
    }
}

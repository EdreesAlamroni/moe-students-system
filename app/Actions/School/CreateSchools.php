<?php

namespace App\Actions\School;

use App\Models\School;
use Illuminate\Support\Collection;

class CreateSchools
{
    public function execute(array $attributes): Collection
    {
        $schoolPeriods = collect([]);

        foreach ($attributes as $schoolAttributes) {
            /** @var School $school */
            $school = School::create($schoolAttributes['school']);

            foreach ($schoolAttributes['periods'] as $academicPeriod => $periodAttributes) {
                $schoolPeriod = $school->periods()->create($periodAttributes);
                $schoolPeriods->put($academicPeriod, $schoolPeriod);
            }
        }

        return $schoolPeriods;
    }
}

<?php

namespace App\Actions\Administration\School;

use App\Models\School;
use Illuminate\Support\Collection;

class CreateSchools
{
    public function execute(array $schools): Collection
    {
        $created = collect([]);

        foreach ($schools as $academicPeriod => $attributes) {
            $created->put($academicPeriod, School::create($attributes));
        }

        return $created;
    }
}

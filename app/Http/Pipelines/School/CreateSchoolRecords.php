<?php

namespace App\Http\Pipelines\School;

use App\Actions\School\CreateSchools;
use App\Http\Requests\Shared\School\StoreSchoolRequest;
use Illuminate\Http\Request;

class CreateSchoolRecords
{
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var StoreSchoolRequest $request */
        $attributes = $request->getAttributes('school_aggregates');

        $schoolPeriods = app(CreateSchools::class)->execute($attributes);

        $request->merge([
            'moe' => [
                'school_periods' => $schoolPeriods,
            ],
        ]);

        return $next($request);
    }
}

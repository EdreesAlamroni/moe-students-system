<?php

namespace App\Http\Pipelines\School;

use App\Actions\Administration\School\CreateSchools;
use App\Http\Requests\Administration\School\StoreRequest as AdministrationStoreRequest;
use App\Http\Requests\EducationMonitor\School\StoreRequest as EducationMonitorStoreRequest;
use Illuminate\Http\Request;

class CreateSchoolRecords
{
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var AdministrationStoreRequest|EducationMonitorStoreRequest $request */
        $attributes = $request->getAttributes('schools');

        $schools = app(CreateSchools::class)->execute($attributes);

        $request->merge([
            'moe.schools' => $schools,
        ]);

        return $next($request);
    }
}

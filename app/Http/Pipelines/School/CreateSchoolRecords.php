<?php

namespace App\Http\Pipelines\School;

use App\Actions\School\CreateSchools;
use App\Http\Requests\Administration\School\StoreRequest as AdministrationStoreRequest;
use App\Http\Requests\EducationMonitor\School\StoreRequest as EducationMonitorStoreRequest;
use App\Http\Requests\EducationServicesOffice\School\StoreRequest as EducationServicesOfficeStoreRequest;
use Illuminate\Http\Request;

class CreateSchoolRecords
{
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var AdministrationStoreRequest|EducationMonitorStoreRequest|EducationServicesOfficeStoreRequest $request */
        $attributes = $request->getAttributes('schools');

        $schools = app(CreateSchools::class)->execute($attributes);

        $request->merge([
            'moe.schools' => $schools,
        ]);

        return $next($request);
    }
}

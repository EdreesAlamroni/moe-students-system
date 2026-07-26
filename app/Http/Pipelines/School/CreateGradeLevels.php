<?php

namespace App\Http\Pipelines\School;

use App\Actions\School\CreateGradeLevels as CreateGradeLevelsAction;
use App\Http\Requests\Administration\School\StoreRequest as AdministrationStoreRequest;
use App\Http\Requests\EducationMonitor\School\StoreRequest as EducationMonitorStoreRequest;
use App\Http\Requests\EducationServicesOffice\School\StoreRequest as EducationServicesOfficeStoreRequest;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CreateGradeLevels
{
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var AdministrationStoreRequest|EducationMonitorStoreRequest|EducationServicesOfficeStoreRequest $request */
        $attributes = $request->getAttributes('grade_levels');

        /** @var Collection<string, School> $schools */
        $schools = $request->input('moe.schools');

        $schools->each(function (School $school, string $academicPeriod) use ($attributes): void {
            app(CreateGradeLevelsAction::class)->execute($school, $attributes[$academicPeriod] ?? []);
        });

        return $next($request);
    }
}

<?php

namespace App\Http\Pipelines\School;

use App\Actions\School\CreateEducationalStages as CreateEducationalStagesAction;
use App\Http\Requests\Shared\School\StoreSchoolRequest;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CreateEducationalStages
{
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var StoreSchoolRequest $request */
        $attributes = $request->getAttributes('educational_stages');

        /** @var Collection<string, School> $schools */
        $schools = $request->input('moe.schools');

        $schools->each(function (School $school, string $academicPeriod) use ($attributes): void {
            app(CreateEducationalStagesAction::class)->execute($school, $attributes[$academicPeriod] ?? []);
        });

        return $next($request);
    }
}

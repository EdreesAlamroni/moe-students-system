<?php

namespace App\Http\Pipelines\School;

use App\Actions\School\CreateEducationalStages as CreateEducationalStagesAction;
use App\Http\Requests\Shared\School\StoreSchoolRequest;
use App\Models\SchoolPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CreateEducationalStages
{
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var StoreSchoolRequest $request */
        $attributes = $request->getAttributes('educational_stages');

        /** @var Collection<string, SchoolPeriod> $schoolPeriods */
        $schoolPeriods = $request->input('moe.school_periods');

        $schoolPeriods->each(function (SchoolPeriod $schoolPeriod, string $academicPeriod) use ($attributes): void {
            app(CreateEducationalStagesAction::class)->execute($schoolPeriod, $attributes[$academicPeriod] ?? []);
        });

        return $next($request);
    }
}

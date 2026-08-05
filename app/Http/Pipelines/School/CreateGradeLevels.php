<?php

namespace App\Http\Pipelines\School;

use App\Actions\School\CreateGradeLevels as CreateGradeLevelsAction;
use App\Http\Requests\Shared\School\StoreSchoolRequest;
use App\Models\SchoolPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CreateGradeLevels
{
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var StoreSchoolRequest $request */
        $attributes = $request->getAttributes('grade_levels');

        /** @var Collection<string, SchoolPeriod> $schoolPeriods */
        $schoolPeriods = $request->input('moe.school_periods');

        $schoolPeriods->each(function (SchoolPeriod $schoolPeriod, string $academicPeriod) use ($attributes): void {
            app(CreateGradeLevelsAction::class)->execute($schoolPeriod, $attributes[$academicPeriod] ?? []);
        });

        return $next($request);
    }
}

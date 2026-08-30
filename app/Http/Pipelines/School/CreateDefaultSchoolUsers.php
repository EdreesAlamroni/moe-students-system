<?php

namespace App\Http\Pipelines\School;

use App\Actions\User\CreateDefaultOrganizationUser;
use App\Http\Requests\Shared\School\StoreSchoolRequest;
use App\Models\SchoolPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CreateDefaultSchoolUsers
{
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var StoreSchoolRequest $request */
        /** @var Collection<string, SchoolPeriod> $schoolPeriods */
        $schoolPeriods = $request->input('moe.school_periods');

        $schoolPeriods->each(function (SchoolPeriod $period): void {
            app(CreateDefaultOrganizationUser::class)->forSchoolPeriod($period);
        });

        return $next($request);
    }
}

<?php

namespace App\Http\Controllers\School;

use App\Actions\School\TransferGradeLevels;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\GradeLevel\TransferRequest;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

class GradeLevelTransferController extends Controller
{
    public function store(TransferRequest $request): RedirectResponse
    {
        Gate::authorize('transfer', GradeLevel::class);

        /** @var SchoolPeriod $sourcePeriod */
        $sourcePeriod = auth('school')->user()->organization;
        $destinationPeriod = $sourcePeriod->siblingPeriod();

        $gradeLevelIds = $request->getAttributes();

        app(TransferGradeLevels::class)->execute($sourcePeriod, $destinationPeriod, $gradeLevelIds);

        flash_success('grade-levels-transferred', ['count' => count($gradeLevelIds)]);

        return Redirect::back();
    }
}

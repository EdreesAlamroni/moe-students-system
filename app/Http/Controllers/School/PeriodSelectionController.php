<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\PeriodSelectionRequest;
use App\Models\SchoolPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class PeriodSelectionController extends Controller
{
    public function __invoke(PeriodSelectionRequest $request): RedirectResponse
    {
        auth('school')->user()->update([
            'organization_id' => $request->validated('school_period_id'),
            'organization_type' => SchoolPeriod::class,
        ]);

        flash_success('period-selected');

        $previousUrl = URL::previous();

        $redirectPath = $previousUrl
            ? (parse_url($previousUrl, PHP_URL_PATH) ?: '/')
            : $request->path();

        return Redirect::to($redirectPath);
    }
}

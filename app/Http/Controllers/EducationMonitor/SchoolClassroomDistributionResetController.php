<?php

namespace App\Http\Controllers\EducationMonitor;

use App\Actions\EducationMonitor\ResetClassroomDistribution;
use App\Enums\ClassroomDistributionResetScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\EducationMonitor\School\ResetClassroomDistributionRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

class SchoolClassroomDistributionResetController extends Controller
{
    public function reset(ResetClassroomDistributionRequest $request, School $school): RedirectResponse
    {
        Gate::authorize('resetClassroomDistribution', $school);

        app(ResetClassroomDistribution::class)->execute(
            $school,
            $request->enum('scope', ClassroomDistributionResetScope::class),
            $request->validated('grade_level_ids', []),
        );

        flash_success('classroom-distribution-reset');

        return Redirect::route('education-monitor.schools.show', ['school' => $school]);
    }
}

<?php

namespace App\Http\Controllers\School;

use App\Authorization\School\ClassroomDistribution;
use App\Enums\ClassroomDistributionMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\ClassroomDistribution\DistributionMethodRequest;
use App\Models\AcademicYear;
use App\Models\ClassroomDistributionCompletion;
use App\Services\School\ClassroomDistribution\ClassroomDistributionMethodRegistry;
use App\Services\School\ClassroomDistribution\Shared\ClassroomDistributionHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ClassroomDistributionMethodController extends Controller
{
    public function create(Request $request, ClassroomDistributionMethod $method): RedirectResponse|Response
    {
        Gate::authorize('distribute', ClassroomDistribution::class);

        if (ClassroomDistributionCompletion::isCompleteForCurrentSchoolAndYear()) {
            flash_error('classroom-distribution-already-finalized');

            return Redirect::route('school.classroom-distribution.index');
        }

        if ($guardFailure = ClassroomDistributionHelper::resolveEnrollmentGuardFailure()) {
            flash_error($guardFailure);

            return Redirect::route('school.classroom-distribution.index');
        }

        $credentials = ClassroomDistributionMethodRegistry::getMethod($method)->credentials($request);

        $component = ClassroomDistributionMethodRegistry::getView($method);

        return Inertia::render($component, Arr::merge($credentials, [
            'method' => $method->toArray(),
            'can' => [
                'distribute' => Gate::allows('distribute', ClassroomDistribution::class),
            ],
        ]));
    }

    public function store(DistributionMethodRequest $request, ClassroomDistributionMethod $method): RedirectResponse
    {
        Gate::authorize('distribute', ClassroomDistribution::class);

        if (is_null(AcademicYear::currentId())) {
            flash_error('academic-year-not-found');

            return Redirect::to($method->route());
        }

        if (ClassroomDistributionCompletion::isCompleteForCurrentSchoolAndYear()) {
            flash_error('classroom-distribution-already-finalized');

            return Redirect::to($method->route());
        }

        if ($guardFailure = ClassroomDistributionHelper::resolveEnrollmentGuardFailure()) {
            flash_error($guardFailure);

            return Redirect::to($method->route());
        }

        ClassroomDistributionMethodRegistry::getMethod($method)->apply($request);

        flash_success('classroom-distribution-applied');

        return Redirect::to($method->route());
    }
}

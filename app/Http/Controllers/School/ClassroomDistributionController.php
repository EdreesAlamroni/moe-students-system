<?php

namespace App\Http\Controllers\School;

use App\Actions\School\FinalizeClassroomDistribution;
use App\Authorization\School\ClassroomDistribution;
use App\Enums\ClassroomDistributionMethod;
use App\Http\Controllers\Controller;
use App\Models\ClassroomDistributionCompletion;
use App\Services\School\ClassroomDistribution\Shared\ClassroomDistributionHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ClassroomDistributionController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('view', ClassroomDistribution::class);

        $methods = ClassroomDistributionMethod::buildMethods();

        $isDistributionCompleted = ClassroomDistributionCompletion::isCompleteForCurrentSchoolAndYear();

        $enrollmentSummary = ClassroomDistributionHelper::getEnrollmentSummaryForCurrentSchoolAndYear();

        $hasEligibleEnrollments = $enrollmentSummary['eligible_count'] > 0;

        return Inertia::render('school/classroom-distribution/index', [
            'methods' => $methods,
            'isDistributionCompleted' => $isDistributionCompleted,
            'enrollmentSummary' => [
                'totalCount' => $enrollmentSummary['total_count'],
                'eligibleCount' => $enrollmentSummary['eligible_count'],
                'withoutGradeLevelCount' => $enrollmentSummary['without_grade_level_count'],
                'withoutClassroomCount' => $enrollmentSummary['without_classroom_count'],
            ],
            'schoolWideUnassignedCount' => $enrollmentSummary['without_classroom_count'],
            'can' => [
                'distribute' => Gate::allows('distribute', ClassroomDistribution::class),
                'finalize' => Gate::allows('finalize', ClassroomDistribution::class) && $hasEligibleEnrollments,
            ],
        ]);
    }

    public function finalize(): RedirectResponse
    {
        Gate::authorize('finalize', ClassroomDistribution::class);

        try {
            app(FinalizeClassroomDistribution::class)->execute();
        } catch (ValidationException $exception) {
            flash_error($exception->getMessage());

            return Redirect::route('school.classroom-distribution.index');
        }

        flash_success('classroom-distribution-finalized');

        return Redirect::route('school.classroom-distribution.index');
    }
}

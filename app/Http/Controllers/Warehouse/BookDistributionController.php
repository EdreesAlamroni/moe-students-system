<?php

namespace App\Http\Controllers\Warehouse;

use App\Actions\Warehouse\DistributeBooksToGradeLevels;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BookDistribution\IndexRequest;
use App\Http\Requests\Warehouse\BookDistribution\StoreRequest;
use App\Models\BookDistribution;
use App\Services\Warehouse\BookDistributionGradeLevelStats;
use App\Services\Warehouse\BookDistributionOrganizationSelection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class BookDistributionController extends Controller
{
    public function index(IndexRequest $request): Response
    {
        Gate::authorize('view', BookDistribution::class);

        $organization = app(BookDistributionOrganizationSelection::class)->resolve($request->getAttributes());
        $schoolId = $organization['schoolId'];

        $gradeLevels = filled($schoolId)
            ? app(BookDistributionGradeLevelStats::class)->forDistribution($schoolId)
            : collect([]);

        return Inertia::render('warehouse/book-distributions/index', [
            'monitors' => $organization['monitors'],
            'schools' => $organization['schools'],
            'gradeLevels' => $gradeLevels,
            'selected' => $organization['selected'],
            'can' => [
                'distribute' => Gate::allows('distribute', BookDistribution::class),
            ],
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('distribute', BookDistribution::class);

        $attributes = $request->getAttributes();

        $count = app(DistributeBooksToGradeLevels::class)->execute(
            monitorId: $attributes['education_monitor_id'],
            schoolId: $attributes['school_id'],
            warehouseId: auth('warehouse')->user()->organization_id,
            gradeLevelIds: $attributes['grade_level_ids'],
        );

        if ($count === 0) {
            flash()->warning(__('alerts.messages.book-distribution-no-eligible-grade-levels'));
        } else {
            flash()->success(__('alerts.messages.book-distribution-grade-levels-confirmed', ['count' => $count]));
        }

        return Redirect::route('warehouse.book-distributions.index', [
            'education_monitor_id' => $attributes['education_monitor_id'],
            'school_id' => $attributes['school_id'],
        ]);
    }
}

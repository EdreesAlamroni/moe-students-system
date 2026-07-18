<?php

namespace App\Http\Controllers\Warehouse;

use App\Actions\Warehouse\DistributeBooksToGradeLevels;
use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BookDistribution\IndexRequest;
use App\Http\Requests\Warehouse\BookDistribution\StoreRequest;
use App\Models\BookDistribution;
use App\Models\EducationMonitor;
use App\Models\School;
use App\Services\Warehouse\BookDistributionGradeLevelStats;
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

        $selectedAttributes = $request->getAttributes();
        $monitorId = $selectedAttributes['education_monitor_id'];
        $schoolId = $selectedAttributes['school_id'];

        $monitors = EducationMonitor::list(function ($query): void {
            $query->forCurrentWarehouse();
        }, ['warehouse_id']);

        $schools = filled($monitorId)
            ? School::list(function ($query) use ($monitorId): void {
                $query->forCurrentWarehouse()->where('education_monitor_id', '=', $monitorId);
            }, ['education_monitor_id'])
            : collect([]);

        $gradeLevels = filled($schoolId)
            ? app(BookDistributionGradeLevelStats::class)->forDistribution($schoolId)
            : collect([]);

        return Inertia::render('warehouse/book-distributions/index', [
            'monitors' => $monitors,
            'schools' => $schools,
            'gradeLevels' => $gradeLevels,
            'selected' => $selectedAttributes,
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

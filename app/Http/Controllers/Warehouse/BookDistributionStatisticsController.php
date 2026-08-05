<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BookDistribution\IndexRequest;
use App\Models\BookDistribution;
use App\Services\Warehouse\BookDistributionGradeLevelStats;
use App\Services\Warehouse\BookDistributionOrganizationSelection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BookDistributionStatisticsController extends Controller
{
    public function index(IndexRequest $request): Response
    {
        Gate::authorize('viewStatistics', BookDistribution::class);

        $organization = app(BookDistributionOrganizationSelection::class)->resolve($request->getAttributes());
        $schoolPeriodId = $organization['schoolPeriodId'];

        $statistics = filled($schoolPeriodId)
            ? app(BookDistributionGradeLevelStats::class)->forSchoolPeriod($schoolPeriodId)
            : collect([]);

        return Inertia::render('warehouse/book-distributions/statistics', [
            'monitors' => $organization['monitors'],
            'schools' => $organization['schoolPeriods'],
            'statistics' => $statistics,
            'selected' => $organization['selected'],
        ]);
    }
}

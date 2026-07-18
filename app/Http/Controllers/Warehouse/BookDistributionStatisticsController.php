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
        $schoolId = $organization['schoolId'];

        $statistics = filled($schoolId)
            ? app(BookDistributionGradeLevelStats::class)->forSchool($schoolId)
            : collect([]);

        return Inertia::render('warehouse/book-distributions/statistics', [
            'monitors' => $organization['monitors'],
            'schools' => $organization['schools'],
            'statistics' => $statistics,
            'selected' => $organization['selected'],
        ]);
    }
}

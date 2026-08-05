<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BookDistribution\IndexRequest;
use App\Http\Requests\Warehouse\BookDistribution\PrintReportRequest;
use App\Models\BookDistribution;
use App\Models\SchoolPeriod;
use App\Services\Warehouse\BookDistributionGradeLevelStats;
use App\Services\Warehouse\BookDistributionOrganizationSelection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BookDistributionReportController extends Controller
{
    public function index(IndexRequest $request): Response
    {
        Gate::authorize('viewStatistics', BookDistribution::class);

        $organization = app(BookDistributionOrganizationSelection::class)->resolve($request->getAttributes());
        $schoolPeriodId = $organization['schoolPeriodId'];

        $statistics = filled($schoolPeriodId)
            ? app(BookDistributionGradeLevelStats::class)->forSchoolPeriod($schoolPeriodId)
            : collect([]);

        return Inertia::render('warehouse/book-distributions/report', [
            'monitors' => $organization['monitors'],
            'schools' => $organization['schoolPeriods'],
            'statistics' => $statistics,
            'selected' => $organization['selected'],
            'canPrint' => filled($schoolPeriodId) && $statistics->isNotEmpty(),
        ]);
    }

    public function print(PrintReportRequest $request): View
    {
        Gate::authorize('viewStatistics', BookDistribution::class);

        $schoolPeriodId = $request->integer('school_period_id');
        $statsService = app(BookDistributionGradeLevelStats::class);
        $statistics = $statsService->forSchoolPeriod($schoolPeriodId);

        $schoolPeriod = SchoolPeriod::query()
            ->select(['id', 'name', 'education_monitor_id'])
            ->with(['monitor:id,name'])
            ->findOrFail($schoolPeriodId);

        return view('print.warehouse.reports.book-distributions', [
            'statistics' => $statistics,
            'totals' => $statsService->totals($statistics),
            'schoolPeriod' => $schoolPeriod,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BookDistribution\IndexRequest;
use App\Http\Requests\Warehouse\BookDistribution\PrintReportRequest;
use App\Models\BookDistribution;
use App\Models\School;
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

        $organization = app(BookDistributionOrganizationSelection::class)->resolve($request);
        $schoolId = $organization['schoolId'];

        $statistics = filled($schoolId)
            ? app(BookDistributionGradeLevelStats::class)->forSchool($schoolId)
            : collect([]);

        return Inertia::render('warehouse/book-distributions/report', [
            'monitors' => $organization['monitors'],
            'schools' => $organization['schools'],
            'statistics' => $statistics,
            'selected' => $organization['selected'],
            'canPrint' => filled($schoolId) && $statistics->isNotEmpty(),
        ]);
    }

    public function print(PrintReportRequest $request): View
    {
        Gate::authorize('viewStatistics', BookDistribution::class);

        $schoolId = $request->integer('school_id');
        $statsService = app(BookDistributionGradeLevelStats::class);
        $statistics = $statsService->forSchool($schoolId);

        $school = School::query()
            ->select(['id', 'name', 'education_monitor_id'])
            ->with(['monitor:id,name'])
            ->findOrFail($schoolId);

        return view('print.warehouse.reports.book-distributions', [
            'statistics' => $statistics,
            'totals' => $statsService->totals($statistics),
            'school' => $school,
            'printedBy' => auth('warehouse')->user()->name,
        ]);
    }
}

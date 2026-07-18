<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\BookDistribution\IndexRequest;
use App\Models\BookDistribution;
use App\Models\EducationMonitor;
use App\Models\School;
use App\Services\Warehouse\BookDistributionGradeLevelStats;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BookDistributionStatisticsController extends Controller
{
    public function index(IndexRequest $request): Response
    {
        Gate::authorize('viewStatistics', BookDistribution::class);

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

        $statistics = filled($schoolId)
            ? app(BookDistributionGradeLevelStats::class)->forSchool($schoolId)
            : collect([]);

        return Inertia::render('warehouse/book-distributions/statistics', [
            'monitors' => $monitors,
            'schools' => $schools,
            'statistics' => $statistics,
            'selected' => $selectedAttributes,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\EducationServicesOfficeCollection;
use App\Http\Resources\Warehouse\EducationMonitorCollection;
use App\Http\Resources\Warehouse\EducationMonitorResource;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class EducationMonitorController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', EducationMonitor::class);

        $monitors = QueryBuilder::for(EducationMonitor::class)
            ->select([
                'id',
                'uuid',
                'name',
                'municipal_id',
                'warehouse_id',
                'created_at',
                'deleted_at',
            ])
            ->forCurrentWarehouse()
            ->withCount([
                'offices',
                'schools',
                'students',
            ])
            ->allowedFilters(
                'name',
            )
            ->ordered()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('warehouse/education-monitors/index', [
            'monitors' => ResourcePayloadBuilder::paginateWithAbilities(
                $monitors,
                EducationMonitorCollection::make($monitors),
                ['view'],
                $request,
            ),
            'filter' => $request->input('filter', []),
        ]);
    }

    public function show(Request $request, EducationMonitor $monitor): Response
    {
        Gate::authorize('view', $monitor);

        $monitor->load([
            'municipal:id,uuid,name',
        ])->loadCount([
            'offices',
            'schools',
            'students',
        ]);

        $offices = EducationServicesOffice::query()
            ->select([
                'id',
                'uuid',
                'education_monitor_id',
                'name',
                'created_at',
                'deleted_at',
            ])
            ->whereBelongsTo($monitor, 'monitor')
            ->ordered()
            ->paginate(pageName: 'offices')
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('warehouse/education-monitors/show', [
            'monitor' => ResourcePayloadBuilder::make(
                EducationMonitorResource::make($monitor),
            ),
            'offices' => ResourcePayloadBuilder::paginate(
                $offices,
                EducationServicesOfficeCollection::make($offices),
                $request,
            ),
        ]);
    }
}

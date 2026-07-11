<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\Warehouse\StoreRequest;
use App\Http\Requests\Administration\Warehouse\UpdateRequest;
use App\Http\Resources\Administration\EducationMonitorCollection;
use App\Http\Resources\Administration\WarehouseCollection;
use App\Http\Resources\Administration\WarehouseFormResource;
use App\Http\Resources\Administration\WarehouseResource;
use App\Models\EducationMonitor;
use App\Models\Warehouse;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class WarehouseController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Warehouse::class);

        $warehouses = QueryBuilder::for(Warehouse::class)
            ->select(['id', 'uuid', 'name', 'created_at', 'deleted_at'])
            ->withCount([
                'monitors',
                // 'schools', // TODO: Uncomment this when schools are implemented
            ])
            ->allowedFilters(
                'name',
            )
            ->ordered()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/warehouses/index', [
            'warehouses' => ResourcePayloadBuilder::paginateWithAbilities(
                $warehouses,
                WarehouseCollection::make($warehouses),
                ['view'],
            ),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(Warehouse::class, ['create']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Warehouse::class);

        $monitors = EducationMonitor::list(function (Builder $query) {
            $query->whereNull('warehouse_id');
        }, ['warehouse_id']);

        return Inertia::render('administration/warehouses/create', [
            'monitors' => $monitors,
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Warehouse::class);

        $warehouse = DB::transaction(function () use ($request): Warehouse {
            /** @var Warehouse $warehouse */
            $warehouse = Warehouse::create($request->getAttributes());

            $warehouse->syncEducationMonitors($request->validated('education_monitor_ids', []));

            return $warehouse->refresh();
        });

        flash_success('create');

        return Redirect::route('administration.warehouses.show', ['warehouse' => $warehouse]);
    }

    public function show(Request $request, Warehouse $warehouse): Response
    {
        Gate::authorize('view', $warehouse);

        $warehouse->load([
            'monitors',
        ])->loadCount([
            'monitors',
            // 'schools', // TODO: Uncomment this when schools are implemented
        ]);

        $monitors = $warehouse->monitors()
            ->select(['id', 'uuid', 'name', 'municipal_id', 'created_at', 'deleted_at'])
            ->withCount([
                'offices',
                // 'schools', // TODO: Uncomment this when schools are implemented
                // 'students', // TODO: Uncomment this when students are implemented
            ])
            ->paginate(pageName: 'monitors')
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/warehouses/show', [
            'warehouse' => ResourcePayloadBuilder::make(
                WarehouseResource::make($warehouse)
            ),
            'monitors' => ResourcePayloadBuilder::paginateWithAbilities(
                $monitors,
                EducationMonitorCollection::make($monitors),
                ['view'],
            ),
            ...ModelAbilityMap::make($warehouse, ['update', 'delete']),
        ]);
    }

    public function edit(Warehouse $warehouse): Response
    {
        Gate::authorize('update', $warehouse);

        $warehouse->load([
            'monitors',
        ]);

        $monitors = EducationMonitor::list(function (Builder $query) use ($warehouse) {
            $query->where(function (Builder $query) use ($warehouse) {
                $query->whereNull('warehouse_id')->orWhere('warehouse_id', '=', $warehouse->id);
            });
        }, ['warehouse_id']);

        return Inertia::render('administration/warehouses/edit', [
            'warehouse' => ResourcePayloadBuilder::make(
                WarehouseFormResource::make($warehouse)
            ),
            'monitors' => $monitors,
        ]);
    }

    public function update(UpdateRequest $request, Warehouse $warehouse): RedirectResponse
    {
        Gate::authorize('update', $warehouse);

        $warehouse = DB::transaction(function () use ($request, $warehouse): Warehouse {
            $warehouse->update($request->getAttributes());

            $warehouse->syncEducationMonitors($request->validated('education_monitor_ids', []));

            return $warehouse->refresh();
        });

        flash_success('update');

        return Redirect::route('administration.warehouses.show', ['warehouse' => $warehouse]);
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        Gate::authorize('delete', $warehouse);

        $warehouse->delete();

        flash_success('delete');

        return Redirect::route('administration.warehouses.index');
    }
}

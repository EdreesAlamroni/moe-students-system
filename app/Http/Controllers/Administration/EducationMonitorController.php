<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\EducationMonitor\StoreRequest;
use App\Http\Requests\Administration\EducationMonitor\UpdateRequest;
use App\Http\Resources\Administration\EducationMonitorCollection;
use App\Http\Resources\Administration\EducationMonitorFormResource;
use App\Http\Resources\Administration\EducationMonitorResource;
use App\Http\Resources\Administration\EducationServicesOfficeCollection;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\Municipal;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class EducationMonitorController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', EducationMonitor::class);

        $monitors = QueryBuilder::for(EducationMonitor::class)
            ->select(['id', 'uuid', 'name', 'municipal_id', 'created_at', 'deleted_at'])
            ->withCount([
                'offices',
                // 'schools', // TODO: Uncomment this when schools are implemented
                // 'students', // TODO: Uncomment this when students are implemented
            ])
            ->allowedFilters(
                'name',
            )
            ->ordered()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/education-monitors/index', [
            'monitors' => ResourcePayloadBuilder::paginateWithAbilities(
                $monitors,
                EducationMonitorCollection::make($monitors),
                ['view'],
            ),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(EducationMonitor::class, ['create']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', EducationMonitor::class);

        return Inertia::render('administration/education-monitors/create', [
            'municipals' => Municipal::listUnassigned(),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', EducationMonitor::class);

        $monitor = EducationMonitor::create($request->getAttributes());

        flash_success('create');

        return Redirect::route('administration.education-monitors.show', ['monitor' => $monitor]);
    }

    public function show(Request $request, EducationMonitor $monitor): Response
    {
        Gate::authorize('view', $monitor);

        $monitor->load([
            'municipal:id,uuid,name',
        ])->loadCount([
            'offices',
            // 'schools', // TODO: Uncomment this when schools are implemented
            // 'students', // TODO: Uncomment this when students are implemented
        ]);

        $offices = EducationServicesOffice::query()
            ->whereBelongsTo($monitor)
            ->select(['id', 'uuid', 'education_monitor_id', 'name', 'created_at', 'deleted_at'])
            ->ordered()
            ->paginate(pageName: 'offices')
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/education-monitors/show', [
            'monitor' => ResourcePayloadBuilder::make(
                EducationMonitorResource::make($monitor),
            ),
            'offices' => ResourcePayloadBuilder::paginateWithAbilities(
                $offices,
                EducationServicesOfficeCollection::make($offices),
                ['view'],
            ),
            ...ModelAbilityMap::make($monitor, ['update', 'delete']),
        ]);
    }

    public function edit(EducationMonitor $monitor): Response
    {
        Gate::authorize('update', $monitor);

        return Inertia::render('administration/education-monitors/edit', [
            'monitor' => ResourcePayloadBuilder::make(
                EducationMonitorFormResource::make($monitor),
            ),
            'municipals' => Municipal::listUnassigned($monitor->id),
        ]);
    }

    public function update(UpdateRequest $request, EducationMonitor $monitor): RedirectResponse
    {
        Gate::authorize('update', $monitor);

        $monitor->update($request->getAttributes());

        flash_success('update');

        return Redirect::route('administration.education-monitors.show', ['monitor' => $monitor]);
    }

    public function destroy(EducationMonitor $monitor): RedirectResponse
    {
        Gate::authorize('delete', $monitor);

        $monitor->delete();

        flash_success('delete');

        return Redirect::route('administration.education-monitors.index');
    }
}

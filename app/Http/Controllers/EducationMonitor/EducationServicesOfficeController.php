<?php

namespace App\Http\Controllers\EducationMonitor;

use App\Http\Controllers\Controller;
use App\Http\Requests\EducationMonitor\EducationServicesOffice\StoreRequest;
use App\Http\Requests\EducationMonitor\EducationServicesOffice\UpdateRequest;
use App\Http\Resources\EducationMonitor\EducationServicesOfficeCollection;
use App\Http\Resources\EducationMonitor\EducationServicesOfficeFormResource;
use App\Http\Resources\EducationMonitor\EducationServicesOfficeResource;
use App\Models\EducationServicesOffice;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EducationServicesOfficeController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', EducationServicesOffice::class);

        $offices = QueryBuilder::for(EducationServicesOffice::class)
            ->select([
                'id',
                'uuid',
                'education_monitor_id',
                'name',
                'created_at',
                'deleted_at',
            ])
            ->forCurrentEducationMonitor()
            ->allowedFilters(
                AllowedFilter::partial('name'),
            )
            ->ordered()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('education-monitor/education-services-offices/index', [
            'offices' => ResourcePayloadBuilder::paginateWithAbilities(
                $offices,
                EducationServicesOfficeCollection::make($offices),
                ['view'],
                $request,
            ),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(EducationServicesOffice::class, ['create']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', EducationServicesOffice::class);

        return Inertia::render('education-monitor/education-services-offices/create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', EducationServicesOffice::class);

        $office = EducationServicesOffice::create($request->getAttributes());

        flash_success('create');

        return Redirect::route('education-monitor.education-services-offices.show', ['office' => $office]);
    }

    public function show(EducationServicesOffice $office): Response
    {
        Gate::authorize('view', $office);

        $office->load([
            'monitor:id,uuid,name',
        ]);

        $office->loadCount([
            'schools',
            'students',
        ]);

        return Inertia::render('education-monitor/education-services-offices/show', [
            'office' => ResourcePayloadBuilder::make(
                EducationServicesOfficeResource::make($office),
            ),
            ...ModelAbilityMap::make($office, ['update', 'delete']),
        ]);
    }

    public function edit(EducationServicesOffice $office): Response
    {
        Gate::authorize('update', $office);

        return Inertia::render('education-monitor/education-services-offices/edit', [
            'office' => ResourcePayloadBuilder::make(
                EducationServicesOfficeFormResource::make($office),
            ),
        ]);
    }

    public function update(UpdateRequest $request, EducationServicesOffice $office): RedirectResponse
    {
        Gate::authorize('update', $office);

        $office->update($request->getAttributes());

        flash_success('update');

        return Redirect::route('education-monitor.education-services-offices.show', ['office' => $office]);
    }

    public function destroy(EducationServicesOffice $office): RedirectResponse
    {
        Gate::authorize('delete', $office);

        $office->delete();

        flash_success('delete');

        return Redirect::route('education-monitor.education-services-offices.index');
    }
}

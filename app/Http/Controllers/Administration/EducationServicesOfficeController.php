<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\EducationServicesOffice\StoreRequest;
use App\Http\Requests\Administration\EducationServicesOffice\UpdateRequest;
use App\Http\Resources\Administration\EducationServicesOfficeCollection;
use App\Http\Resources\Administration\EducationServicesOfficeFormResource;
use App\Http\Resources\Administration\EducationServicesOfficeResource;
use App\Models\EducationMonitor;
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
                'education_services_offices.id',
                'education_services_offices.uuid',
                'education_services_offices.education_monitor_id',
                'education_services_offices.name',
                'education_services_offices.created_at',
                'education_services_offices.deleted_at',
            ])
            ->with([
                'monitor:id,uuid,name',
            ])
            ->allowedFilters(
                AllowedFilter::exact('education_monitor_id'),
                AllowedFilter::partial('name', 'education_services_offices.name'),
            )
            ->orderedByMonitor()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/education-services-offices/index', [
            'offices' => ResourcePayloadBuilder::paginateWithAbilities(
                $offices,
                EducationServicesOfficeCollection::make($offices),
                ['view'],
            ),
            'monitors' => EducationMonitor::list(),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(EducationServicesOffice::class, ['create']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', EducationServicesOffice::class);

        return Inertia::render('administration/education-services-offices/create', [
            'monitors' => EducationMonitor::list(),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', EducationServicesOffice::class);

        $office = EducationServicesOffice::create($request->getAttributes());

        flash_success('create');

        return Redirect::route('administration.education-services-offices.show', ['office' => $office]);
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

        return Inertia::render('administration/education-services-offices/show', [
            'office' => ResourcePayloadBuilder::make(
                EducationServicesOfficeResource::make($office),
            ),
            ...ModelAbilityMap::make($office, ['update', 'delete']),
        ]);
    }

    public function edit(EducationServicesOffice $office): Response
    {
        Gate::authorize('update', $office);

        return Inertia::render('administration/education-services-offices/edit', [
            'office' => ResourcePayloadBuilder::make(
                EducationServicesOfficeFormResource::make($office),
            ),
            'monitors' => EducationMonitor::list(),
        ]);
    }

    public function update(UpdateRequest $request, EducationServicesOffice $office): RedirectResponse
    {
        Gate::authorize('update', $office);

        $office->update($request->getAttributes());

        flash_success('update');

        return Redirect::route('administration.education-services-offices.show', ['office' => $office]);
    }

    public function destroy(EducationServicesOffice $office): RedirectResponse
    {
        Gate::authorize('delete', $office);

        $office->delete();

        flash_success('delete');

        return Redirect::route('administration.education-services-offices.index');
    }
}

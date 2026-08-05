<?php

namespace App\Http\Controllers\Warehouse;

use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Warehouse\SchoolCollection;
use App\Http\Resources\Warehouse\SchoolResource;
use App\Models\EducationMonitor;
use App\Models\School;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SchoolController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', School::class);

        $schools = QueryBuilder::for(School::class)
            ->select([
                'schools.id',
                'schools.uuid',
                'schools.education_monitor_id',
                'schools.name',
                'schools.serial_number',
                'schools.type',
                'schools.created_at',
                'schools.deleted_at',
            ])
            ->forCurrentWarehouse()
            ->with([
                'monitor:id,uuid,name',
                'periods' => function ($query): void {
                    $query
                        ->select(['id', 'uuid', 'school_id', 'name', 'academic_period'])
                        ->withCount(['students']);
                },
            ])
            ->allowedFilters(
                AllowedFilter::exact('education_monitor_id'),
                AllowedFilter::exact('type'),
                AllowedFilter::partial('name', 'schools.name'),
            )
            ->orderedByMonitor()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        $monitors = EducationMonitor::list(function ($query) {
            return $query->forCurrentWarehouse();
        }, ['warehouse_id']);

        return Inertia::render('warehouse/schools/index', [
            'schools' => ResourcePayloadBuilder::paginateWithAbilities(
                $schools,
                SchoolCollection::make($schools),
                ['view'],
                $request,
            ),
            'monitors' => $monitors,
            'types' => SchoolType::optionsArray(),
            'filter' => $request->input('filter', []),
        ]);
    }

    public function show(School $school): Response
    {
        Gate::authorize('view', $school);

        $school->load([
            'monitor:id,uuid,name',
            'office:id,uuid,name',
            'periods' => function ($query): void {
                $query
                    ->with(['educationalStages'])
                    ->withCount(['gradeLevels', 'classrooms', 'students'])
                    ->orderedByAcademicPeriod();
            },
        ]);

        return Inertia::render('warehouse/schools/show', [
            'school' => ResourcePayloadBuilder::make(
                SchoolResource::make($school),
            ),
        ]);
    }
}

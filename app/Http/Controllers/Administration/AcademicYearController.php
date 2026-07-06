<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\AcademicYear\StoreRequest;
use App\Http\Resources\Administration\AcademicYearCollection;
use App\Http\Resources\Administration\AcademicYearResource;
use App\Models\AcademicYear;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AcademicYearController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', AcademicYear::class);

        $academicYears = QueryBuilder::for(AcademicYear::class)
            ->select([
                'id',
                'uuid',
                'name',
                'start_date',
                'end_date',
                'is_active',
                'created_at',
                'deleted_at',
            ])
            ->allowedFilters(
                AllowedFilter::partial('name'),
                AllowedFilter::exact('start_date'),
                AllowedFilter::exact('end_date'),
            )
            ->orderedByActiveFirst()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/academic-years/index', [
            'academicYears' => ResourcePayloadBuilder::paginateWithAbilities(
                $academicYears,
                AcademicYearCollection::make($academicYears),
                ['view'],
                $request,
            ),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(AcademicYear::class, ['create']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', AcademicYear::class);

        $defaults = AcademicYear::defaultsForCreateForm();

        return Inertia::render('administration/academic-years/create', [
            'name' => $defaults['name'],
            'minStartDate' => $defaults['min_start_date'],
            'maxEndDate' => $defaults['max_end_date'],
        ]);
    }

    public function store(StoreRequest $request)
    {
        $academicYear = AcademicYear::createNewYear($request->getAttributes());

        flash_success('create');

        return Redirect::route('administration.academic-years.show', ['academicYear' => $academicYear]);
    }

    public function show(AcademicYear $academicYear)
    {
        Gate::authorize('view', $academicYear);

        return Inertia::render('administration/academic-years/show', [
            'academicYear' => ResourcePayloadBuilder::make(
                AcademicYearResource::make($academicYear),
            ),
            ...ModelAbilityMap::make($academicYear, ['close']),
        ]);
    }

    public function close(AcademicYear $academicYear)
    {
        Gate::authorize('close', $academicYear);

        dd('WIP');

        // TODO: Implement the close method.
    }
}

<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Administration\ReorderClassPeriods;
use App\Enums\SchoolAcademicPeriod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\ClassPeriod\StoreRequest;
use App\Http\Requests\Administration\ClassPeriod\UpdateRequest;
use App\Http\Resources\Administration\ClassPeriodCollection;
use App\Http\Resources\Administration\ClassPeriodFormResource;
use App\Http\Resources\Administration\ClassPeriodResource;
use App\Models\ClassPeriod;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ClassPeriodController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', ClassPeriod::class);

        $classPeriods = QueryBuilder::for(ClassPeriod::class)
            ->select([
                'id',
                'uuid',
                'academic_year_id',
                'academic_period',
                'name',
                'start_time',
                'end_time',
                'order',
                'is_break',
                'created_at',
                'deleted_at',
            ])
            ->forCurrentAcademicYear()
            ->withCount(['schedules'])
            ->allowedFilters(
                'name',
                AllowedFilter::exact('academic_period'),
            )
            ->ordered()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/class-periods/index', [
            'classPeriods' => ResourcePayloadBuilder::paginateWithAbilities(
                $classPeriods,
                ClassPeriodCollection::make($classPeriods),
                ['view'],
                $request,
            ),
            'academicPeriods' => SchoolAcademicPeriod::getPrimaryPeriods(),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(ClassPeriod::class, ['create']),
        ]);
    }

    public function create(SchoolAcademicPeriod $academicPeriod): Response
    {
        Gate::authorize('create', ClassPeriod::class);

        $nextOrder = ClassPeriod::getNextOrder($academicPeriod);

        return Inertia::render('administration/class-periods/create', [
            'academicPeriod' => $academicPeriod->toArray(),
            'nextOrder' => $nextOrder,
        ]);
    }

    public function store(StoreRequest $request)
    {
        Gate::authorize('create', ClassPeriod::class);

        $classPeriod = DB::transaction(function () use ($request) {
            [$uniqueAttributes, $fillAttributes] = $request->getAttributes();

            $classPeriod = ClassPeriod::create([
                ...$uniqueAttributes,
                ...$fillAttributes,
            ]);

            $newOrder = $request->integer('order');
            $newAcademicPeriod = $request->enum('academic_period', SchoolAcademicPeriod::class);

            app(ReorderClassPeriods::class)->execute(
                $classPeriod,
                $newOrder,
                $newAcademicPeriod
            );

            return $classPeriod;
        });

        flash_success('create');

        return Redirect::route('administration.class-periods.show', ['classPeriod' => $classPeriod]);
    }

    public function show(ClassPeriod $classPeriod)
    {
        Gate::authorize('view', $classPeriod);

        $classPeriod->loadCount(['schedules']);

        return Inertia::render('administration/class-periods/show', [
            'classPeriod' => ResourcePayloadBuilder::make(
                ClassPeriodResource::make($classPeriod)
            ),
            ...ModelAbilityMap::make($classPeriod, ['update', 'delete']),
        ]);
    }

    public function edit(ClassPeriod $classPeriod): Response
    {
        Gate::authorize('update', $classPeriod);

        return Inertia::render('administration/class-periods/edit', [
            'classPeriod' => ResourcePayloadBuilder::make(
                ClassPeriodFormResource::make($classPeriod)
            ),
            'academicPeriods' => SchoolAcademicPeriod::getPrimaryPeriods(),
        ]);
    }

    public function update(UpdateRequest $request, ClassPeriod $classPeriod): RedirectResponse
    {
        Gate::authorize('update', $classPeriod);

        $classPeriod = DB::transaction(function () use ($request, $classPeriod) {
            $oldOrder = $classPeriod->order;
            $oldAcademicPeriod = $classPeriod->academic_period;
            $newOrder = $request->integer('order');
            $newAcademicPeriod = $request->enum('academic_period', SchoolAcademicPeriod::class);

            $classPeriod->update($request->getAttributes());

            app(ReorderClassPeriods::class)->execute(
                $classPeriod,
                $newOrder,
                $newAcademicPeriod,
                $oldOrder,
                $oldAcademicPeriod
            );

            return $classPeriod->refresh();
        });

        flash_success('update');

        return Redirect::route('administration.class-periods.show', ['classPeriod' => $classPeriod]);
    }

    public function destroy(ClassPeriod $classPeriod): RedirectResponse
    {
        Gate::authorize('delete', $classPeriod);

        $classPeriod->delete();

        flash_success('delete');

        return Redirect::route('administration.class-periods.index');
    }
}

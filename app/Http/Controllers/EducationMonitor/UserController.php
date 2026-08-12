<?php

namespace App\Http\Controllers\EducationMonitor;

use App\Enums\UserScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\EducationMonitor\User\StoreRequest;
use App\Http\Requests\EducationMonitor\User\UpdateRequest;
use App\Http\Resources\EducationMonitor\UserCollection;
use App\Http\Resources\Shared\UserFormResource;
use App\Http\Resources\Shared\UserResource;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $users = QueryBuilder::for(User::class)
            ->select([
                'id',
                'uuid',
                'organization_id',
                'organization_type',
                'name',
                'username',
                'scope',
                'role',
                'created_at',
                'deleted_at',
            ])
            ->forCurrentEducationMonitor()
            ->with([
                'organization' => function (Relation $morphTo): void {
                    assert($morphTo instanceof MorphTo);

                    $morphTo->constrain([
                        EducationServicesOffice::class => function (Builder $query): void {
                            $query->select(['id', 'education_monitor_id']);
                        },
                        SchoolPeriod::class => function (Builder $query): void {
                            $query->select(['id', 'education_monitor_id']);
                        },
                        EducationMonitor::class => function (Builder $query): void {
                            $query->select(['id']);
                        },
                    ]);
                },
            ])
            ->allowedFilters(
                'name',
                'username',
                'scope',
            )
            ->orderedByScope()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('education-monitor/users/index', [
            'users' => ResourcePayloadBuilder::paginateWithAbilities(
                $users,
                UserCollection::make($users),
                ['view'],
                $request,
            ),
            'filter' => $request->input('filter', []),
            'scopes' => UserScope::getCreationMenuItems(),
            ...ModelAbilityMap::make(User::class, ['create']),
        ]);
    }

    public function create(UserScope $scope): Response
    {
        Gate::authorize('create', [User::class, $scope]);

        /** @var EducationMonitor $monitor */
        $monitor = auth('education_monitor')->user()->organization;

        $offices = $scope->isEducationServicesOffice()
            ? EducationServicesOffice::list(function ($query) {
                $query->forCurrentEducationMonitor();
            }, ['education_monitor_id'])
            : collect([]);

        $schools = $scope->isSchool()
            ? School::listWithPeriods(function ($query): void {
                $query->forCurrentEducationMonitor();
            }, ['education_monitor_id'])
            : collect([]);

        return Inertia::render('education-monitor/users/create', [
            'scope' => $scope->toArray(),
            'creationLabel' => $scope->getCreationLabel(),
            'monitor' => $monitor->only(['id', 'name']),
            'offices' => $offices,
            'schools' => $schools,
            'groupedRoles' => get_grouped_roles($scope),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $user = DB::transaction(function () use ($request): User {
            /** @var User $user */
            $user = User::create($request->getAttributes());

            if ($request->enum('scope', UserScope::class) === UserScope::SCHOOL) {
                $user->syncSchoolPeriodMemberships($request->validatedSchoolPeriodIds());
            }

            $user->assignRole($request->validated('roles', []));

            return $user;
        });

        flash_success('create');

        return Redirect::route('education-monitor.users.show', ['user' => $user]);
    }

    public function show(User $user): Response
    {
        Gate::authorize('view', $user);

        $user->loadMissing('roles:id,name');

        return Inertia::render('education-monitor/users/show', [
            'user' => ResourcePayloadBuilder::make(
                UserResource::make($user),
            ),
            'roles' => $user->roles->isNotEmpty()
                ? get_grouped_roles($user->scope, $user->roles->pluck('id'))
                : [],
            'availableStates' => $user->getTransitionableStates(),
            ...ModelAbilityMap::make($user, ['update', 'delete', 'stateUpdate']),
        ]);
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        $user->loadMissing([
            'roles:id,name',
            'schoolPeriods:id,school_id,academic_period,name',
        ]);

        return Inertia::render('education-monitor/users/edit', [
            'user' => ResourcePayloadBuilder::make(
                UserFormResource::make($user),
            ),
            'schools' => $user->scope === UserScope::SCHOOL
                ? School::listWithPeriods(function ($query): void {
                    $query->forCurrentEducationMonitor();
                }, ['education_monitor_id'])
                : collect([]),
            'groupedRoles' => get_grouped_roles($user->scope),
        ]);
    }

    public function update(UpdateRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        DB::transaction(function () use ($request, $user): void {
            $user->update($request->getAttributes());

            if ($user->scope === UserScope::SCHOOL) {
                $user->syncSchoolPeriodMemberships($request->validatedSchoolPeriodIdsForUser($user));
                $user->ensureActiveSchoolPeriodIsValid();
            }

            $user->syncRoles($request->validated('roles', []));
        });

        flash_success('update');

        return Redirect::route('education-monitor.users.show', ['user' => $user]);
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        flash_success('delete');

        return Redirect::route('education-monitor.users.index');
    }
}

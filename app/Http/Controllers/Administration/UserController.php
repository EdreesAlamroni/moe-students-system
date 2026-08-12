<?php

namespace App\Http\Controllers\Administration;

use App\Enums\UserScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\User\StoreRequest;
use App\Http\Requests\Administration\User\UpdateRequest;
use App\Http\Resources\Administration\UserCollection;
use App\Http\Resources\Administration\UserFormResource;
use App\Http\Resources\Administration\UserResource;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
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
            ->allowedFilters(
                'name',
                'username',
                'scope',
            )
            ->orderedByScope()
            ->orderBy('name')
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/users/index', [
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
        Gate::authorize('create', User::class);

        return Inertia::render('administration/users/create', [
            'scope' => $scope->toArray(),
            'creationLabel' => $scope->getCreationLabel(),
            'warehouses' => $scope->isWarehouse() ? Warehouse::list() : [],
            'monitors' => match ($scope) {
                UserScope::EDUCATION_MONITOR => EducationMonitor::list(),
                UserScope::EDUCATION_SERVICES_OFFICE => EducationMonitor::listWithOffices(),
                UserScope::SCHOOL => EducationMonitor::listWithSchools(),
                default => [],
            },
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

        return Redirect::route('administration.users.show', ['user' => $user]);
    }

    public function show(User $user): Response
    {
        Gate::authorize('view', $user);

        $this->loadOrganizationRelation($user);
        $user->loadMissing([
            'roles:id,name',
            'schoolPeriods:id,school_id,academic_period,name',
        ]);

        return Inertia::render('administration/users/show', [
            'user' => ResourcePayloadBuilder::make(
                UserResource::make($user),
            ),
            'roles' => $user->roles->isNotEmpty()
                ? get_grouped_roles($user->scope, $user->roles->pluck('id'))
                : [],
            'availableStates' => $user->getTransitionableStates(),
            'availableRequestStates' => $user->getTransitionableStates('request_state'),
            ...ModelAbilityMap::make($user, ['update', 'delete', 'stateUpdate', 'updatePassword']),
        ]);
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        $this->loadOrganizationRelation($user);
        $user->loadMissing([
            'roles:id,name',
            'schoolPeriods:id,school_id,academic_period,name',
        ]);

        return Inertia::render('administration/users/edit', [
            'user' => ResourcePayloadBuilder::make(
                UserFormResource::make($user),
            ),
            'monitors' => $user->scope === UserScope::SCHOOL
                ? EducationMonitor::listWithSchools()
                : [],
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

        return Redirect::route('administration.users.show', ['user' => $user]);
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        flash_success('delete');

        return Redirect::route('administration.users.index');
    }

    protected function loadOrganizationRelation(User $user): void
    {
        if ($user->organization_type === null) {
            return;
        }

        $user->loadMissing(match ($user->organization_type) {
            EducationServicesOffice::class, SchoolPeriod::class => ['organization.monitor'],
            default => ['organization'],
        });
    }
}

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
use App\Models\School;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = QueryBuilder::for(User::class)
            ->select([
                'id',
                'uuid',
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
            ->oldest()
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
            'groupedRoles' => $this->getGroupedRoles($scope),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $user = DB::transaction(function () use ($request): User {
            $user = User::create($request->getAttributes());

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
        $user->loadMissing('roles:id,name');

        return Inertia::render('administration/users/show', [
            'user' => ResourcePayloadBuilder::make(
                UserResource::make($user),
            ),
            'roles' => $user->roles->isNotEmpty()
                ? $this->getGroupedRoles($user->scope, $user->roles->pluck('id'))
                : [],
            'availableStates' => $user->getTransitionableStates(),
            'availableRequestStates' => $user->getTransitionableStates('request_state'),
            'isRequestPending' => $user->requestIsPending(),
            ...ModelAbilityMap::make($user, ['update', 'delete', 'stateUpdate']),
        ]);
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        $this->loadOrganizationRelation($user);
        $user->loadMissing('roles:id,name');

        return Inertia::render('administration/users/edit', [
            'user' => ResourcePayloadBuilder::make(
                UserFormResource::make($user),
            ),
            'groupedRoles' => $this->getGroupedRoles($user->scope),
        ]);
    }

    public function update(UpdateRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        DB::transaction(function () use ($request, $user): void {
            $user->update($request->getAttributes());

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
        if ($user->model_type === null) {
            return;
        }

        $user->loadMissing(match ($user->model_type) {
            EducationServicesOffice::class, School::class => ['model.monitor'],
            default => ['model'],
        });
    }

    protected function getGroupedRoles(UserScope|string $scope, Collection|array $ids = []): Collection
    {
        $ids = $ids instanceof Collection ? $ids : collect($ids);

        $scope = $scope instanceof UserScope ? $scope->value : $scope;

        return Role::query()
            ->select(['id', 'name', 'guard_name'])
            ->where('guard_name', '=', $scope)
            ->when($ids->isNotEmpty(), function (Builder $query) use ($ids) {
                $query->whereIn('id', $ids->all());
            })
            ->oldest()
            ->get()
            ->groupBy(function (Role $role): string {
                return Str::before($role->name, ':');
            })->mapWithKeys(function (Collection $roles, string $group): array {
                return [
                    $group => [
                        'label' => __("roles.{$group}.label"),
                        'roles' => $roles->map(function (Role $role) use ($group): array {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                                'label' => __("roles.{$group}.values.{$role->name}"),
                            ];
                        })->values(),
                    ],
                ];
            })->values();
    }
}

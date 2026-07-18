<?php

namespace App\Http\Controllers\EducationServicesOffice;

use App\Enums\UserScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\EducationServicesOffice\User\StoreRequest;
use App\Http\Requests\EducationServicesOffice\User\UpdateRequest;
use App\Http\Resources\EducationServicesOffice\UserCollection;
use App\Http\Resources\EducationServicesOffice\UserFormResource;
use App\Http\Resources\EducationServicesOffice\UserResource;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\User;
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
            ->forCurrentEducationServicesOffice()
            ->allowedFilters(
                'name',
                'username',
                'scope',
            )
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('education-services-office/users/index', [
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

        /** @var EducationServicesOffice $office */
        $office = auth('education_services_office')->user()->organization;

        $schools = $scope->isSchool()
            ? School::list(function (Builder $query) use ($office) {
                $query->where('education_services_office_id', '=', $office->id);
            }, ['education_services_office_id'])
            : [];

        return Inertia::render('education-services-office/users/create', [
            'scope' => $scope->toArray(),
            'creationLabel' => $scope->getCreationLabel(),
            'office' => $office->only(['id', 'name']),
            'schools' => $schools,
            'groupedRoles' => $this->getGroupedRoles($scope),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $user = DB::transaction(function () use ($request): User {
            /** @var User $user */
            $user = User::create($request->getAttributes());

            $user->assignRole($request->validated('roles', []));

            return $user;
        });

        flash_success('create');

        return Redirect::route('education-services-office.users.show', ['user' => $user]);
    }

    public function show(User $user): Response
    {
        Gate::authorize('view', $user);

        $this->loadOrganizationRelation($user);
        $user->loadMissing('roles:id,name');

        return Inertia::render('education-services-office/users/show', [
            'user' => ResourcePayloadBuilder::make(
                UserResource::make($user),
            ),
            'roles' => $user->roles->isNotEmpty()
                ? $this->getGroupedRoles($user->scope, $user->roles->pluck('id'))
                : [],
            'availableStates' => $user->getTransitionableStates(),
            ...ModelAbilityMap::make($user, ['update', 'delete', 'stateUpdate']),
        ]);
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        $this->loadOrganizationRelation($user);
        $user->loadMissing('roles:id,name');

        return Inertia::render('education-services-office/users/edit', [
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

        return Redirect::route('education-services-office.users.show', ['user' => $user]);
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        flash_success('delete');

        return Redirect::route('education-services-office.users.index');
    }

    protected function loadOrganizationRelation(User $user): void
    {
        if ($user->organization_type === null) {
            return;
        }

        $user->loadMissing(match ($user->organization_type) {
            EducationServicesOffice::class, School::class => ['organization.monitor'],
            default => ['organization'],
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

<?php

namespace App\Http\Controllers\School;

use App\Enums\UserScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\User\StoreRequest;
use App\Http\Requests\School\User\UpdateRequest;
use App\Http\Resources\School\UserCollection;
use App\Http\Resources\School\UserFormResource;
use App\Http\Resources\School\UserResource;
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
            ->forCurrentSchool()
            ->allowedFilters(
                'name',
                'username',
            )
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('school/users/index', [
            'users' => ResourcePayloadBuilder::paginateWithAbilities(
                $users,
                UserCollection::make($users),
                ['view'],
                $request,
            ),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(User::class, ['create']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        /** @var School $school */
        $school = auth('school')->user()->organization;

        return Inertia::render('school/users/create', [
            'scope' => UserScope::SCHOOL->toArray(),
            'school' => $school->only(['id', 'name']),
            'groupedRoles' => $this->getGroupedRoles(),
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

        return Redirect::route('school.users.show', ['user' => $user]);
    }

    public function show(User $user): Response
    {
        Gate::authorize('view', $user);

        $user->loadMissing(['organization', 'roles:id,name']);

        return Inertia::render('school/users/show', [
            'user' => ResourcePayloadBuilder::make(
                UserResource::make($user),
            ),
            'roles' => $user->roles->isNotEmpty()
                ? $this->getGroupedRoles($user->roles->pluck('id'))
                : [],
            'availableStates' => $user->getTransitionableStates(),
            ...ModelAbilityMap::make($user, ['update', 'delete', 'stateUpdate']),
        ]);
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        $user->loadMissing(['organization', 'roles:id,name']);

        return Inertia::render('school/users/edit', [
            'user' => ResourcePayloadBuilder::make(
                UserFormResource::make($user),
            ),
            'groupedRoles' => $this->getGroupedRoles(),
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

        return Redirect::route('school.users.show', ['user' => $user]);
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        flash_success('delete');

        return Redirect::route('school.users.index');
    }

    protected function getGroupedRoles(Collection|array $ids = []): Collection
    {
        $ids = $ids instanceof Collection ? $ids : collect($ids);

        return Role::query()
            ->select(['id', 'name', 'guard_name'])
            ->where('guard_name', '=', UserScope::SCHOOL->value)
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

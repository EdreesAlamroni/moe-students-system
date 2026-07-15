<?php

namespace App\Http\Controllers\Administration;

use App\Enums\UserScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\User\StoreRequest;
use App\Http\Requests\Administration\User\UpdateRequest;
use App\Http\Resources\Administration\UserCollection;
use App\Models\User;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = QueryBuilder::for(User::class)
            ->select(['id', 'uuid', 'name', 'username', 'scope', 'role', 'created_at', 'deleted_at'])
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

    public function create(UserScope $scope)
    {
        //
    }

    public function store(StoreRequest $request)
    {
        //
    }

    public function show(User $user)
    {
        //
    }

    public function edit(User $user)
    {
        //
    }

    public function update(UpdateRequest $request, User $user)
    {
        //
    }

    public function destroy(User $user)
    {
        //
    }
}

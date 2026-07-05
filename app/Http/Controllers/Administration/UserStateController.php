<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\User\UpdateRequestStateRequest;
use App\Http\Requests\Administration\User\UpdateStateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

class UserStateController extends Controller
{
    public function stateUpdate(UpdateStateRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('stateUpdate', $user);

        $user->state->transitionTo($request->validated('state'));

        flash_success('state-update');

        return Redirect::route('administration.users.show', ['user' => $user]);
    }

    public function requestStateUpdate(UpdateRequestStateRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('stateUpdate', $user);

        $user->request_state->transitionTo($request->validated('request_state'));

        flash_success('state-update');

        return Redirect::route('administration.users.show', ['user' => $user]);
    }
}

<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\User\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

class UserPasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('updatePassword', $user);

        $user->update([
            'password' => $request->validated('password'),
            'must_change_password' => true,
        ]);

        flash_success('update-password');

        return Redirect::route('administration.users.show', ['user' => $user]);
    }
}

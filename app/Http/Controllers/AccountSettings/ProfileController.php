<?php

namespace App\Http\Controllers\AccountSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountSettings\ProfileUpdateRequest;
use App\Support\Auth\DashboardAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request, DashboardAuth $dashboard): Response
    {
        return Inertia::render('account-settings/profile', [
            'routes' => [
                'update' => $dashboard->url('account-settings.profile.update'),
            ],
        ]);
    }

    public function update(ProfileUpdateRequest $request, DashboardAuth $dashboard): RedirectResponse
    {
        $user = $request->user($dashboard->guard);
        $user->fill($request->validated());
        $user->save();

        flash_success('update-profile');

        return Redirect::to($dashboard->url('account-settings.profile.edit'));
    }
}

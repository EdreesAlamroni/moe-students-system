<?php

namespace App\Http\Controllers\AccountSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountSettings\PasswordUpdateRequest;
use App\Support\Auth\DashboardAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    public function edit(Request $request, DashboardAuth $dashboard): Response
    {
        return Inertia::render('account-settings/security', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'routes' => [
                'update' => $dashboard->url('account-settings.password.update'),
            ],
        ]);
    }

    public function update(PasswordUpdateRequest $request, DashboardAuth $dashboard): RedirectResponse
    {
        $request->user($dashboard->guard)->update([
            'password' => $request->validated('password'),
        ]);

        flash_success('password-updated');

        return Redirect::back();
    }
}

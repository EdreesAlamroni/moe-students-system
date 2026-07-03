<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ChangeUserPassword;
use App\Enums\AuthPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Support\Auth\DashboardAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ChangePasswordController extends Controller
{
    public function create(Request $request, DashboardAuth $dashboard): Response
    {
        $props = $dashboard->inertiaProps(AuthPage::CHANGE_PASSWORD, [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);

        return Inertia::render('auth/change-password', $props);
    }

    public function store(ChangePasswordRequest $request, ChangeUserPassword $action, DashboardAuth $dashboard): RedirectResponse
    {
        $user = $request->user($dashboard->guard);

        $action->execute($user, $request->validated('password'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password updated.')]);

        return Redirect::route($dashboard->dashboardRouteName());
    }
}

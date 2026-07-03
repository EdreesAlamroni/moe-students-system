<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetUserPassword;
use App\Enums\AuthPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Support\Auth\DashboardAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token, DashboardAuth $dashboard): Response
    {
        $props = $dashboard->inertiaProps(AuthPage::RESET_PASSWORD, [
            'token' => $token,
            'email' => $request->string('email')->value(),
            'passwordRules' => PasswordRule::defaults()->toPasswordRulesString(),
        ]);

        return Inertia::render('auth/reset-password', $props);
    }

    public function store(ResetPasswordRequest $request, ResetUserPassword $action, DashboardAuth $dashboard): RedirectResponse
    {
        $status = $action->execute($dashboard, $request->validated());

        return $status === Password::PASSWORD_RESET
            ? Redirect::route($dashboard->loginRouteName())->with('status', __($status))
            : Redirect::back()->withErrors(['email' => __($status)]);
    }
}

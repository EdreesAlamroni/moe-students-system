<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ConfirmUserPassword;
use App\Enums\AuthPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use App\Support\Auth\DashboardAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    public function show(Request $request, DashboardAuth $dashboard): Response
    {
        $props = $dashboard->inertiaProps(AuthPage::CONFIRM_PASSWORD);

        return Inertia::render('auth/confirm-password', $props);
    }

    public function store(ConfirmPasswordRequest $request, ConfirmUserPassword $action, DashboardAuth $dashboard): RedirectResponse
    {
        $user = $request->user($dashboard->guard);

        $action->execute($dashboard, $user, $request->validated('password'));

        $request->session()->put('auth.password_confirmed_at', time());

        return Redirect::intended(route($dashboard->dashboardRouteName()));
    }
}

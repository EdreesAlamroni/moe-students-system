<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Actions\Auth\DestroyAuthenticatedSession;
use App\Enums\AuthPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\Auth\DashboardAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request, DashboardAuth $dashboard): Response
    {
        $props = $dashboard->inertiaProps(AuthPage::LOGIN, [
            'status' => $request->session()->get('status'),
        ]);

        return Inertia::render('auth/login', $props);
    }

    public function store(LoginRequest $request, AuthenticateUser $action, DashboardAuth $dashboard): RedirectResponse
    {
        $action->execute(
            $dashboard,
            $request->validated('username'),
            $request->validated('password'),
            $request->boolean('remember'),
            $request->ip(),
        );

        $request->session()->regenerate();

        $user = $request->user($dashboard->guard);

        if ($user?->must_change_password) {
            return Redirect::to($dashboard->url('password.change'));
        }

        return Redirect::intended($dashboard->url('dashboard'));
    }

    public function destroy(Request $request, DestroyAuthenticatedSession $action, DashboardAuth $dashboard): HttpResponse
    {
        return $action->execute($dashboard, $request);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use App\Enums\AuthPage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetLinkRequest;
use App\Support\Auth\DashboardAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    public function create(Request $request, DashboardAuth $dashboard): Response
    {
        $props = $dashboard->inertiaProps(AuthPage::FORGOT_PASSWORD, ['status' => $request->session()->get('status')]);

        return Inertia::render('auth/forgot-password', $props);
    }

    public function store(PasswordResetLinkRequest $request, SendPasswordResetLink $action, DashboardAuth $dashboard): RedirectResponse
    {
        $status = $action->execute($dashboard, $request->validated('email'));

        return $status === Password::RESET_LINK_SENT
            ? Redirect::back()->with('status', __($status))
            : Redirect::back()->withErrors(['email' => __($status)]);
    }
}

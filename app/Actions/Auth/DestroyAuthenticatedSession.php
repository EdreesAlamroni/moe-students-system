<?php

namespace App\Actions\Auth;

use App\Support\Auth\DashboardAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class DestroyAuthenticatedSession
{
    public function execute(DashboardAuth $dashboard, Request $request): Response
    {
        Auth::guard($dashboard->guard)->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Inertia::clearHistory();

        $response = Redirect::to(route('welcome', absolute: false));

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}

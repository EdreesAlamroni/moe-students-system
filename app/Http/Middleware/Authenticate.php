<?php

namespace App\Http\Middleware;

use App\Support\Auth\DashboardAuth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards): void
    {
        $guards = $guards === [] ? [null] : $guards;
        $route = DashboardAuth::administration()->loginRouteName();

        foreach ($guards as $guard) {
            if ($guard !== null) {
                $route = DashboardAuth::fromGuard($guard)->loginRouteName();
            }
        }

        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $this->redirect($request, $route),
        );
    }

    protected function redirect(Request $request, string $route): ?string
    {
        return $request->expectsJson() ? null : route($route);
    }
}

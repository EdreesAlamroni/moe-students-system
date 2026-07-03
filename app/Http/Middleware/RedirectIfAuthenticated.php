<?php

namespace App\Http\Middleware;

use App\Support\Auth\DashboardAuth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = $guards === [] ? [null] : $guards;

        foreach ($guards as $guard) {
            if (! Auth::guard($guard)->check()) {
                continue;
            }

            $route = $guard === null
                ? DashboardAuth::administration()->dashboardRouteName()
                : DashboardAuth::fromGuard($guard)->dashboardRouteName();

            return Redirect::route($route);
        }

        return $next($request);
    }
}

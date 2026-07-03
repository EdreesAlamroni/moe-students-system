<?php

namespace App\Http\Middleware;

use App\Support\Auth\DashboardAuth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $dashboard = DashboardAuth::resolve($request);

        if ($dashboard === null) {
            return $next($request);
        }

        if ($request->routeIs(
            $dashboard->routeName('password.change'),
            $dashboard->routeName('password.change.store'),
            $dashboard->routeName('logout'),
        )) {
            return $next($request);
        }

        $user = $request->user($dashboard->guard);

        if ($user !== null && $user->must_change_password) {
            return Redirect::to($dashboard->url('password.change'));
        }

        return $next($request);
    }
}

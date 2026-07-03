<?php

namespace App\Http\Middleware;

use App\Support\Auth\DashboardAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BindDashboardAuth
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $dashboardKey): Response
    {
        app()->instance(DashboardAuth::class, DashboardAuth::fromDashboardKey($dashboardKey));

        return $next($request);
    }
}

<?php

namespace App\Support;

use App\Support\Auth\DashboardAuth;
use Illuminate\Http\Request;

class AcademicYearReadOnlyExemptions
{
    /**
     * Route name suffixes that must remain available in read-only mode.
     *
     * These are session and account-management actions, not academic-year data mutations.
     *
     * @var list<string>
     */
    private const DASHBOARD_ROUTE_SUFFIXES = [
        'logout',
        'password.change',
        'password.change.store',
        'password.confirm',
        'password.confirm.store',
        'account-settings.profile.update',
        'account-settings.password.update',
    ];

    /**
     * @return list<string>
     */
    public static function routeNames(): array
    {
        static $routeNames = null;

        if ($routeNames !== null) {
            return $routeNames;
        }

        $routeNames = ['academic-year.select'];

        foreach (DashboardAuth::all() as $dashboard) {
            foreach (self::DASHBOARD_ROUTE_SUFFIXES as $suffix) {
                $routeNames[] = $dashboard->routeName($suffix);
            }
        }

        return $routeNames;
    }

    public static function matches(Request $request): bool
    {
        return $request->routeIs(self::routeNames());
    }
}

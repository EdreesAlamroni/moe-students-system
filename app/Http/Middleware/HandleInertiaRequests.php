<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use App\Models\User;
use App\Support\Auth\DashboardAuth;
use App\Support\Navigation\NavigationManager;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $dashboard = $this->resolveDashboard($request);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $this->resolveAuthenticatedUser($request, $dashboard)?->only(['id', 'name', 'username', 'email', 'role']),
            ],
            'dashboard' => $dashboard !== null ? [
                'key' => $dashboard->dashboardKey,
                'label' => $dashboard->label,
            ] : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'routeName' => $request->route()?->getName(),
            'navigation' => fn (): array => app(NavigationManager::class)->get($request),
            'currentAcademicYear' => fn (): ?array => AcademicYear::current()?->only(['id', 'name', 'is_active']),
            'availableAcademicYears' => fn (): Collection => AcademicYear::list(),
            'flash' => flash()->getMessage(),
        ];
    }

    private function resolveDashboard(Request $request): ?DashboardAuth
    {
        return DashboardAuth::resolve($request);
    }

    private function resolveAuthenticatedUser(Request $request, ?DashboardAuth $dashboard): ?User
    {
        if ($dashboard !== null) {
            $user = $request->user($dashboard->guard);

            if ($user !== null) {
                return $user;
            }
        }

        foreach (DashboardAuth::all() as $panel) {
            $user = $request->user($panel->guard);

            if ($user !== null) {
                return $user;
            }
        }

        return null;
    }
}

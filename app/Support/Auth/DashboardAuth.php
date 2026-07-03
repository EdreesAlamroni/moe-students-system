<?php

namespace App\Support\Auth;

use App\Enums\AuthPage;
use App\Enums\UserScope;
use Illuminate\Http\Request;
use InvalidArgumentException;

readonly class DashboardAuth
{
    /**
     * @var array<string, array{
     *     scope: UserScope,
     *     guard: string,
     *     label: string,
     *     supports_password_reset: bool
     * }>
     */
    private const DASHBOARDS = [
        'administration' => [
            'scope' => UserScope::ADMINISTRATION,
            'guard' => 'administration',
            'label' => 'الإدارة',
            'supports_password_reset' => true,
        ],
        'warehouse' => [
            'scope' => UserScope::WAREHOUSE,
            'guard' => 'warehouse',
            'label' => 'المخزن',
            'supports_password_reset' => true,
        ],
        'education-monitor' => [
            'scope' => UserScope::EDUCATION_MONITOR,
            'guard' => 'education_monitor',
            'label' => 'المُراقبة',
            'supports_password_reset' => true,
        ],
        'education-services-office' => [
            'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
            'guard' => 'education_services_office',
            'label' => 'مكتب الخدمات التعليمية',
            'supports_password_reset' => true,
        ],
        'school' => [
            'scope' => UserScope::SCHOOL,
            'guard' => 'school',
            'label' => 'المدرسة',
            'supports_password_reset' => false,
        ],
    ];

    public function __construct(
        public UserScope $scope,
        public string $guard,
        public string $dashboardKey,
        public string $label,
        public bool $supportsPasswordReset = false,
    ) {}

    public function routeName(string $suffix): string
    {
        return "{$this->dashboardKey}.{$suffix}";
    }

    public function route(string $suffix, mixed $parameters = [], bool $absolute = false): string
    {
        return route($this->routeName($suffix), $parameters, absolute: $absolute);
    }

    public function url(string $suffix, mixed $parameters = [], bool $absolute = false): string
    {
        return route($this->routeName($suffix), $parameters, absolute: $absolute);
    }

    public function loginRouteName(): string
    {
        return $this->routeName('login');
    }

    public function dashboardRouteName(): string
    {
        return $this->routeName('dashboard');
    }

    /**
     * @return array{title: string, description: string}
     */
    public function authPageHeading(AuthPage $page): array
    {
        return [
            'title' => __("auth.pages.{$page->value}.title", [
                'portal' => $this->label,
            ]),
            'description' => __("auth.pages.{$page->value}.description", [
                'portal' => $this->label,
            ]),
        ];
    }

    /**
     * @return array{
     *     dashboard: array{key: string, label: string},
     *     routes: array<string, string>,
     *     heading: array{title: string, description: string}
     * }
     */
    public function inertiaProps(AuthPage $page, array $extra = []): array
    {
        return array_merge([
            'dashboard' => [
                'key' => $this->dashboardKey,
                'label' => $this->label,
            ],
            'routes' => $this->authRoutes(),
            'heading' => $this->authPageHeading($page),
        ], $extra);
    }

    /**
     * @return array<string, string>
     */
    public function authRoutes(): array
    {
        $login = $this->route('login');

        $routes = [
            'login' => $login,
            'logout' => $this->route('logout'),
            'confirmPassword' => $this->route('password.confirm'),
            'confirmPasswordStore' => $this->route('password.confirm.store'),
            'changePassword' => $this->route('password.change'),
            'changePasswordStore' => $this->route('password.change.store'),
        ];

        if ($this->supportsPasswordReset) {
            $routes['forgotPassword'] = $this->route('password.request');
            $routes['forgotPasswordStore'] = $this->route('password.email');
            $routes['resetPasswordStore'] = $this->route('password.store');
        }

        return $routes;
    }

    /**
     * @return list<string>
     */
    public static function guestMiddleware(): array
    {
        $middleware = [];

        foreach (self::all() as $dashboard) {
            $middleware[] = sprintf('guest:%s', $dashboard->guard);
        }

        return $middleware;
    }

    public static function administration(): self
    {
        return self::fromDashboardKey('administration');
    }

    public static function warehouse(): self
    {
        return self::fromDashboardKey('warehouse');
    }

    public static function educationMonitor(): self
    {
        return self::fromDashboardKey('education-monitor');
    }

    public static function educationServicesOffice(): self
    {
        return self::fromDashboardKey('education-services-office');
    }

    public static function school(): self
    {
        return self::fromDashboardKey('school');
    }

    /**
     * @return array<string, self>
     */
    public static function all(): array
    {
        $dashboards = [];

        foreach (array_keys(self::DASHBOARDS) as $key) {
            $dashboards[$key] = self::fromDashboardKey($key);
        }

        return $dashboards;
    }

    public static function fromDashboardKey(string $dashboardKey): self
    {
        $config = self::DASHBOARDS[$dashboardKey] ?? null;

        if ($config === null) {
            throw new InvalidArgumentException("Unknown dashboard key [{$dashboardKey}].");
        }

        return new self(
            scope: $config['scope'],
            guard: $config['guard'],
            dashboardKey: $dashboardKey,
            label: $config['label'],
            supportsPasswordReset: $config['supports_password_reset'],
        );
    }

    public static function fromGuard(string $guard): self
    {
        foreach (self::all() as $dashboard) {
            if ($dashboard->guard === $guard) {
                return $dashboard;
            }
        }

        throw new InvalidArgumentException("Unknown authentication guard [{$guard}].");
    }

    public static function fromScope(UserScope $scope): ?self
    {
        foreach (self::all() as $dashboard) {
            if ($dashboard->scope === $scope) {
                return $dashboard;
            }
        }

        return null;
    }

    public static function resolve(?Request $request = null): ?self
    {
        if (app()->bound(self::class)) {
            return app(self::class);
        }

        $request ??= request();
        $dashboardKey = $request->segment(1);

        if ($dashboardKey === null) {
            return null;
        }

        try {
            return self::fromDashboardKey($dashboardKey);
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}

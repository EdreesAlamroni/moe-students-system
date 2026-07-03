<?php

namespace App\Support\Navigation;

use App\Models\User;
use App\Support\Auth\DashboardAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Resolves dashboard navigation sections and filters items by authorization.
 *
 * Item arrays require `title` and `href`. Supported optional keys:
 *
 * - `icon` — icon identifier applied when the item is rendered; defaults to `CircleIcon`.
 * - `can` — include the item when true; defaults to false. Removed before the payload is sent to the client.
 * - `activeRoutes` — route name pattern or patterns that mark the item as active; false disables highlighting; omitted values fall back to URL comparison.
 * - `excludedRoutes` — route name pattern or patterns that override a matching `activeRoutes` value.
 * - `key` — persistent identifier for client-side rendering and item-specific behavior.
 */
abstract class NavigationPanel
{
    protected ?User $user;

    protected ?DashboardAuth $dashboard = null;

    public function __construct(Request $request)
    {
        $this->dashboard = DashboardAuth::resolve($request);
        $this->user = $this->dashboard !== null
            ? $request->user($this->dashboard->guard)
            : $request->user();
    }

    public function get(): array
    {
        $sections = [
            ['title' => __('العمليات الأساسية'), 'items' => $this->main()],
            ['title' => __('التقارير'), 'items' => $this->reports()],
            ['title' => __('الإعدادات'), 'items' => $this->settings()],
        ];

        $main = [];

        foreach ($sections as $section) {
            $items = $this->visible($section['items']);

            if ($items !== []) {
                $main[] = ['title' => $section['title'], 'items' => $items];
            }
        }

        return [
            'home' => $main[0]['items'][0]['href'] ?? null,
            'main' => $main,
            'account' => [
                'menu' => $this->visible($this->accountMenu()),
                'tabs' => $this->visible($this->accountTabs()),
            ],
        ];
    }

    protected function main(): array
    {
        return [];
    }

    protected function reports(): array
    {
        return [];
    }

    protected function settings(): array
    {
        return [];
    }

    protected function accountMenu(): array
    {
        if ($this->dashboard === null) {
            return [];
        }

        return [
            [
                'key' => 'account-settings',
                'title' => 'إعدادات الحساب',
                'href' => route($this->dashboard->routeName('account-settings.profile.edit')),
                'icon' => 'SettingsIcon',
                'activeRoutes' => $this->dashboard->routeName('account-settings.*'),
                'can' => true,
            ],
            [
                'key' => 'logout',
                'title' => 'تسجيل الخروج',
                'href' => route($this->dashboard->routeName('logout')),
                'icon' => 'LogOutIcon',
                'activeRoutes' => false,
                'can' => true,
            ],
        ];
    }

    protected function accountTabs(): array
    {
        if ($this->dashboard === null) {
            return [];
        }

        return [
            [
                'title' => 'الملف الشخصي',
                'href' => route($this->dashboard->routeName('account-settings.profile.edit')),
                'icon' => 'UserIcon',
                'activeRoutes' => $this->dashboard->routeName('account-settings.profile.*'),
                'can' => true,
            ],
            [
                'title' => 'الحماية والأمان',
                'href' => route($this->dashboard->routeName('account-settings.security.edit')),
                'icon' => 'ShieldIcon',
                'activeRoutes' => $this->dashboard->routeName('account-settings.security.*'),
                'can' => true,
            ],
        ];
    }

    /**
     * Keep only authorized items, apply the default icon, and drop the `can`
     * flag so the authorization decision never reaches the client.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function visible(array $items): array
    {
        $visible = [];

        foreach ($items as $item) {
            if (! ($item['can'] ?? false)) {
                continue;
            }

            $item['icon'] ??= 'CircleIcon';

            $visible[] = Arr::except($item, 'can');
        }

        return $visible;
    }
}

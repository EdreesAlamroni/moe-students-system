<?php

namespace App\Support\Navigation;

use Illuminate\Http\Request;

class NavigationManager
{
    /**
     * @var array<string, class-string<NavigationPanel>>
     */
    protected array $panels = [
        'administration' => Panels\AdministrationNavigation::class,
        'warehouse' => Panels\WarehouseNavigation::class,
        'education-monitor' => Panels\EducationMonitorNavigation::class,
        'education-services-office' => Panels\EducationServicesOfficeNavigation::class,
        'school' => Panels\SchoolNavigation::class,
    ];

    public function get(Request $request): array
    {
        $class = $this->panels[$request->segment(1)] ?? null;

        if ($class === null) {
            return [
                'home' => null,
                'main' => [],
                'account' => ['menu' => [], 'tabs' => []],
            ];
        }

        return app($class, ['request' => $request])->get();
    }
}

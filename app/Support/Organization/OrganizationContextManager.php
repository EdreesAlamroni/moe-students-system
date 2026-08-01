<?php

namespace App\Support\Organization;

use App\Support\Auth\DashboardAuth;
use App\Support\Organization\Contexts\EducationMonitorOrganizationContext;
use App\Support\Organization\Contexts\EducationServicesOfficeOrganizationContext;
use App\Support\Organization\Contexts\SchoolOrganizationContext;
use Illuminate\Http\Request;

final class OrganizationContextManager
{
    /**
     * @var array<string, class-string<OrganizationContext>>
     */
    private array $contexts = [
        'education-monitor' => EducationMonitorOrganizationContext::class,
        'education-services-office' => EducationServicesOfficeOrganizationContext::class,
        'school' => SchoolOrganizationContext::class,
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function resolve(Request $request): ?array
    {
        $dashboard = DashboardAuth::resolve($request);

        if ($dashboard === null) {
            return null;
        }

        $contextClass = $this->contexts[$dashboard->dashboardKey] ?? null;

        if ($contextClass === null) {
            return null;
        }

        $user = $request->user($dashboard->guard);

        if ($user === null) {
            return null;
        }

        return app($contextClass)->resolve($user);
    }
}

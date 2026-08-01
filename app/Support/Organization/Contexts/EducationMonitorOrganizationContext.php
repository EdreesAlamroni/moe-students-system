<?php

namespace App\Support\Organization\Contexts;

use App\Models\EducationMonitor;
use App\Support\Organization\OrganizationContext;
use Illuminate\Database\Eloquent\Model;

final class EducationMonitorOrganizationContext extends OrganizationContext
{
    protected function organizationType(): string
    {
        return EducationMonitor::class;
    }

    protected function columns(): array
    {
        return ['id', 'name'];
    }

    protected function build(Model $organization): array
    {
        /** @var EducationMonitor $organization */
        return [
            'type' => 'education_monitor',
            'id' => $organization->id,
            'name' => $organization->name,
        ];
    }
}

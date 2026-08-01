<?php

namespace App\Support\Organization\Contexts;

use App\Models\EducationServicesOffice;
use App\Support\Organization\OrganizationContext;
use Illuminate\Database\Eloquent\Model;

final class EducationServicesOfficeOrganizationContext extends OrganizationContext
{
    protected function organizationType(): string
    {
        return EducationServicesOffice::class;
    }

    protected function columns(): array
    {
        return ['id', 'name'];
    }

    protected function build(Model $organization): array
    {
        /** @var EducationServicesOffice $organization */
        return [
            'type' => 'education_services_office',
            'id' => $organization->id,
            'name' => $organization->name,
        ];
    }
}

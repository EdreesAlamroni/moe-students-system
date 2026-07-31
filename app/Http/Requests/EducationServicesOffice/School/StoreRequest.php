<?php

namespace App\Http\Requests\EducationServicesOffice\School;

use App\Http\Requests\Shared\School\StoreSchoolRequest;
use App\Models\EducationServicesOffice;

class StoreRequest extends StoreSchoolRequest
{
    public function authorize(): bool
    {
        return auth('education_services_office')->check();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function organizationRules(): array
    {
        return [];
    }

    protected function educationMonitorId(): ?int
    {
        /** @var EducationServicesOffice|null $office */
        $office = auth('education_services_office')->user()?->organization;

        if ($office === null) {
            return null;
        }

        return $office->education_monitor_id;
    }

    protected function educationServicesOfficeId(): ?int
    {
        return auth('education_services_office')->user()?->organization_id;
    }
}

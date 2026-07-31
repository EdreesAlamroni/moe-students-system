<?php

namespace App\Http\Requests\EducationMonitor\School;

use App\Http\Requests\Shared\School\StoreSchoolRequest;

class StoreRequest extends StoreSchoolRequest
{
    public function authorize(): bool
    {
        return auth('education_monitor')->check();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function organizationRules(): array
    {
        return $this->educationServicesOfficeRules();
    }

    protected function educationMonitorId(): ?int
    {
        return auth('education_monitor')->user()?->organization_id;
    }
}

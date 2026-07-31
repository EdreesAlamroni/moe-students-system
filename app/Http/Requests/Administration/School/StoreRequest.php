<?php

namespace App\Http\Requests\Administration\School;

use App\Http\Requests\Shared\School\StoreSchoolRequest;

class StoreRequest extends StoreSchoolRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function organizationRules(): array
    {
        return array_merge(
            $this->educationMonitorRules(),
            $this->educationServicesOfficeRules(),
        );
    }
}

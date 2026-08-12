<?php

namespace App\Http\Requests\Administration\User;

use App\Enums\UserScope;
use App\Http\Requests\Shared\User\StoreUserRequest;
use App\ModelStates\User\RequestState\Approved;
use Illuminate\Validation\Rule;

class StoreRequest extends StoreUserRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    /**
     * @return array<string, array<int, mixed>|\Illuminate\Validation\ConditionalRules>
     */
    protected function organizationRules(): array
    {
        return [
            'warehouse_id' => Rule::when(
                $this->scopeIs(UserScope::WAREHOUSE),
                $this->warehouseIdRules(),
            ),
            'education_monitor_id' => Rule::when(
                $this->scopeRequiresMonitor(),
                $this->educationMonitorIdRules(),
            ),
            'education_services_office_id' => Rule::when(
                $this->scopeIs(UserScope::EDUCATION_SERVICES_OFFICE),
                $this->educationServicesOfficeIdRules($this->integer('education_monitor_id') ?: null),
            ),
            'school_id' => Rule::when(
                $this->scopeIs(UserScope::SCHOOL),
                $this->schoolIdRulesForMonitor($this->integer('education_monitor_id') ?: null),
            ),
        ];
    }

    protected function defaultRequestState(): string
    {
        return Approved::class;
    }

    private function scopeRequiresMonitor(): bool
    {
        return $this->scopeIs(UserScope::EDUCATION_MONITOR)
            || $this->scopeIs(UserScope::EDUCATION_SERVICES_OFFICE)
            || $this->scopeIs(UserScope::SCHOOL);
    }
}

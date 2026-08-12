<?php

namespace App\Http\Requests\EducationMonitor\User;

use App\Enums\UserScope;
use App\Http\Requests\Shared\User\StoreUserRequest;
use App\Models\User;
use App\ModelStates\User\RequestState\Pending;
use Illuminate\Validation\Rule;

class StoreRequest extends StoreUserRequest
{
    public function authorize(): bool
    {
        return auth('education_monitor')->check();
    }

    /**
     * @return array<string, array<int, mixed>|\Illuminate\Validation\ConditionalRules>
     */
    protected function organizationRules(): array
    {
        $monitorId = $this->currentMonitorId();

        return [
            'education_services_office_id' => Rule::when(
                $this->scopeIs(UserScope::EDUCATION_SERVICES_OFFICE),
                $this->educationServicesOfficeIdRules($monitorId),
            ),
            'school_id' => Rule::when(
                $this->scopeIs(UserScope::SCHOOL),
                $this->schoolIdRulesForMonitor($monitorId),
            ),
        ];
    }

    protected function defaultRequestState(): string
    {
        return Pending::class;
    }

    protected function educationMonitorId(array $validated): ?int
    {
        return $this->currentMonitorId();
    }

    private function currentMonitorId(): ?int
    {
        /** @var User|null $user */
        $user = $this->user('education_monitor');

        return $user?->organization_id;
    }
}

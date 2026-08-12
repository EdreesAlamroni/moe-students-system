<?php

namespace App\Http\Requests\EducationServicesOffice\User;

use App\Enums\UserScope;
use App\Http\Requests\Shared\User\StoreUserRequest;
use App\Models\User;
use App\ModelStates\User\RequestState\Pending;
use Illuminate\Validation\Rule;

class StoreRequest extends StoreUserRequest
{
    public function authorize(): bool
    {
        return auth('education_services_office')->check();
    }

    /**
     * @return array<string, array<int, mixed>|\Illuminate\Validation\ConditionalRules>
     */
    protected function organizationRules(): array
    {
        return [
            'school_id' => Rule::when(
                $this->scopeIs(UserScope::SCHOOL),
                $this->schoolIdRulesForOffice($this->currentOfficeId()),
            ),
        ];
    }

    protected function defaultRequestState(): string
    {
        return Pending::class;
    }

    protected function educationServicesOfficeId(array $validated): ?int
    {
        return $this->currentOfficeId();
    }

    private function currentOfficeId(): ?int
    {
        /** @var User|null $user */
        $user = $this->user('education_services_office');

        return $user?->organization_id;
    }
}

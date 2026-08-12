<?php

namespace App\Http\Requests\School\User;

use App\Enums\UserScope;
use App\Http\Requests\Shared\User\StoreUserRequest;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\ModelStates\User\RequestState\Pending;

class StoreRequest extends StoreUserRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    protected function organizationRules(): array
    {
        return [];
    }

    protected function scopeRules(): array
    {
        return [];
    }

    protected function forcedScope(): ?UserScope
    {
        return UserScope::SCHOOL;
    }

    protected function includesSchoolPeriodAssignment(): bool
    {
        return false;
    }

    protected function roleGuardName(): string
    {
        return UserScope::SCHOOL->value;
    }

    protected function defaultRequestState(): string
    {
        return Pending::class;
    }

    protected function resolveOrganization(UserScope $scope, array $validated): array
    {
        /** @var User $user */
        $user = $this->user('school');

        return [
            $user->organization_id,
            SchoolPeriod::class,
        ];
    }
}

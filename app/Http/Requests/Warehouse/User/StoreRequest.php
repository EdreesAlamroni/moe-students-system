<?php

namespace App\Http\Requests\Warehouse\User;

use App\Enums\UserScope;
use App\Http\Requests\Shared\User\StoreUserRequest;
use App\Models\User;
use App\Models\Warehouse;
use App\ModelStates\User\RequestState\Approved;

class StoreRequest extends StoreUserRequest
{
    public function authorize(): bool
    {
        return auth('warehouse')->check();
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
        return UserScope::WAREHOUSE;
    }

    protected function includesSchoolPeriodAssignment(): bool
    {
        return false;
    }

    protected function roleGuardName(): string
    {
        return UserScope::WAREHOUSE->value;
    }

    protected function defaultRequestState(): string
    {
        return Approved::class;
    }

    protected function resolveOrganization(UserScope $scope, array $validated): array
    {
        /** @var User $user */
        $user = $this->user('warehouse');

        return [
            $user->organization_id,
            Warehouse::class,
        ];
    }
}

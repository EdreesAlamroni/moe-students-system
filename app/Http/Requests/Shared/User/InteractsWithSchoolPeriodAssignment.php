<?php

namespace App\Http\Requests\Shared\User;

use App\Enums\UserScope;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\Rules\SchoolPeriodMembership;
use Illuminate\Validation\Rule;

trait InteractsWithSchoolPeriodAssignment
{
    protected function schoolPeriodAssignmentRules(?int $schoolId): array
    {
        return [
            Rule::requiredIf(function () {
                return SchoolPeriod::hasDualPeriodsForSchool($this->integer('school_id') ?: null);
            }),
            'array',
            new SchoolPeriodMembership($schoolId),
        ];
    }

    protected function schoolPeriodIdsItemRules(): array
    {
        return [
            'integer',
            'distinct',
        ];
    }

    protected function schoolOrganizationAttributesForUser(User $user): array
    {
        $schoolPeriodIds = $this->validatedSchoolPeriodIdsForUser($user);
        $activeSchoolPeriodId = in_array((int) $user->organization_id, $schoolPeriodIds, true)
            ? (int) $user->organization_id
            : User::resolveDefaultActiveSchoolPeriodId($schoolPeriodIds);

        return [
            'organization_id' => $activeSchoolPeriodId,
            'organization_type' => SchoolPeriod::class,
        ];
    }

    public function validatedSchoolPeriodIds(): array
    {
        if ($this->enum('scope', UserScope::class) !== UserScope::SCHOOL) {
            return [];
        }

        $validated = $this->validated();

        return User::resolveSchoolPeriodIds(
            (int) $validated['school_id'],
            $validated['school_period_ids'] ?? null,
        );
    }

    public function validatedSchoolPeriodIdsForUser(User $user): array
    {
        if ($user->scope !== UserScope::SCHOOL) {
            return [];
        }

        $validated = $this->validated();

        return User::resolveSchoolPeriodIds(
            (int) $validated['school_id'],
            $validated['school_period_ids'] ?? null,
        );
    }
}

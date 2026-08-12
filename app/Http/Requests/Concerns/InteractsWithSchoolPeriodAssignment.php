<?php

namespace App\Http\Requests\Concerns;

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

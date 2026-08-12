<?php

namespace App\Http\Requests\Shared\User;

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

abstract class StoreUserRequest extends FormRequest
{
    use InteractsWithSchoolPeriodAssignment, ValidatesUserOrganization;

    /**
     * @return array<string, array<int, mixed>|\Illuminate\Validation\ConditionalRules>
     */
    abstract protected function organizationRules(): array;

    abstract protected function defaultRequestState(): string;

    public function rules(): array
    {
        return array_merge(
            $this->organizationRules(),
            $this->schoolPeriodStoreRules(),
            $this->profileRules(),
            $this->scopeRules(),
            $this->roleRules(),
        );
    }

    public function getAttributes(): array
    {
        $validated = $this->validated();
        $scope = $this->forcedScope() ?? UserScope::from($validated['scope']);

        $attributes = Arr::except($validated, [
            'warehouse_id',
            'education_monitor_id',
            'education_services_office_id',
            'school_id',
            'school_period_ids',
            'roles',
            'password_confirmation',
        ]);

        if ($this->forcedScope() !== null) {
            $attributes['scope'] = $this->forcedScope()->value;
        }

        [$organizationId, $organizationType] = $this->resolveOrganization($scope, $validated);

        return Arr::merge($attributes, [
            'organization_id' => $organizationId,
            'organization_type' => $organizationType,
            'role' => UserRole::EMPLOYEE->value,
            'request_state' => $this->defaultRequestState(),
        ]);
    }

    protected function prepareForValidation(): void
    {
        $roles = $this->input('roles', []);

        $this->merge([
            'email' => $this->filled('email') ? $this->input('email') : null,
            'roles' => is_array($roles) ? $roles : json_decode($roles, true) ?? [],
        ]);
    }

    protected function profileRules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class, 'username'),
            ],
            'email' => [
                'sometimes',
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],
            'password' => [
                'required',
                'string',
                'max:255',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }

    protected function scopeRules(): array
    {
        return [
            'scope' => [
                'required',
                Rule::in(UserScope::options()->pluck('id')->all()),
            ],
        ];
    }

    protected function roleRules(): array
    {
        return [
            'roles' => [
                'required',
                'array',
                'min:1',
            ],
            'roles.*' => [
                'required',
                'integer',
                Rule::exists(Role::class, 'id')->where('guard_name', $this->roleGuardName()),
            ],
        ];
    }

    protected function schoolPeriodStoreRules(): array
    {
        if (! $this->includesSchoolPeriodAssignment()) {
            return [];
        }

        $scopeIsSchool = $this->scopeIs(UserScope::SCHOOL);

        return [
            'school_period_ids' => Rule::when(
                $scopeIsSchool,
                $this->schoolPeriodAssignmentRules($this->integer('school_id') ?: null),
            ),
            'school_period_ids.*' => $this->schoolPeriodIdsItemRules(),
        ];
    }

    protected function resolveOrganization(UserScope $scope, array $validated): array
    {
        return match ($scope) {
            UserScope::ADMINISTRATION => [
                null,
                null,
            ],
            UserScope::WAREHOUSE => [
                isset($validated['warehouse_id']) ? (int) $validated['warehouse_id'] : null,
                Warehouse::class,
            ],
            UserScope::EDUCATION_MONITOR => [
                $this->educationMonitorId($validated),
                EducationMonitor::class,
            ],
            UserScope::EDUCATION_SERVICES_OFFICE => [
                $this->educationServicesOfficeId($validated),
                EducationServicesOffice::class,
            ],
            UserScope::SCHOOL => [
                User::resolveDefaultActiveSchoolPeriodId($this->validatedSchoolPeriodIds()),
                SchoolPeriod::class,
            ],
        };
    }

    protected function educationMonitorId(array $validated): ?int
    {
        if (! isset($validated['education_monitor_id'])) {
            return null;
        }

        return (int) $validated['education_monitor_id'];
    }

    protected function educationServicesOfficeId(array $validated): ?int
    {
        if (! isset($validated['education_services_office_id'])) {
            return null;
        }

        return (int) $validated['education_services_office_id'];
    }

    protected function forcedScope(): ?UserScope
    {
        return null;
    }

    protected function includesSchoolPeriodAssignment(): bool
    {
        return true;
    }

    protected function roleGuardName(): string
    {
        return (string) $this->input('scope');
    }

    protected function scopeIs(UserScope $scope): bool
    {
        return $this->enum('scope', UserScope::class) === $scope;
    }
}

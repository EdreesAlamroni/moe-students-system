<?php

namespace App\Http\Requests\EducationMonitor\User;

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Http\Requests\Concerns\InteractsWithSchoolPeriodAssignment;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\ModelStates\User\RequestState\Pending;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class StoreRequest extends FormRequest
{
    use InteractsWithSchoolPeriodAssignment;

    public function authorize(): bool
    {
        return auth('education_monitor')->check();
    }

    public function rules(): array
    {
        $monitorId = $this->currentMonitorId();

        $scopeIsEducationServicesOffice = $this->scopeIs(UserScope::EDUCATION_SERVICES_OFFICE);
        $scopeIsSchool = $this->scopeIs(UserScope::SCHOOL);

        return [
            'education_services_office_id' => Rule::when($scopeIsEducationServicesOffice, [
                'required',
                Rule::exists(EducationServicesOffice::class, 'id')
                    ->where('education_monitor_id', $monitorId),
            ]),
            'school_id' => Rule::when($scopeIsSchool, [
                'required',
                'integer',
                Rule::exists(School::class, 'id')
                    ->where('education_monitor_id', $monitorId),
            ]),
            'school_period_ids' => Rule::when($scopeIsSchool, $this->schoolPeriodAssignmentRules($this->integer('school_id') ?: null)),
            'school_period_ids.*' => [
                'integer',
                'distinct',
            ],
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
            'scope' => [
                'required',
                Rule::in(UserScope::options()->pluck('id')->all()),
            ],
            'password' => [
                'required',
                'string',
                'max:255',
                'confirmed',
                Password::defaults(),
            ],
            'roles' => [
                'required',
                'array',
                'min:1',
            ],
            'roles.*' => [
                'required',
                'integer',
                Rule::exists(Role::class, 'id')->where('guard_name', $this->input('scope')),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $roles = $this->input('roles', []);

        $this->merge([
            'email' => $this->filled('email') ? $this->input('email') : null,
            'roles' => is_array($roles) ? $roles : json_decode($roles, true) ?? [],
        ]);
    }

    public function getAttributes(): array
    {
        $validated = $this->validated();
        $scope = UserScope::from($validated['scope']);

        $attributes = Arr::except($validated, [
            'education_services_office_id',
            'school_id',
            'school_period_ids',
            'roles',
            'password_confirmation',
        ]);

        [$organizationId, $organizationType] = match ($scope) {
            UserScope::EDUCATION_MONITOR => [
                $this->currentMonitorId(),
                EducationMonitor::class,
            ],
            UserScope::EDUCATION_SERVICES_OFFICE => [
                $validated['education_services_office_id'] ?? null,
                EducationServicesOffice::class,
            ],
            UserScope::SCHOOL => [
                User::resolveDefaultActiveSchoolPeriodId($this->validatedSchoolPeriodIds()),
                SchoolPeriod::class,
            ],
            default => [null, null],
        };

        return Arr::merge($attributes, [
            'organization_id' => $organizationId,
            'organization_type' => $organizationType,
            'role' => UserRole::EMPLOYEE->value,
            'request_state' => Pending::class,
        ]);
    }

    private function scopeIs(UserScope $scope): bool
    {
        return $this->enum('scope', UserScope::class) === $scope;
    }

    private function currentMonitorId(): ?int
    {
        /** @var User|null $user */
        $user = $this->user('education_monitor');

        return $user?->organization_id;
    }
}

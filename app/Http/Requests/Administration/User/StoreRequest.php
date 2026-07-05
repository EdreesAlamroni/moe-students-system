<?php

namespace App\Http\Requests\Administration\User;

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\User;
use App\Models\Warehouse;
use App\ModelStates\User\RequestState\Approved;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function (): bool {
                    return $this->scopeIs(UserScope::WAREHOUSE);
                }),
                Rule::exists(Warehouse::class, 'id'),
            ],
            'education_monitor_id' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function (): bool {
                    return $this->scopeIs(UserScope::EDUCATION_MONITOR)
                        || $this->scopeIs(UserScope::EDUCATION_SERVICES_OFFICE)
                        || $this->scopeIs(UserScope::SCHOOL);
                }),
                Rule::exists(EducationMonitor::class, 'id'),
            ],
            'education_services_office_id' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function (): bool {
                    return $this->scopeIs(UserScope::EDUCATION_SERVICES_OFFICE);
                }),
                Rule::exists(EducationServicesOffice::class, 'id')->where('education_monitor_id', $this->input('education_monitor_id')),
            ],
            'school_id' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function (): bool {
                    return $this->scopeIs(UserScope::SCHOOL);
                }),
                Rule::exists(School::class, 'id')->where('education_monitor_id', $this->input('education_monitor_id')),
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
                Rule::exists(Role::class, 'id'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $roles = $this->input('roles', []);

        $this->merge([
            'roles' => is_array($roles) ? $roles : json_decode($roles, true) ?? [],
        ]);
    }

    public function getAttributes(): array
    {
        $validated = $this->validated();
        $scope = UserScope::from($validated['scope']);

        $attributes = Arr::except($validated, [
            'warehouse_id',
            'education_monitor_id',
            'education_services_office_id',
            'school_id',
            'roles',
            'password_confirmation',
        ]);

        [$modelId, $modelType] = match ($scope) {
            UserScope::ADMINISTRATION => [
                null,
                null,
            ],
            UserScope::WAREHOUSE => [
                $validated['warehouse_id'] ?? null,
                Warehouse::class,
            ],
            UserScope::EDUCATION_MONITOR => [
                $validated['education_monitor_id'] ?? null,
                EducationMonitor::class,
            ],
            UserScope::EDUCATION_SERVICES_OFFICE => [
                $validated['education_services_office_id'] ?? null,
                EducationServicesOffice::class,
            ],
            UserScope::SCHOOL => [
                $validated['school_id'] ?? null,
                School::class,
            ],
        };

        return Arr::merge($attributes, [
            'model_id' => $modelId,
            'model_type' => $modelType,
            'role' => UserRole::EMPLOYEE->value,
            'request_state' => Approved::class,
        ]);
    }

    private function scopeIs(UserScope $scope): bool
    {
        return $this->input('scope') === $scope->value;
    }
}

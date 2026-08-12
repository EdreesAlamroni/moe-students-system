<?php

namespace App\Http\Requests\Shared\User;

use App\Enums\UserScope;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

abstract class UpdateUserRequest extends FormRequest
{
    use InteractsWithSchoolPeriodAssignment, ValidatesUserOrganization;

    /**
     * @return array<string, array<int, mixed>|\Illuminate\Validation\ConditionalRules>
     */
    abstract protected function schoolIdRules(): array;

    /**
     * @return array<string, array<int, mixed>|\Illuminate\Validation\ConditionalRules>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return array_merge(
            $this->profileRules($user),
            $this->schoolAssignmentRules($user),
            $this->roleRules($user),
        );
    }

    public function getAttributes(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        $attributes = Arr::except($this->validated(), [
            'roles',
            'school_id',
            'school_period_ids',
        ]);

        if ($user->scope === UserScope::SCHOOL && $this->allowsSchoolPeriodAssignment()) {
            $attributes = Arr::merge($attributes, $this->schoolOrganizationAttributesForUser($user));
        }

        return $attributes;
    }

    protected function prepareForValidation(): void
    {
        $roles = $this->input('roles', []);

        $this->merge([
            'email' => $this->filled('email') ? $this->input('email') : null,
            'roles' => is_array($roles) ? $roles : json_decode($roles, true) ?? [],
        ]);
    }

    protected function profileRules(User $user): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'sometimes',
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user),
            ],
        ];
    }

    protected function roleRules(User $user): array
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
                Rule::exists(Role::class, 'id')->where('guard_name', $user->scope->value),
            ],
        ];
    }

    protected function schoolAssignmentRules(User $user): array
    {
        if ($user->scope !== UserScope::SCHOOL || ! $this->allowsSchoolPeriodAssignment()) {
            return [];
        }

        return array_merge($this->schoolIdRules(), [
            'school_period_ids' => $this->schoolPeriodAssignmentRules($this->integer('school_id') ?: null),
            'school_period_ids.*' => $this->schoolPeriodIdsItemRules(),
        ]);
    }

    protected function allowsSchoolPeriodAssignment(): bool
    {
        return true;
    }
}

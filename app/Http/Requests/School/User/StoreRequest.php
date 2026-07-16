<?php

namespace App\Http\Requests\School\User;

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\School;
use App\Models\User;
use App\ModelStates\User\RequestState\Pending;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
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
            'roles' => [
                'required',
                'array',
                'min:1',
            ],
            'roles.*' => [
                'required',
                'integer',
                Rule::exists(Role::class, 'id')->where('guard_name', UserScope::SCHOOL->value),
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
        /** @var User $user */
        $user = $this->user('school');

        $attributes = Arr::except($this->validated(), [
            'roles',
            'password_confirmation',
        ]);

        return Arr::merge($attributes, [
            'scope' => UserScope::SCHOOL->value,
            'organization_id' => $user->organization_id,
            'organization_type' => School::class,
            'role' => UserRole::EMPLOYEE->value,
            'request_state' => Pending::class,
        ]);
    }
}

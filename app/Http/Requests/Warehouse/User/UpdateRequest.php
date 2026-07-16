<?php

namespace App\Http\Requests\Warehouse\User;

use App\Enums\UserScope;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('warehouse')->check();
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

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
            'roles' => [
                'required',
                'array',
                'min:1',
            ],
            'roles.*' => [
                'required',
                'integer',
                Rule::exists(Role::class, 'id')->where('guard_name', UserScope::WAREHOUSE->value),
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
        return Arr::except($this->validated(), [
            'roles',
        ]);
    }
}

<?php

namespace App\Http\Requests\Administration\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
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
                'email',
                Rule::unique(User::class, 'email')->ignore($this->route('user')),
            ],
            'roles' => [
                'required',
                'array',
            ],
            'roles.*' => [
                'required',
                Rule::exists(Role::class, 'id'),
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $roles = $this->input('roles', []);

        $this->merge([
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

<?php

namespace App\Http\Requests\Administration\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'max:255',
                'confirmed',
                Password::defaults(),
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\Administration\User;

use App\ModelStates\User\State\UserState;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        return [
            'state' => [
                'required',
                (new ValidStateRule(UserState::class))->required(),
            ],
        ];
    }
}

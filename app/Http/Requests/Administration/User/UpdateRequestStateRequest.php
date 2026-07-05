<?php

namespace App\Http\Requests\Administration\User;

use App\ModelStates\User\RequestState\UserRequestState;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateRequestStateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        return [
            'request_state' => [
                'required',
                new ValidStateRule(UserRequestState::class)->required(),
            ],
        ];
    }
}

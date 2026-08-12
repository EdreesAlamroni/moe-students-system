<?php

namespace App\Http\Requests\Administration\User;

use App\Http\Requests\Shared\User\UpdateUserRequest;

class UpdateRequest extends UpdateUserRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    protected function schoolIdRules(): array
    {
        return [
            'school_id' => $this->schoolIdExistsRules(),
        ];
    }
}

<?php

namespace App\Http\Requests\School\User;

use App\Http\Requests\Shared\User\UpdateUserRequest;

class UpdateRequest extends UpdateUserRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    protected function schoolIdRules(): array
    {
        return [];
    }

    protected function allowsSchoolPeriodAssignment(): bool
    {
        return false;
    }
}

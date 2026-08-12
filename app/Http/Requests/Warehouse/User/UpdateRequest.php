<?php

namespace App\Http\Requests\Warehouse\User;

use App\Http\Requests\Shared\User\UpdateUserRequest;

class UpdateRequest extends UpdateUserRequest
{
    public function authorize(): bool
    {
        return auth('warehouse')->check();
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

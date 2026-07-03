<?php

namespace App\Http\Requests\AccountSettings;

use App\Concerns\PasswordValidationRules;
use App\Support\Auth\DashboardAuth;
use Illuminate\Foundation\Http\FormRequest;

class PasswordUpdateRequest extends FormRequest
{
    use PasswordValidationRules;

    public function rules(): array
    {
        $dashboard = DashboardAuth::resolve($this)
            ?? throw new \InvalidArgumentException('Unable to resolve dashboard from request.');

        $guard = $dashboard->guard;

        return [
            'current_password' => ['required', 'string', 'current_password:'.$guard],
            'password' => $this->passwordRules(),
        ];
    }
}

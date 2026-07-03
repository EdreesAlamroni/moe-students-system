<?php

namespace App\Http\Requests\Auth;

use App\Concerns\PasswordValidationRules;
use App\Support\Auth\DashboardAuth;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
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

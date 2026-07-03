<?php

namespace App\Http\Requests\AccountSettings;

use App\Concerns\ProfileValidationRules;
use App\Support\Auth\DashboardAuth;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    public function rules(): array
    {
        $dashboard = DashboardAuth::resolve($this)
            ?? throw new \InvalidArgumentException('Unable to resolve dashboard from request.');

        return $this->profileRules($this->user($dashboard->guard)->id);
    }
}

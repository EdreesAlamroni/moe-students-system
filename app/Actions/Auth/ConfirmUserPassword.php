<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\Auth\DashboardAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ConfirmUserPassword
{
    public function execute(DashboardAuth $dashboard, User $user, string $password): void
    {
        if (! Auth::guard($dashboard->guard)->validate([
            'username' => $user->username,
            'password' => $password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }
    }
}

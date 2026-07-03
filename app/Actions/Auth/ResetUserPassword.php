<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\Auth\DashboardAuth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetUserPassword
{
    /**
     * @param  array{token: string, email: string, password: string, password_confirmation: string}  $credentials
     */
    public function execute(DashboardAuth $dashboard, array $credentials): string
    {
        $user = User::query()
            ->where('email', '=', $credentials['email'])
            ->first();

        if (is_null($user) || $user->scope !== $dashboard->scope) {
            return Password::INVALID_TOKEN;
        }

        return Password::reset($credentials, function (User $user) use ($dashboard, $credentials): void {
            if ($user->scope !== $dashboard->scope) {
                return;
            }

            $user->forceFill([
                'password' => Hash::make($credentials['password']),
                'remember_token' => Str::random(60),
                'must_change_password' => false,
            ])->save();

            event(new PasswordReset($user));
        });
    }
}

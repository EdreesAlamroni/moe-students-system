<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\Auth\DashboardAuth;
use Illuminate\Support\Facades\Password;

class SendPasswordResetLink
{
    public function execute(DashboardAuth $dashboard, string $email): string
    {
        $user = User::query()
            ->where('scope', '=', $dashboard->scope->value)
            ->where('email', '=', $email)
            ->first();

        if (is_null($user)) {
            return Password::RESET_LINK_SENT;
        }

        $token = Password::broker()->createToken($user);
        $user->sendPasswordResetNotification($token);

        return Password::RESET_LINK_SENT;
    }
}

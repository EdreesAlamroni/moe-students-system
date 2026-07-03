<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangeUserPassword
{
    public function execute(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'must_change_password' => false,
        ])->save();
    }
}

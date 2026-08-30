<?php

namespace App\DataTransferObjects\User;

use App\Models\User;

readonly class CreatedDefaultUser
{
    public function __construct(
        public User $user,
        public string $initialPassword,
    ) {}
}
